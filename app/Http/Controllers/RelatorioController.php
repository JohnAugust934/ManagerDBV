<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Caixa;
use App\Models\Patrimonio;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('relatorios.index');
    }

    public function gerarPersonalizado(Request $request)
    {
        $tipo = $request->tipo;
        $titulo = "Relatório Personalizado";
        $dados = [];
        $colunas = [];

        // 1. Fichas Médicas em Lote (NOVO)
        if ($tipo === 'fichas_medicas') {
            $query = Desbravador::query()->with('unidade');

            // Aplica os mesmos filtros de membros
            if ($request->status !== 'todos') {
                $query->where('ativo', $request->status == 'ativos');
            }
            if ($request->unidade_id) {
                $query->where('unidade_id', $request->unidade_id);
            }

            $desbravadores = $query->orderBy('nome')->get();

            // Usa uma view específica para lote com quebra de página
            $pdf = Pdf::loadView('relatorios.fichas_medicas_lote', compact('desbravadores'));
            return $pdf->stream('fichas_medicas_lote.pdf');
        }

        // 2. Outros Relatórios (Lista, Caixa, Unidades)
        switch ($tipo) {
            case 'desbravadores': // Lista Simples
                $query = Desbravador::query()->with('unidade');

                if ($request->status !== 'todos') {
                    $query->where('ativo', $request->status == 'ativos');
                }
                if ($request->unidade_id) {
                    $query->where('unidade_id', $request->unidade_id);
                }

                $titulo = "Relatório de Desbravadores";
                $colunas = ['Nome', 'Unidade', 'Classe', 'Idade'];

                $dados = $query->orderBy('nome')->get()->map(function ($d) {
                    return [
                        $d->nome,
                        $d->unidade->nome ?? '-',
                        $d->classe_atual,
                        \Carbon\Carbon::parse($d->data_nascimento)->age . ' anos'
                    ];
                });
                break;

            case 'caixa':
                $query = Caixa::query();

                if ($request->data_inicio) {
                    $query->whereDate('data_movimentacao', '>=', $request->data_inicio);
                }
                if ($request->data_fim) {
                    $query->whereDate('data_movimentacao', '<=', $request->data_fim);
                }
                if ($request->tipo_movimentacao !== 'todos') {
                    $query->where('tipo', $request->tipo_movimentacao);
                }

                $titulo = "Relatório de Fluxo de Caixa";
                $colunas = ['Data', 'Descrição', 'Tipo', 'Valor'];

                $dados = $query->orderBy('data_movimentacao', 'desc')->get()->map(function ($c) {
                    return [
                        $c->data_movimentacao->format('d/m/Y'),
                        $c->descricao,
                        ucfirst($c->tipo),
                        'R$ ' . number_format($c->valor, 2, ',', '.')
                    ];
                });
                break;

            case 'unidades':
                $dados = Unidade::withCount('desbravadores')->get()->map(function ($u) {
                    return [
                        $u->nome,
                        $u->conselheiro ?? 'Vago',
                        $u->desbravadores_count . ' membros'
                    ];
                });
                $titulo = "Relatório de Unidades";
                $colunas = ['Nome', 'Conselheiro', 'Qtd. Membros'];
                break;
        }

        $pdf = Pdf::loadView('relatorios.custom', compact('titulo', 'colunas', 'dados'));
        return $pdf->stream('relatorio_personalizado.pdf');
    }

    // --- MÉTODOS FIXOS ---

    public function financeiro()
    {
        $movimentacoes = Caixa::orderBy('data_movimentacao', 'desc')->get();
        $lancamentos = $movimentacoes;
        $entradas = $movimentacoes->where('tipo', 'entrada')->sum('valor');
        $saidas = $movimentacoes->where('tipo', 'saida')->sum('valor');
        $saldo = $entradas - $saidas;

        return Pdf::loadView('relatorios.financeiro', compact('movimentacoes', 'lancamentos', 'entradas', 'saidas', 'saldo'))
            ->stream('relatorio_financeiro.pdf');
    }

    public function patrimonio()
    {
        $itens = Patrimonio::orderBy('item')->get();
        $totalValor = $itens->sum('valor_estimado');

        return Pdf::loadView('relatorios.patrimonio', compact('itens', 'totalValor'))
            ->stream('relatorio_patrimonio.pdf');
    }

    public function autorizacao(Desbravador $desbravador)
    {
        return Pdf::loadView('relatorios.autorizacao', compact('desbravador'))
            ->stream("autorizacao_{$desbravador->nome}.pdf");
    }

    public function carteirinha(Desbravador $desbravador)
    {
        $desbravador->load('unidade', 'especialidades');
        return Pdf::loadView('relatorios.carteirinha', compact('desbravador'))
            ->setPaper('a4', 'portrait')
            ->stream("carteirinha_{$desbravador->nome}.pdf");
    }

    public function fichaMedica(Desbravador $desbravador)
    {
        return Pdf::loadView('relatorios.ficha_medica', compact('desbravador'))
            ->stream("ficha_medica_{$desbravador->nome}.pdf");
    }
}
