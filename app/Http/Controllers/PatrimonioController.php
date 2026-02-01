<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatrimonioController extends Controller
{
    public function index(Request $request)
    {
        // Totais para os Widgets
        $totalItens = Patrimonio::sum('quantidade');
        $valorTotal = Patrimonio::sum(DB::raw('valor_estimado * quantidade'));

        // CORREÇÃO: 'estado' mudou para 'estado_conservacao'
        $itensRuins = Patrimonio::whereIn('estado_conservacao', ['Ruim', 'Inservível'])->count();

        // Query Principal com Busca
        $query = Patrimonio::orderBy('item');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('item', 'like', "%{$request->search}%")
                    ->orWhere('local_armazenamento', 'like', "%{$request->search}%");
            });
        }

        $patrimonios = $query->paginate(10);

        return view('patrimonio.index', compact('patrimonios', 'totalItens', 'valorTotal', 'itensRuins'));
    }

    public function create()
    {
        return view('patrimonio.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'item' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'valor_estimado' => 'nullable|numeric|min:0',
            'data_aquisicao' => 'nullable|date',
            // CORREÇÃO: Validação do campo correto
            'estado_conservacao' => 'required|in:Novo,Bom,Regular,Ruim,Inservível',
            'local_armazenamento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        Patrimonio::create($dados);

        return redirect()->route('patrimonio.index')->with('success', 'Item adicionado ao patrimônio!');
    }

    public function edit(Patrimonio $patrimonio)
    {
        return view('patrimonio.edit', compact('patrimonio'));
    }

    public function update(Request $request, Patrimonio $patrimonio)
    {
        $dados = $request->validate([
            'item' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'valor_estimado' => 'nullable|numeric|min:0',
            'data_aquisicao' => 'nullable|date',
            // CORREÇÃO: Validação do campo correto
            'estado_conservacao' => 'required|in:Novo,Bom,Regular,Ruim,Inservível',
            'local_armazenamento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        $patrimonio->update($dados);

        return redirect()->route('patrimonio.index')->with('success', 'Patrimônio atualizado!');
    }

    public function destroy(Patrimonio $patrimonio)
    {
        $patrimonio->delete();

        return redirect()->route('patrimonio.index')->with('success', 'Item baixado do patrimônio.');
    }
}
