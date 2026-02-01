<?php

namespace App\Http\Controllers;

use App\Models\Ato;
use Illuminate\Http\Request;

class AtoController extends Controller
{
    public function index(Request $request)
    {
        $query = Ato::orderBy('data', 'desc');

        if ($request->filled('search')) {
            $query->where('descricao', 'like', "%{$request->search}%")
                ->orWhere('numero', 'like', "%{$request->search}%");
        }

        $atos = $query->paginate(10);

        return view('secretaria.atos.index', compact('atos'));
    }

    public function create()
    {
        return view('secretaria.atos.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'numero' => 'required|string|max:20',
            'data' => 'required|date',
            'tipo' => 'required|string', // Ex: Nomeação, Exoneração, Voto
            'descricao' => 'required|string',
        ]);

        Ato::create($dados);

        return redirect()->route('atos.index')->with('success', 'Ato publicado com sucesso!');
    }
}
