<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use App\Services\ClubContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $clubId = ClubContext::currentClubId();
        $mesAtual = now()->month;
        $anoAtual = now()->year;

        // Totais financeiros e de membros: cache de 5 minutos por clube.
        // Invalida automaticamente ao expirar; operações de escrita (store/update)
        // não precisam invalidar manualmente — a defasagem de 5 min é aceitável no dashboard.
        $resumo = Cache::remember("dashboard_resumo_{$clubId}_{$mesAtual}_{$anoAtual}", 300, function () use ($clubId, $mesAtual, $anoAtual) {
            $entradas = Caixa::where('tipo', 'entrada')->sum('valor');
            $saidas = Caixa::where('tipo', 'saida')->sum('valor');

            $totalMensalidades = Mensalidade::doClube($clubId)
                ->where('mes', $mesAtual)
                ->where('ano', $anoAtual)
                ->count();

            $pendentes = Mensalidade::doClube($clubId)
                ->where('mes', $mesAtual)
                ->where('ano', $anoAtual)
                ->where('status', 'pendente')
                ->count();

            return [
                'saldo_atual' => $entradas - $saidas,
                'total_mensalidades' => $totalMensalidades,
                'pendentes' => $pendentes,
                'total_ativos' => Desbravador::ativos()->count(),
            ];
        });

        $saldoAtual = $resumo['saldo_atual'];
        $totalAtivos = $resumo['total_ativos'];
        $taxaInadimplencia = $resumo['total_mensalidades'] > 0
            ? round(($resumo['pendentes'] / $resumo['total_mensalidades']) * 100, 1)
            : 0;

        // Gráfico de frequência: os últimos 5 registros mudam com frequência —
        // cache separado com TTL menor (60s) para não atrasar visualmente.
        $frequencias = Cache::remember("dashboard_frequencias_{$clubId}", 60, function () use ($clubId) {
            return Frequencia::select(
                'data',
                DB::raw('count(*) as total'),
                DB::raw('sum(case when presente = true then 1 else 0 end) as presentes')
            )
                ->whereHas('desbravador.unidade', fn ($q) => $q->where('club_id', $clubId))
                ->groupBy('data')
                ->orderBy('data', 'desc')
                ->take(5)
                ->get()
                ->reverse();
        });

        $labelsGrafico = $frequencias->map(fn ($f) => Carbon::parse($f->data)->format('d/m'));
        $dadosGrafico = $frequencias->map(fn ($f) => $f->total > 0 ? round(($f->presentes / $f->total) * 100) : 0);

        return view('dashboard', compact(
            'saldoAtual',
            'taxaInadimplencia',
            'totalAtivos',
            'labelsGrafico',
            'dadosGrafico'
        ));
    }
}
