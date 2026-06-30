<?php

namespace App\Services;

use App\Models\Desbravador;
use App\Models\Unidade;
use Illuminate\Support\Collection;

/**
 * Fonte ÚNICA da pontuação de ranking. Antes o cálculo vivia duplicado em
 * RankingController (telas ao vivo) e AppServiceProvider::snapshotRankingYear
 * (snapshot agendado), com risco de divergência — o snapshot somava todos os
 * column_values, enquanto a tela só contava os marcados (checked). Aqui a regra
 * é uma só (checked-aware), usada pelos dois.
 *
 * As consultas recebem o clube explicitamente (contexto de console não tem
 * ClubContext/global scope ativos) e filtram no_ranking = true + ativo.
 */
class RankingScorer
{
    /**
     * Closure de eager-load das frequências do ano (com colunas de chamada).
     */
    public function frequenciasLoader(int $year): \Closure
    {
        return fn ($query) => $query->whereYear('data', $year)->with('columnValues.column');
    }

    /**
     * Unidades participantes do clube, com desbravadores e frequências do ano.
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
            ->get(['id', 'nome', 'club_id', 'no_ranking']);
    }

    /**
     * Desbravadores ativos de unidades participantes do clube, com frequências do ano.
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
            ->get(['id', 'nome', 'unidade_id', 'ativo']);
    }

    /**
     * Estatísticas de pontuação de uma coleção de desbravadores: total + quebra
     * por coluna fixa (presente/pontual/biblia/uniforme). Conta apenas colunas
     * marcadas (checked); cai no cálculo legado quando não há column_values.
     *
     * @return array{presente:int, pontual:int, biblia:int, uniforme:int, total:int}
     */
    public function stats($desbravadores): array
    {
        $stats = [
            'presente' => 0,
            'pontual' => 0,
            'biblia' => 0,
            'uniforme' => 0,
            'total' => 0,
        ];

        foreach ($desbravadores as $dbv) {
            foreach ($dbv->frequencias as $freq) {
                if ($freq->columnValues->isNotEmpty()) {
                    foreach ($freq->columnValues as $columnValue) {
                        if (! $columnValue->checked) {
                            continue;
                        }

                        $points = (int) $columnValue->points_awarded;
                        $stats['total'] += $points;

                        $columnKey = $columnValue->column?->key;
                        if (in_array($columnKey, ['presente', 'pontual', 'biblia', 'uniforme'], true)) {
                            $stats[$columnKey] += $points;
                        }
                    }

                    continue;
                }

                // Fallback para registros legados sem column_values.
                if ($freq->presente) {
                    $stats['presente'] += 10;
                    $stats['total'] += 10;
                }
                if ($freq->pontual) {
                    $stats['pontual'] += 5;
                    $stats['total'] += 5;
                }
                if ($freq->biblia) {
                    $stats['biblia'] += 5;
                    $stats['total'] += 5;
                }
                if ($freq->uniforme) {
                    $stats['uniforme'] += 10;
                    $stats['total'] += 10;
                }
            }
        }

        return $stats;
    }
}
