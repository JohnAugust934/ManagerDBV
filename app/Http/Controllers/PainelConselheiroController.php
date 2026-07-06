<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Unidade;
use App\Services\ClubContext;
use Illuminate\Support\Facades\Gate;

class PainelConselheiroController extends Controller
{
    public function index()
    {
        // Disponível para conselheiros e instrutores (permissão pedagógico).
        Gate::authorize('pedagogico');

        $user = auth()->user();
        $clubId = ClubContext::currentClubId();

        // Unidade vinculada a este conselheiro.
        $unidade = Unidade::where('conselheiro_user_id', $user->id)->first();

        if (! $unidade && ! $user->isMaster() && ! $user->is_platform_admin) {
            return redirect()->route('dashboard')
                ->with('info', 'Você não está vinculado como conselheiro de nenhuma unidade.');
        }

        // Master/Diretor/Platform pode ver todas — usa a primeira unidade do clube.
        if (! $unidade) {
            $unidade = Unidade::orderBy('nome')->first();
        }

        if (! $unidade) {
            return redirect()->route('dashboard')->with('info', 'Nenhuma unidade cadastrada.');
        }

        $membros = Desbravador::with(['classe', 'frequencias' => function ($q) {
            $q->whereYear('data', now()->year)->orderBy('data', 'desc');
        }, 'especialidades'])
            ->where('unidade_id', $unidade->id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(function ($desb) {
                $frequenciasAno = $desb->frequencias;
                $totalReunioes = $frequenciasAno->count();
                $presencas = $frequenciasAno->where('presente', true)->count();
                $taxaFreq = $totalReunioes > 0 ? (int) round($presencas / $totalReunioes * 100) : 0;
                $ultimaPresenca = $frequenciasAno->where('presente', true)->first()?->data;

                return (object) [
                    'id' => $desb->id,
                    'nome' => $desb->nome,
                    'foto_url' => $desb->foto_url,
                    'classe' => $desb->classe?->nome ?? 'Sem classe',
                    'progresso_classe' => $desb->progresso_classe,
                    'total_especialidades' => $desb->especialidades->count(),
                    'taxa_frequencia' => $taxaFreq,
                    'ultima_presenca' => $ultimaPresenca,
                    'ausente_recente' => $taxaFreq < 50
                        || ($ultimaPresenca !== null && $ultimaPresenca->lt(now()->subDays(21))),
                ];
            });

        // Últimas 5 reuniões da unidade.
        $ultimasReunioes = Frequencia::whereHas('desbravador', fn ($q) => $q->where('unidade_id', $unidade->id))
            ->selectRaw('DATE(data) as data_reuniao, COUNT(*) as total, SUM(CASE WHEN presente = 1 THEN 1 ELSE 0 END) as presentes')
            ->groupBy('data_reuniao')
            ->orderBy('data_reuniao', 'desc')
            ->take(5)
            ->get();

        return view('painel-conselheiro.index', compact('unidade', 'membros', 'ultimasReunioes'));
    }
}
