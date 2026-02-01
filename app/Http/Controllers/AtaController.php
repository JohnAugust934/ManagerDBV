<?php

namespace App\Http\Controllers;

use App\Models\Ata;
use Illuminate\Http\Request;

class AtaController extends Controller
{
    public function index(Request $request)
    {
        $query = Ata::orderBy('data_reuniao', 'desc');

        if ($request->filled('search')) {
            $query->where('pauta', 'like', "%{$request->search}%")
                ->orWhere('conteudo', 'like', "%{$request->search}%");
        }

        $atas = $query->paginate(10);

        return view('secretaria.atas.index', compact('atas'));
    }

    public function create()
    {
        return view('secretaria.atas.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'titulo' => 'required|string|max:255', // Adicionado Título ou Pauta Principal
            'data_reuniao' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim' => 'nullable',
            'local' => 'required|string',
            'conteudo' => 'required|string', // O texto completo da ata
            'participantes' => 'nullable|string', // Lista de nomes ou ids
        ]);

        Ata::create($dados);

        return redirect()->route('atas.index')->with('success', 'Ata registrada com sucesso!');
    }

    public function show(Ata $ata)
    {
        return view('secretaria.atas.show', compact('ata'));
    }

    public function edit(Ata $ata)
    {
        return view('secretaria.atas.edit', compact('ata'));
    }

    public function update(Request $request, Ata $ata)
    {
        $dados = $request->validate([
            'titulo' => 'required|string|max:255',
            'data_reuniao' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim' => 'nullable',
            'local' => 'required|string',
            'conteudo' => 'required|string',
            'participantes' => 'nullable|string',
        ]);

        $ata->update($dados);

        return redirect()->route('atas.show', $ata)->with('success', 'Ata atualizada!');
    }
}
