<?php

namespace App\Http\Controllers;

use App\Models\Ata;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AtaController extends Controller
{
    public function index()
    {
        // Ordena da mais recente para a mais antiga
        $atas = Ata::orderBy('data_reuniao', 'desc')->get();
        return view('secretaria.atas.index', compact('atas'));
    }

    public function create()
    {
        return view('secretaria.atas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'data_reuniao' => 'required|date',
            'tipo' => 'required|string',
            'conteudo' => 'required|string',
        ]);

        Ata::create($request->all());

        return redirect()->route('atas.index')
            ->with('success', 'Ata registrada com sucesso!');
    }

    public function show($id)
    {
        $ata = Ata::findOrFail($id);
        return view('secretaria.atas.show', compact('ata'));
    }
}
