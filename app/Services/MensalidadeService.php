<?php

namespace App\Services;

use App\Models\Desbravador;
use App\Models\Mensalidade;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Regras de geração em lote e pagamento de mensalidades.
 *
 * Antes a lógica de "quais desbravadores ativos ainda não têm mensalidade nesta
 * competência" estava escrita DUAS vezes (previewMassivo × gerarMassivo) e o
 * pagamento criava o lançamento de caixa inline no controller (Candidato C).
 * Aqui há uma única fonte para as duas coisas.
 */
class MensalidadeService
{
    public function __construct(private readonly LancamentoCaixaService $caixa) {}

    /**
     * Preview (dry-run): quantos ativos existem, quantos já têm mensalidade na
     * competência e quantas seriam criadas. Não persiste nada.
     *
     * @return array{total_ativos:int,ja_existem:int,serao_criadas:int}
     */
    public function preview(int $mes, int $ano): array
    {
        $totalAtivos = Desbravador::ativos()->count();
        $faltantes = $this->idsSemMensalidade($mes, $ano)->count();

        return [
            'total_ativos' => $totalAtivos,
            'ja_existem' => $totalAtivos - $faltantes,
            'serao_criadas' => $faltantes,
        ];
    }

    /**
     * Cria as mensalidades pendentes para os ativos sem mensalidade na
     * competência. Retorna a quantidade criada.
     */
    public function gerar(int $clubId, int $mes, int $ano, float $valor, ?int $autorId): int
    {
        $agora = now();

        $novas = $this->idsSemMensalidade($mes, $ano)
            ->map(fn ($id) => [
                'desbravador_id' => $id,
                'club_id' => $clubId, // insert() em massa não dispara o auto-fill do BelongsToTenant
                'mes' => $mes,
                'ano' => $ano,
                // insert() em massa nao passa pelo cast decimal:2 do model — formatamos
                // aqui para nao persistir um float impreciso na coluna decimal(10,2).
                'valor' => number_format($valor, 2, '.', ''),
                'status' => 'pendente',
                // insert() também não dispara RegistraAutoria — preenchemos manualmente.
                'created_by' => $autorId,
                'updated_by' => $autorId,
                'created_at' => $agora,
                'updated_at' => $agora,
            ])
            ->values()
            ->all();

        if (! empty($novas)) {
            Mensalidade::insert($novas);
        }

        return count($novas);
    }

    /**
     * Marca a mensalidade como paga e lança a entrada correspondente no caixa,
     * atomicamente.
     */
    public function pagar(Mensalidade $mensalidade, int $clubId): void
    {
        DB::transaction(function () use ($mensalidade, $clubId) {
            $mensalidade->update([
                'status' => 'pago',
                'data_pagamento' => Carbon::now(),
            ]);

            $descricao = 'Mensalidade '.str_pad((string) $mensalidade->mes, 2, '0', STR_PAD_LEFT)
                .'/'.$mensalidade->ano.' - '.$mensalidade->desbravador->nome;

            $this->caixa->entrada('Mensalidade', $descricao, $mensalidade->valor, $clubId);
        });
    }

    /**
     * IDs dos desbravadores ativos do clube (via ClubScope) sem mensalidade na
     * competência informada. Fonte única de preview() e gerar().
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function idsSemMensalidade(int $mes, int $ano): \Illuminate\Support\Collection
    {
        $ids = Desbravador::ativos()->pluck('id');

        $existentes = Mensalidade::whereIn('desbravador_id', $ids)
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->pluck('desbravador_id')
            ->flip();

        return $ids->reject(fn ($id) => $existentes->has($id))->values();
    }
}
