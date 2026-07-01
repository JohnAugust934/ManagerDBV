<?php

namespace App\Http\Controllers;

use App\Models\RankingSnapshot;
use App\Services\ClubContext;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RankingController extends Controller
{
    public function __construct(private readonly RankingService $ranking) {}

    public function unidades()
    {
        Gate::authorize('relatorios');

        $clubId = ClubContext::currentClubId();
        $ano = now()->year;

        $data = $this->ranking->unidades($clubId, $ano)->map(fn (array $u) => (object) [
            'id' => $u['id'],
            'nome' => $u['nome'],
            'subtexto' => $u['membros'].' membros',
            'cor' => $this->getCorUnidade($u['id']),
            'pontos' => $u['pontos'],
            'detalhes' => $u['detalhes'],
            'tipo' => 'unidade',
        ]);

        return $this->renderView($data, 'Ranking das Unidades', $ano, 'unidades');
    }

    public function desbravadores()
    {
        Gate::authorize('relatorios');

        $clubId = ClubContext::currentClubId();
        $ano = now()->year;

        $data = $this->ranking->desbravadores($clubId, $ano)->map(fn (array $d) => (object) [
            'id' => $d['id'],
            'nome' => $d['nome'],
            'subtexto' => $d['unidade'],
            'cor' => $this->getCorUnidade($d['unidade_id'] ?? 0),
            'pontos' => $d['pontos'],
            'detalhes' => $d['detalhes'],
            'tipo' => 'desbravador',
        ]);

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

        // O serviço grava os dois escopos (unidades + desbravadores) de uma vez.
        $this->ranking->snapshot($clubId, $year, auth()->id());

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
