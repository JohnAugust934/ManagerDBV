<?php

namespace App\Http\Controllers;

use App\Models\Ata;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AtaController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('secretaria');

        $query = Ata::orderBy('data_reuniao', 'desc');

        if ($request->filled('search')) {
            // CORREÇÃO: Mudado de 'pauta' para 'titulo'
            $query->where('titulo', 'like', "%{$request->search}%")
                ->orWhere('conteudo', 'like', "%{$request->search}%");
        }

        $atas = $query->paginate(10);

        return view('secretaria.atas.index', compact('atas'));
    }

    public function create()
    {
        Gate::authorize('secretaria');

        return view('secretaria.atas.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('secretaria');

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
        Gate::authorize('secretaria');

        return view('secretaria.atas.show', compact('ata'));
    }

    public function print(Ata $ata)
    {
        Gate::authorize('secretaria');

        $pdf = Pdf::loadView('relatorios.ata', [
            'ata' => $ata,
            'clubeNome' => auth()->user()?->club?->nome ?? 'Clube de Desbravadores',
            'responsavelNome' => auth()->user()?->name ?? 'Sistema',
            'emitidoEm' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4');

        return $pdf->stream('ata_'.$ata->id.'.pdf');
    }

    public function edit(Ata $ata)
    {
        Gate::authorize('secretaria');

        return view('secretaria.atas.edit', compact('ata'));
    }

    public function update(Request $request, Ata $ata)
    {
        Gate::authorize('secretaria');

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

    public function destroy(Ata $ata)
    {
        Gate::authorize('secretaria');

        $ata->delete();

        return redirect()->route('atas.index')->with('success', 'Ata excluída com sucesso!');
    }
}
