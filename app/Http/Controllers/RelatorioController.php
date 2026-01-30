<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Caixa;
use App\Models\Patrimonio;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    // Método de Autorização
    public function autorizacao(Desbravador $desbravador)
    {
        $pdf = Pdf::loadView('relatorios.autorizacao', compact('desbravador'));
        return $pdf->stream("autorizacao_{$desbravador->nome}.pdf");
    }

    // Método Carteirinha
    public function carteirinha(Desbravador $desbravador)
    {
        $desbravador->load('unidade', 'especialidades');

        $pdf = Pdf::loadView('relatorios.carteirinha', compact('desbravador'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("carteirinha_{$desbravador->nome}.pdf");
    }

    // Método Ficha Médica
    public function fichaMedica(Desbravador $desbravador)
    {
        $pdf = Pdf::loadView('relatorios.ficha_medica', compact('desbravador'));
        return $pdf->stream("ficha_medica_{$desbravador->nome}.pdf");
    }

    // Método Financeiro (CORRIGIDO)
    public function financeiro()
    {
        $movimentacoes = Caixa::orderBy('data_movimentacao', 'desc')->get();

        // CORREÇÃO: Passar explicitamente $lancamentos E $movimentacoes para evitar erro na view
        $lancamentos = $movimentacoes;

        $entradas = $movimentacoes->where('tipo', 'entrada')->sum('valor');
        $saidas = $movimentacoes->where('tipo', 'saida')->sum('valor');
        $saldo = $entradas - $saidas;

        return Pdf::loadView('relatorios.financeiro', [
            'movimentacoes' => $movimentacoes,
            'lancamentos' => $lancamentos, // Garantia dupla
            'entradas' => $entradas,
            'saidas' => $saidas,
            'saldo' => $saldo
        ])->stream('relatorio_financeiro.pdf');
    }

    // Método Patrimônio
    public function patrimonio()
    {
        $itens = Patrimonio::orderBy('item')->get();
        $totalValor = $itens->sum('valor_estimado');

        $pdf = Pdf::loadView('relatorios.patrimonio', compact('itens', 'totalValor'));
        return $pdf->stream('relatorio_patrimonio.pdf');
    }
}
