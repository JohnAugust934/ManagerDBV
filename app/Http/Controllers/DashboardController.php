<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Evento;
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

        $alertas = $this->montarAlertas($clubId, $taxaInadimplencia);

        return view('dashboard', compact(
            'saldoAtual',
            'taxaInadimplencia',
            'totalAtivos',
            'labelsGrafico',
            'dadosGrafico',
            'alertas'
        ));
    }

    /**
     * Alertas proativos do painel. Sempre frescos (sem cache) e já filtrados pela
     * permissão do usuário — só consultamos um módulo se ele puder vê-lo, evitando
     * vazar contagens de áreas a que o cargo não tem acesso.
     *
     * @return array<int, array<string, mixed>>
     */
    private function montarAlertas(?int $clubId, float $taxaInadimplencia): array
    {
        $user = auth()->user();

        if (! $user || ! $clubId) {
            return [];
        }

        $alertas = [];

        if ($user->temPermissao('financeiro')) {
            if ($taxaInadimplencia > 30) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'icone' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    'titulo' => 'Inadimplência alta',
                    'texto' => "Taxa de {$taxaInadimplencia}% no mês atual. Verifique as mensalidades pendentes.",
                    'link' => route('mensalidades.index'),
                    'link_texto' => 'Ver mensalidades',
                ];
            }

            $inadimplentesAntigos = Mensalidade::doClube($clubId)->inadimplentes()->count();
            if ($inadimplentesAntigos > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icone' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'titulo' => 'Mensalidades atrasadas',
                    'texto' => "{$inadimplentesAntigos} mensalidade(s) de meses anteriores ainda pendente(s).",
                    'link' => route('mensalidades.index'),
                    'link_texto' => 'Verificar',
                ];
            }
        }

        if ($user->temPermissao('eventos')) {
            $eventosProximos = Evento::whereBetween('data_inicio', [now(), now()->addDays(7)])->count();
            if ($eventosProximos > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'icone' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'titulo' => 'Eventos nos próximos 7 dias',
                    'texto' => "{$eventosProximos} evento(s) se aproximando. Confirme inscrições e pagamentos.",
                    'link' => route('eventos.index'),
                    'link_texto' => 'Ver eventos',
                ];
            }
        }

        if ($user->temPermissao('pedagogico')) {
            $semFrequencia = Desbravador::ativos()
                ->whereDoesntHave('frequencias', fn ($q) => $q->where('data', '>=', now()->subDays(21)))
                ->count();
            if ($semFrequencia > 3) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icone' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'titulo' => 'Membros sem frequência recente',
                    'texto' => "{$semFrequencia} desbravadores sem registro de presença nos últimos 21 dias.",
                    'link' => route('frequencia.index'),
                    'link_texto' => 'Ver frequência',
                ];
            }
        }

        return $alertas;
    }
}
