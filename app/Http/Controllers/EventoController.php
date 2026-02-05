<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Evento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoController extends Controller
{
    // ... index, create, store, edit, update (MANTIDOS IGUAIS) ...
    public function index()
    {
        $eventos = Evento::withCount('desbravadores')->orderBy('data_inicio', 'desc')->paginate(9);

        return view('eventos.index', compact('eventos'));
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'local' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'valor' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);
        Evento::create($dados);

        return redirect()->route('eventos.index')->with('success', 'Evento criado!');
    }

    public function show(Evento $evento)
    {
        $evento->load(['desbravadores.unidade']);

        // CORREÇÃO: Carrega quem NÃO está inscrito para o select
        $inscritosIds = $evento->desbravadores->pluck('id');
        $naoInscritos = Desbravador::where('ativo', true)
            ->whereNotIn('id', $inscritosIds)
            ->orderBy('nome')
            ->get();

        return view('eventos.show', compact('evento', 'naoInscritos'));
    }

    public function edit(Evento $evento)
    {
        return view('eventos.edit', compact('evento'));
    }

    public function update(Request $request, Evento $evento)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'local' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'valor' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);

        $evento->update($dados);

        // ALTERADO: Redireciona para a tela de visualização (show) do evento
        return redirect()->route('eventos.show', $evento->id)->with('success', 'Evento atualizado!');
    }

    public function destroy(Evento $evento)
    {
        if ($evento->desbravadores()->count() > 0) {
            return back()->with('error', 'Não é possível excluir evento com inscritos. Remova as inscrições primeiro.');
        }
        $evento->delete();

        return redirect()->route('eventos.index')->with('success', 'Evento removido.');
    }

    // --- MÉTODOS DE INSCRIÇÃO ---

    public function inscrever(Request $request, Evento $evento)
    {
        $request->validate(['desbravador_id' => 'required|exists:desbravadores,id']);

        if (! $evento->desbravadores()->where('desbravador_id', $request->desbravador_id)->exists()) {
            $evento->desbravadores()->attach($request->desbravador_id, ['pago' => false, 'autorizacao_entregue' => false]);

            return back()->with('success', 'Inscrito com sucesso!');
        }

        return back()->with('info', 'Já estava inscrito.');
    }

    // NOVO: Inscrição em Lote
    public function inscreverEmLote(Request $request, Evento $evento)
    {
        $request->validate(['desbravadores' => 'required|array']);

        $count = 0;
        foreach ($request->desbravadores as $id) {
            if (! $evento->desbravadores()->where('desbravador_id', $id)->exists()) {
                $evento->desbravadores()->attach($id, ['pago' => false, 'autorizacao_entregue' => false]);
                $count++;
            }
        }

        return back()->with('success', "$count desbravadores inscritos!");
    }

    public function removerInscricao(Evento $evento, Desbravador $desbravador)
    {
        // Se estava pago, deveríamos estornar do caixa?
        // Por simplicidade, assumimos que o tesoureiro ajusta manual se necessário, ou removemos aqui.
        // Vamos apenas remover a inscrição.
        $evento->desbravadores()->detach($desbravador->id);

        return back()->with('success', 'Removido do evento.');
    }

    // ATUALIZADO: AJAX + Integração com Caixa
    public function atualizarStatus(Request $request, Evento $evento, Desbravador $desbravador)
    {
        $campo = $request->campo;
        $valor = filter_var($request->valor, FILTER_VALIDATE_BOOLEAN);

        if ($campo === 'pago') {
            DB::transaction(function () use ($evento, $desbravador, $valor) {
                // 1. Atualiza Pivot
                $evento->desbravadores()->updateExistingPivot($desbravador->id, ['pago' => $valor]);

                // 2. Lança no Caixa (Se tiver valor > 0)
                if ($evento->valor > 0) {
                    if ($valor) {
                        // Entrada
                        Caixa::create([
                            'descricao' => "Evento: {$evento->nome} - {$desbravador->nome}",
                            'tipo' => 'entrada',
                            'valor' => $evento->valor,
                            'data_movimentacao' => now(),
                        ]);
                    } else {
                        // Saída (Estorno)
                        Caixa::create([
                            'descricao' => "Estorno Evento: {$evento->nome} - {$desbravador->nome}",
                            'tipo' => 'saida',
                            'valor' => $evento->valor,
                            'data_movimentacao' => now(),
                        ]);
                    }
                }
            });

            // Retorno JSON para o AJAX
            return response()->json(['success' => true, 'novo_status' => $valor]);
        }

        if ($campo === 'autorizacao_entregue') {
            $evento->desbravadores()->updateExistingPivot($desbravador->id, ['autorizacao_entregue' => $valor]);

            return response()->json(['success' => true, 'novo_status' => $valor]);
        }

        return response()->json(['error' => 'Campo inválido'], 400);
    }

    public function gerarAutorizacao(Evento $evento, Desbravador $desbravador)
    {
        $pdf = Pdf::loadView('relatorios.autorizacao', [
            'desbravador' => $desbravador,
            'evento' => $evento,
        ]);

        return $pdf->stream('autorizacao.pdf');
    }
}
