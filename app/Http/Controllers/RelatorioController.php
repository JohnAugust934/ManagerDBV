<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Caixa;
use App\Models\Patrimonio;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    // 1. Autorização de Saída (Gera PDF de um desbravador específico)
    public function autorizacaoSaida($id)
    {
        $desbravador = Desbravador::with('unidade')->findOrFail($id);

        // Carrega a view e passa os dados
        $pdf = Pdf::loadView('relatorios.autorizacao', compact('desbravador'));

        // Retorna o PDF para visualizar no navegador (stream)
        return $pdf->stream("autorizacao_{$desbravador->nome}.pdf");
    }

    // 2. Relatório Financeiro (Fluxo de Caixa Completo)
    public function financeiro()
    {
        $lancamentos = Caixa::orderBy('data_movimentacao', 'asc')->get();

        $entradas = $lancamentos->where('tipo', 'entrada')->sum('valor');
        $saidas = $lancamentos->where('tipo', 'saida')->sum('valor');
        $saldo = $entradas - $saidas;

        $pdf = Pdf::loadView('relatorios.financeiro', compact('lancamentos', 'entradas', 'saidas', 'saldo'));

        return $pdf->stream('relatorio_financeiro.pdf');
    }

    // 3. Relatório de Patrimônio
    public function patrimonio()
    {
        $itens = Patrimonio::orderBy('item')->get();
        $totalValor = $itens->sum(fn($i) => $i->quantidade * $i->valor_estimado);

        $pdf = Pdf::loadView('relatorios.patrimonio', compact('itens', 'totalValor'));

        return $pdf->stream('relatorio_patrimonio.pdf');
    }
}
