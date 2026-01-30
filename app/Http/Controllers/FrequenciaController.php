<?php

namespace App\Http\Controllers;

use App\Models\Frequencia;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FrequenciaController extends Controller
{
    public function create()
    {
        // Busca unidades que o usuário pode gerenciar
        $unidades = Unidade::with(['desbravadores' => function ($q) {
            $q->where('ativo', true)->orderBy('nome');
        }])->get()->filter(function ($unidade) {
            return Gate::allows('gerir-unidade', $unidade);
        });

        return view('frequencia.create', compact('unidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|date',
            'unidade_id' => 'required|exists:unidades,id',
            'presencas' => 'required|array'
        ]);

        $unidade = Unidade::findOrFail($request->unidade_id);

        // Verifica permissão (Master/Diretor passam direto, Conselheiro valida nome)
        if (Gate::denies('gerir-unidade', $unidade)) {
            abort(403, 'Você não tem permissão para esta unidade.');
        }

        foreach ($request->presencas as $id => $dados) {
            Frequencia::updateOrCreate(
                [
                    'desbravador_id' => $id,
                    'data' => $request->data
                ],
                [
                    // isset() funciona para checkboxes que não enviam valor quando desmarcados
                    'presente' => isset($dados['presente']),
                    'pontual' => isset($dados['pontual']),
                    'biblia' => isset($dados['biblia']),
                    'uniforme' => isset($dados['uniforme']),
                ]
            );
        }

        return redirect()->route('dashboard')->with('success', 'Chamada realizada com sucesso!');
    }
}
