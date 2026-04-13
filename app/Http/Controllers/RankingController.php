<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Unidade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class RankingController extends Controller
{
    public function unidades()
    {
        Gate::authorize('relatorios');

        $ano = now()->year;
        $hasColumnValues = Schema::hasTable('frequencia_column_values');

        $frequenciasLoader = function ($query) use ($ano, $hasColumnValues) {
            $query->whereYear('data', $ano);
            if ($hasColumnValues) {
                $query->with('columnValues.column');
            }
        };

        $data = Unidade::with([
            'desbravadores.frequencias' => $frequenciasLoader,
        ])
            ->get()
            ->map(function ($unidade) use ($hasColumnValues) {
                $stats = $this->calcularPontos($unidade->desbravadores, $hasColumnValues);

                return (object) [
                    'id' => $unidade->id,
                    'nome' => $unidade->nome,
                    'subtexto' => $unidade->desbravadores->count().' membros',
                    'cor' => $this->getCorUnidade($unidade->id),
                    'pontos' => $stats['total'],
                    'detalhes' => $stats,
                    'tipo' => 'unidade',
                ];
            })
            ->sortByDesc('pontos')
            ->values();

        return $this->renderView($data, 'Ranking das Unidades', $ano);
    }

    public function desbravadores()
    {
        Gate::authorize('relatorios');

        $ano = now()->year;
        $hasColumnValues = Schema::hasTable('frequencia_column_values');

        $frequenciasLoader = function ($query) use ($ano, $hasColumnValues) {
            $query->whereYear('data', $ano);
            if ($hasColumnValues) {
                $query->with('columnValues.column');
            }
        };

        $data = Desbravador::with([
            'unidade',
            'frequencias' => $frequenciasLoader,
        ])
            ->where('ativo', true)
            ->get()
            ->map(function ($dbv) use ($hasColumnValues) {
                $stats = $this->calcularPontos(collect([$dbv]), $hasColumnValues);

                return (object) [
                    'id' => $dbv->id,
                    'nome' => $dbv->nome,
                    'subtexto' => $dbv->unidade->nome ?? 'Sem Unidade',
                    'cor' => $this->getCorUnidade($dbv->unidade_id ?? 0),
                    'pontos' => $stats['total'],
                    'detalhes' => $stats,
                    'tipo' => 'desbravador',
                ];
            })
            ->sortByDesc('pontos')
            ->values();

        return $this->renderView($data, 'Ranking Individual', $ano);
    }

    private function renderView($data, $titulo, $ano)
    {
        $top3 = $data->take(3);
        $demais = $data->skip(3);

        return view('ranking.index', compact('data', 'top3', 'demais', 'titulo', 'ano'));
    }

    private function calcularPontos($desbravadores, bool $hasColumnValues): array
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
                if ($hasColumnValues && $freq->columnValues->isNotEmpty()) {
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

                // Fallback para registros legados criados antes das colunas dinamicas.
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

    private function getCorUnidade($id): string
    {
        $colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'];

        return $colors[$id % count($colors)];
    }
}
