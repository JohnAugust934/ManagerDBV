<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RankingController extends Controller
{
    public function unidades()
    {
        Gate::authorize('relatorios');

        $clubId = auth()->user()->club_id;
        $ano = now()->year;

        // Schema::hasTable removido — tabela frequencia_column_values existe desde a migration
        // 2026_04_13_100100 e está sempre presente em produção.
        $frequenciasLoader = function ($query) use ($ano) {
            $query->whereYear('data', $ano)->with('columnValues.column');
        };

        $data = Unidade::where('club_id', $clubId)
            ->where('no_ranking', true)
            ->with(['desbravadores.frequencias' => $frequenciasLoader])
            ->get()
            ->map(function ($unidade) {
                $stats = $this->calcularPontos($unidade->desbravadores);

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

        return $this->renderView($data, 'Ranking das Unidades', $ano, 'unidades');
    }

    public function desbravadores()
    {
        Gate::authorize('relatorios');

        $ano = now()->year;

        $frequenciasLoader = function ($query) use ($ano) {
            $query->whereYear('data', $ano)->with('columnValues.column');
        };

        // GlobalScope DesbravadorClubScope aplica o filtro de clube automaticamente.
        $data = Desbravador::with([
            'unidade',
            'frequencias' => $frequenciasLoader,
        ])
            ->where('ativo', true)
            ->whereHas('unidade', fn ($q) => $q->where('no_ranking', true))
            ->get()
            ->map(function ($dbv) {
                $stats = $this->calcularPontos(collect([$dbv]));

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

        return $this->renderView($data, 'Ranking Individual', $ano, 'desbravadores');
    }

    public function salvarSnapshot(Request $request)
    {
        Gate::authorize('relatorios');

        $request->validate([
            'scope' => 'required|in:unidades,desbravadores',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $scope = $request->scope;
        $year = (int) $request->year;
        $clubId = auth()->user()->club_id;

        $frequenciasLoader = fn ($q) => $q->whereYear('data', $year)->with('columnValues.column');

        if ($scope === 'unidades') {
            $entries = Unidade::where('club_id', $clubId)
                ->where('no_ranking', true)
                ->with(['desbravadores.frequencias' => $frequenciasLoader])
                ->get()
                ->map(function ($unidade) {
                    $stats = $this->calcularPontos($unidade->desbravadores);
                    return ['id' => $unidade->id, 'nome' => $unidade->nome, 'pontos' => $stats['total'], 'detalhes' => $stats];
                })
                ->sortByDesc('pontos')
                ->values()
                ->toArray();
        } else {
            $entries = Desbravador::with(['unidade', 'frequencias' => $frequenciasLoader])
                ->where('ativo', true)
                ->whereHas('unidade', fn ($q) => $q->where('no_ranking', true))
                ->get()
                ->map(function ($dbv) {
                    $stats = $this->calcularPontos(collect([$dbv]));
                    return ['id' => $dbv->id, 'nome' => $dbv->nome, 'unidade' => $dbv->unidade->nome ?? '-', 'pontos' => $stats['total'], 'detalhes' => $stats];
                })
                ->sortByDesc('pontos')
                ->values()
                ->toArray();
        }

        RankingSnapshot::updateOrCreate(
            ['year' => $year, 'scope' => $scope, 'generated_by' => auth()->id()],
            ['entries' => $entries, 'generated_at' => now()]
        );

        return back()->with('success', "Snapshot do ranking de {$year} ({$scope}) salvo com sucesso!");
    }

    public function verSnapshot(Request $request, string $scope)
    {
        Gate::authorize('relatorios');

        $year = (int) $request->input('year', now()->year - 1);

        $snapshot = RankingSnapshot::where('scope', $scope)
            ->where('year', $year)
            ->first();

        $anosDisponiveis = RankingSnapshot::where('scope', $scope)
            ->orderByDesc('year')
            ->pluck('year');

        return view('ranking.snapshot', compact('snapshot', 'scope', 'year', 'anosDisponiveis'));
    }

    private function renderView($data, $titulo, $ano, string $scope)
    {
        $top3 = $data->take(3);
        $demais = $data->skip(3);

        $snapshotsDisponiveis = RankingSnapshot::where('scope', $scope)
            ->orderByDesc('year')
            ->pluck('year');

        return view('ranking.index', compact('data', 'top3', 'demais', 'titulo', 'ano', 'scope', 'snapshotsDisponiveis'));
    }

    private function calcularPontos($desbravadores): array
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

    private function getCorUnidade($id): string
    {
        $colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'];

        return $colors[$id % count($colors)];
    }
}
