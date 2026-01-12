<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio;
use Illuminate\Http\Request;

class PatrimonioController extends Controller
{
    public function index()
    {
        $itens = Patrimonio::all();

        // Cálculos simples para o relatório
        $totalItens = $itens->sum('quantidade');
        $valorTotal = $itens->sum(fn($item) => $item->valor_estimado * $item->quantidade);
        $itensRuins = $itens->whereIn('estado_conservacao', ['Ruim', 'Inservível'])->count();

        return view('patrimonio.index', compact('itens', 'totalItens', 'valorTotal', 'itensRuins'));
    }

    public function create()
    {
        return view('patrimonio.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'item' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'estado_conservacao' => 'required|string',
            'valor_estimado' => 'nullable|numeric|min:0',
        ]);

        Patrimonio::create($request->all());

        return redirect()->route('patrimonio.index')
            ->with('success', 'Item adicionado ao patrimônio!');
    }

    public function destroy($id)
    {
        Patrimonio::destroy($id);
        return back()->with('success', 'Item baixado do patrimônio.');
    }
}
