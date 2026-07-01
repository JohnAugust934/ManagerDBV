<?php

namespace App\Services;

use App\Models\Desbravador;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use Illuminate\Support\Collection;

/**
 * Fonte da verdade ÚNICA do ranking de frequência.
 *
 * Antes esta lógica existia DUPLICADA em três lugares que divergiam entre si
 * (snapshot de console em AppServiceProvider, telas ao vivo em RankingController
 * e PDFs em RelatorioController). Agora todos consomem este serviço; cada
 * chamador apenas molda a apresentação (cores, subtexto, linhas de tabela).
 *
 * Regras centralizadas aqui:
 *  - Apenas unidades participantes (no_ranking = true) entram no ranking.
 *  - Apenas desbravadores ativos entram no ranking individual.
 *  - A pontuação vem de Frequencia::getPontosAttribute/detalhePontos (que conta
 *    somente colunas marcadas) — ver App\Models\Frequencia.
 *  - Isolamento por club_id explícito (não depende do ClubScope ativo, para
 *    funcionar também no contexto de console do snapshot).
 */
class RankingService
{
    /**
     * Ranking de unidades do clube no ano informado, ordenado por pontos (desc).
     *
     * @return Collection<int, array{id:int,nome:string,membros:int,pontos:int,media:float,detalhes:array}>
     */
    public function unidades(int $clubId, int $year): Collection
    {
        return Unidade::where('club_id', $clubId)
            ->where('no_ranking', true)
            ->with([
                'desbravadores:id,nome,unidade_id,ativo',
                'desbravadores.frequencias' => $this->frequenciasLoader($year),
            ])
            ->orderBy('nome')
            ->get(['id', 'nome', 'club_id', 'no_ranking'])
            ->map(function (Unidade $unidade) {
                $detalhes = $this->agregarDetalhes($unidade->desbravadores->flatMap->frequencias);
                $membros = $unidade->desbravadores->count();

                return [
                    'id' => $unidade->id,
                    'nome' => $unidade->nome,
                    'membros' => $membros,
                    'pontos' => $detalhes['total'],
                    'media' => $membros > 0 ? round($detalhes['total'] / $membros, 1) : 0.0,
                    'detalhes' => $detalhes,
                ];
            })
            ->sortByDesc('pontos')
            ->values();
    }

    /**
     * Ranking individual de desbravadores ativos do clube no ano informado.
     *
     * @return Collection<int, array{id:int,nome:string,unidade_id:?int,unidade:string,presencas:int,pontos:int,detalhes:array}>
     */
    public function desbravadores(int $clubId, int $year): Collection
    {
        return Desbravador::with([
            'unidade:id,nome,no_ranking',
            'frequencias' => $this->frequenciasLoader($year),
        ])
            ->where('ativo', true)
            ->whereHas('unidade', fn ($q) => $q->where('club_id', $clubId)->where('no_ranking', true))
            ->orderBy('nome')
            ->get(['id', 'nome', 'unidade_id', 'ativo'])
            ->map(function (Desbravador $desbravador) {
                $detalhes = $this->agregarDetalhes($desbravador->frequencias);

                return [
                    'id' => $desbravador->id,
                    'nome' => $desbravador->nome,
                    'unidade_id' => $desbravador->unidade_id,
                    'unidade' => $desbravador->unidade->nome ?? 'Sem unidade',
                    'presencas' => $desbravador->frequencias->where('presente', true)->count(),
                    'pontos' => $detalhes['total'],
                    'detalhes' => $detalhes,
                ];
            })
            ->sortByDesc('pontos')
            ->values();
    }

    /**
     * Persiste o snapshot anual (unidades + desbravadores) do clube. Usado pelo
     * comando agendado ranking:snapshot e pela ação manual em RankingController.
     */
    public function snapshot(int $clubId, int $year, ?int $generatedBy = null): void
    {
        $unidades = $this->unidades($clubId, $year)
            ->map(fn (array $u) => [
                'id' => $u['id'],
                'nome' => $u['nome'],
                'pontos' => $u['pontos'],
                'detalhes' => $u['detalhes'],
            ])
            ->all();

        $desbravadores = $this->desbravadores($clubId, $year)
            ->map(fn (array $d) => [
                'id' => $d['id'],
                'nome' => $d['nome'],
                'unidade' => $d['unidade'],
                'pontos' => $d['pontos'],
                'detalhes' => $d['detalhes'],
            ])
            ->all();

        RankingSnapshot::updateOrCreate(
            ['year' => $year, 'scope' => 'unidades', 'club_id' => $clubId],
            ['generated_by' => $generatedBy, 'entries' => $unidades, 'generated_at' => now()]
        );

        RankingSnapshot::updateOrCreate(
            ['year' => $year, 'scope' => 'desbravadores', 'club_id' => $clubId],
            ['generated_by' => $generatedBy, 'entries' => $desbravadores, 'generated_at' => now()]
        );
    }

    /**
     * Eager-load das frequências do ano, com columnValues.column para o breakdown
     * de pontos sem N+1.
     */
    private function frequenciasLoader(int $year): callable
    {
        return fn ($query) => $query
            ->whereYear('data', $year)
            ->with('columnValues.column');
    }

    /**
     * Soma o detalhamento de pontos de uma coleção de frequências.
     *
     * @param  Collection<int, \App\Models\Frequencia>  $frequencias
     * @return array{presente:int,pontual:int,biblia:int,uniforme:int,total:int}
     */
    private function agregarDetalhes(Collection $frequencias): array
    {
        $stats = ['presente' => 0, 'pontual' => 0, 'biblia' => 0, 'uniforme' => 0, 'total' => 0];

        foreach ($frequencias as $frequencia) {
            foreach ($frequencia->detalhePontos() as $chave => $valor) {
                $stats[$chave] += $valor;
            }
        }

        return $stats;
    }
}
