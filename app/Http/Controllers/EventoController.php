<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Desbravador;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EventoController extends Controller
{
    public function index()
    {
        // Ordena eventos futuros primeiro
        $eventos = Evento::orderBy('data_inicio', 'desc')->get();
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
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'valor' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);

        Evento::create($dados);

        return redirect()->route('eventos.index')->with('success', 'Evento criado com sucesso!');
    }

    public function show(Evento $evento)
    {
        // Carrega inscritos e também todos os desbravadores para o select de inscrição
        $evento->load(['desbravadores' => function ($q) {
            $q->orderBy('nome');
        }]);

        // Lista de quem NÃO está inscrito ainda (apenas ativos)
        $naoInscritos = Desbravador::ativos()
            ->whereDoesntHave('eventos', function ($q) use ($evento) {
                $q->where('evento_id', $evento->id);
            })
            ->orderBy('nome')
            ->get();

        return view('eventos.show', compact('evento', 'naoInscritos'));
    }

    // --- Métodos de Inscrição ---

    public function inscrever(Request $request, Evento $evento)
    {
        $request->validate(['desbravador_id' => 'required|exists:desbravadores,id']);

        $evento->desbravadores()->attach($request->desbravador_id, [
            'pago' => false,
            'autorizacao_entregue' => false
        ]);

        return back()->with('success', 'Inscrição realizada!');
    }

    public function removerInscricao(Evento $evento, Desbravador $desbravador)
    {
        $evento->desbravadores()->detach($desbravador->id);
        return back()->with('success', 'Inscrição removida.');
    }

    public function atualizarStatus(Request $request, Evento $evento, Desbravador $desbravador)
    {
        // Atualiza pivot (Pago / Autorização)
        $campo = $request->campo; // 'pago' ou 'autorizacao_entregue'
        $valor = $request->valor == '1';

        if (in_array($campo, ['pago', 'autorizacao_entregue'])) {
            $evento->desbravadores()->updateExistingPivot($desbravador->id, [
                $campo => $valor
            ]);
        }

        return back()->with('success', 'Status atualizado!');
    }

    // --- PDF ---

    public function gerarAutorizacao(Evento $evento, Desbravador $desbravador)
    {
        // Usa a view existente mas injetando dados do evento
        $pdf = Pdf::loadView('relatorios.autorizacao', [
            'desbravador' => $desbravador,
            'evento' => $evento // Passa o evento para preencher local e data
        ]);

        return $pdf->stream("autorizacao_{$evento->nome}_{$desbravador->nome}.pdf");
    }
}
