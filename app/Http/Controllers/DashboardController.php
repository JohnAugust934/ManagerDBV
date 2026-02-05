<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Financeiro: Saldo em Caixa
        $entradas = Caixa::where('tipo', 'entrada')->sum('valor');
        $saidas = Caixa::where('tipo', 'saida')->sum('valor');
        $saldoAtual = $entradas - $saidas;

        // 2. Financeiro: Inadimplência (Mês Atual)
        $mesAtual = now()->month;
        $anoAtual = now()->year;

        $totalMensalidades = Mensalidade::where('mes', $mesAtual)
            ->where('ano', $anoAtual)
            ->count();

        $pendentes = Mensalidade::where('mes', $mesAtual)
            ->where('ano', $anoAtual)
            ->where('status', 'pendente')
            ->count();

        $taxaInadimplencia = $totalMensalidades > 0
            ? round(($pendentes / $totalMensalidades) * 100, 1)
            : 0;

        // 3. Operacional: Total de Ativos
        $totalAtivos = Desbravador::ativos()->count();

        // 4. Analytics: Gráfico de Frequência (Últimas 5 reuniões)
        // Agrupa por data e conta quantos 'presente = true' vs Total
        // CORREÇÃO: Usar 'presente = true' para compatibilidade com PostgreSQL
        $frequencias = Frequencia::select(
            'data',
            DB::raw('count(*) as total'),
            DB::raw('sum(case when presente = true then 1 else 0 end) as presentes')
        )
            ->groupBy('data')
            ->orderBy('data', 'desc')
            ->take(5)
            ->get()
            ->reverse(); // Inverte para o gráfico mostrar cronologicamente (esq -> dir)

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
