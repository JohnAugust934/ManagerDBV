<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PatrimonioController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('financeiro');

        $search = $request->input('search');

        // Query Base
        $query = Patrimonio::query();

        // Busca Inteligente (Case Insensitive)
        if ($search) {
            $term = strtolower($search);
            $query->where(function ($q) use ($term) {
                $q->where(DB::raw('lower(item)'), 'like', "%{$term}%") // Corrigido de 'nome' para 'item'
                    ->orWhere(DB::raw('lower(observacoes)'), 'like', "%{$term}%")
                    ->orWhere(DB::raw('lower(local_armazenamento)'), 'like', "%{$term}%");
            });
        }

        // Ordenação Padrão
        $patrimonios = $query->orderBy('item', 'asc')->paginate(10)->withQueryString();

        // KPIs
        $totalItens = Patrimonio::sum('quantidade'); // Soma quantidade física, não linhas
        $valorTotal = Patrimonio::sum(DB::raw('valor_estimado * quantidade')); // Valor total real

        // Estado de conservação (ajuste conforme os valores reais do banco)
        $itensBons = Patrimonio::whereIn('estado_conservacao', ['Novo', 'Bom', 'Ótimo'])->sum('quantidade');
        $itensRuins = Patrimonio::whereIn('estado_conservacao', ['Ruim', 'Péssimo', 'Inservível'])->sum('quantidade');

        return view('patrimonio.index', compact(
            'patrimonios',
            'search',
            'totalItens',
            'valorTotal',
            'itensBons',
            'itensRuins'
        ));
    }

    public function create()
    {
        Gate::authorize('financeiro');

        return view('patrimonio.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('financeiro');

        $validated = $request->validate([
            'item' => 'required|string|max:255', // Corrigido de 'nome'
            'quantidade' => 'required|integer|min:1',
            'valor_estimado' => 'nullable|numeric|min:0',
            'data_aquisicao' => 'nullable|date',
            'estado_conservacao' => 'required|string', // Corrigido de 'estado'
            'local_armazenamento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string', // Corrigido de 'descricao'
        ]);

        Patrimonio::create($validated);

        return redirect()->route('patrimonio.index')
            ->with('success', 'Item de patrimônio cadastrado com sucesso!');
    }

    public function edit(Patrimonio $patrimonio)
    {
        Gate::authorize('financeiro');

        return view('patrimonio.edit', compact('patrimonio'));
    }

    public function update(Request $request, Patrimonio $patrimonio)
    {
        Gate::authorize('financeiro');

        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'valor_estimado' => 'nullable|numeric|min:0',
            'data_aquisicao' => 'nullable|date',
            'estado_conservacao' => 'required|string',
            'local_armazenamento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        $patrimonio->update($validated);

        return redirect()->route('patrimonio.index')
            ->with('success', 'Patrimônio atualizado com sucesso!');
    }

    public function destroy(Patrimonio $patrimonio)
    {
        Gate::authorize('financeiro');

        $patrimonio->delete();

        return redirect()->route('patrimonio.index')
            ->with('success', 'Item removido do inventário.');
    }
}
