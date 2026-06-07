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
        $clubId = auth()->user()->club_id;

        // Caixa usa GlobalScope (ClubScope) — filtra por club_id automaticamente.
        $entradas = Caixa::where('tipo', 'entrada')->sum('valor');
        $saidas = Caixa::where('tipo', 'saida')->sum('valor');
        $saldoAtual = $entradas - $saidas;

        $mesAtual = now()->month;
        $anoAtual = now()->year;

        // Mensalidade não tem GlobalScope — filtra via desbravador.unidade.club_id.
        $totalMensalidades = Mensalidade::doClube($clubId)
            ->where('mes', $mesAtual)
            ->where('ano', $anoAtual)
            ->count();

        $pendentes = Mensalidade::doClube($clubId)
            ->where('mes', $mesAtual)
            ->where('ano', $anoAtual)
            ->where('status', 'pendente')
            ->count();

        $taxaInadimplencia = $totalMensalidades > 0
            ? round(($pendentes / $totalMensalidades) * 100, 1)
            : 0;

        // Desbravador usa GlobalScope (DesbravadorClubScope).
        $totalAtivos = Desbravador::ativos()->count();

        // Frequência — escopa via desbravador do clube.
        $frequencias = Frequencia::select(
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
