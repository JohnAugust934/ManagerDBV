<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Unidade;
use Illuminate\Support\Facades\Gate;

class RankingController extends Controller
{
    public function unidades()
    {
        Gate::authorize('relatorios');

        $ano = now()->year;

        $data = Unidade::with(['desbravadores.frequencias' => fn ($query) => $query->whereYear('data', $ano)])
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
                    'tipo' => 'unidade', // Para ícone na view
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

        $data = Desbravador::with(['unidade', 'frequencias' => fn ($query) => $query->whereYear('data', $ano)])
            ->where('ativo', true)
            ->get()
            ->map(function ($dbv) {
                // Passamos uma coleção com 1 item para reaproveitar a função de cálculo
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

        return $this->renderView($data, 'Ranking Individual', $ano);
    }

    private function renderView($data, $titulo, $ano)
    {
        $top3 = $data->take(3);
        $demais = $data->skip(3);

        return view('ranking.index', compact('data', 'top3', 'demais', 'titulo', 'ano'));
    }

    private function calcularPontos($desbravadores)
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
                if ($freq->presente) {
                    $stats['presente'] += 10;
                }
                if ($freq->pontual) {
                    $stats['pontual'] += 5;
                }
                if ($freq->biblia) {
                    $stats['biblia'] += 5;
                }
                if ($freq->uniforme) {
                    $stats['uniforme'] += 10;
                }
            }
        }
        $stats['total'] = array_sum($stats);

        return $stats;
    }

    private function getCorUnidade($id)
    {
        $colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'];

        return $colors[$id % count($colors)];
    }
}
