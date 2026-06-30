<?php

namespace App\Http\Controllers;

use App\Models\RankingSnapshot;
use App\Services\ClubContext;
use App\Services\RankingScorer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RankingController extends Controller
{
    public function __construct(private RankingScorer $scorer) {}

    public function unidades()
    {
        Gate::authorize('relatorios');

        $clubId = ClubContext::currentClubId();
        $ano = now()->year;

        $data = $this->scorer->unidades($clubId, $ano)
            ->map(function ($unidade) {
                $stats = $this->scorer->stats($unidade->desbravadores);

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

        $clubId = ClubContext::currentClubId();
        $ano = now()->year;

        $data = $this->scorer->desbravadores($clubId, $ano)
            ->map(function ($dbv) {
                $stats = $this->scorer->stats(collect([$dbv]));

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
        $clubId = ClubContext::currentClubId();

        if ($scope === 'unidades') {
            $entries = $this->scorer->unidades($clubId, $year)
                ->map(function ($unidade) {
                    $stats = $this->scorer->stats($unidade->desbravadores);

                    return ['id' => $unidade->id, 'nome' => $unidade->nome, 'pontos' => $stats['total'], 'detalhes' => $stats];
                })
                ->sortByDesc('pontos')
                ->values()
                ->toArray();
        } else {
            $entries = $this->scorer->desbravadores($clubId, $year)
                ->map(function ($dbv) {
                    $stats = $this->scorer->stats(collect([$dbv]));

                    return ['id' => $dbv->id, 'nome' => $dbv->nome, 'unidade' => $dbv->unidade->nome ?? '-', 'pontos' => $stats['total'], 'detalhes' => $stats];
                })
                ->sortByDesc('pontos')
                ->values()
                ->toArray();
        }

        RankingSnapshot::updateOrCreate(
            ['year' => $year, 'scope' => $scope, 'club_id' => $clubId],
            ['generated_by' => auth()->id(), 'entries' => $entries, 'generated_at' => now()]
        );

        return back()->with('success', "Snapshot do ranking de {$year} ({$scope}) salvo com sucesso!");
    }

    public function verSnapshot(Request $request, string $scope)
    {
        Gate::authorize('relatorios');

        $year = (int) $request->input('year', now()->year - 1);
        $clubId = ClubContext::currentClubId();

        $snapshot = RankingSnapshot::where('scope', $scope)
            ->where('year', $year)
            ->where('club_id', $clubId)
            ->first();

        $anosDisponiveis = RankingSnapshot::where('scope', $scope)
            ->where('club_id', $clubId)
            ->orderByDesc('year')
            ->pluck('year');

        return view('ranking.snapshot', compact('snapshot', 'scope', 'year', 'anosDisponiveis'));
    }

    private function renderView($data, $titulo, $ano, string $scope)
    {
        $top3 = $data->take(3);
        $demais = $data->skip(3);

        $snapshotsDisponiveis = RankingSnapshot::where('scope', $scope)
            ->where('club_id', ClubContext::currentClubId())
            ->orderByDesc('year')
            ->pluck('year');

        return view('ranking.index', compact('data', 'top3', 'demais', 'titulo', 'ano', 'scope', 'snapshotsDisponiveis'));
    }

    private function getCorUnidade($id): string
    {
        $colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'];

        return $colors[$id % count($colors)];
    }
}
