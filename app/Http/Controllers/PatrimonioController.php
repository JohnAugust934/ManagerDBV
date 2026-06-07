<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio;
use App\Models\PatrimonioManutencao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PatrimonioController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('financeiro');

        // GlobalScope ClubScope aplica o filtro de club_id automaticamente.
        $search = $request->input('search');

        $query = Patrimonio::query();

        if ($search) {
            $term = strtolower($search);
            $query->where(function ($q) use ($term) {
                $q->where(DB::raw('lower(item)'), 'like', "%{$term}%")
                    ->orWhere(DB::raw('lower(observacoes)'), 'like', "%{$term}%")
                    ->orWhere(DB::raw('lower(local_armazenamento)'), 'like', "%{$term}%");
            });
        }

        $patrimonios = $query->orderBy('item', 'asc')->paginate(10)->withQueryString();

        $totalItens = Patrimonio::sum('quantidade');
        $valorTotal = Patrimonio::sum(DB::raw('valor_estimado * quantidade'));
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
            'item' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'valor_estimado' => 'nullable|numeric|min:0',
            'data_aquisicao' => 'nullable|date',
            'estado_conservacao' => 'required|string',
            'local_armazenamento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        $validated['club_id'] = auth()->user()->club_id;

        Patrimonio::create($validated);

        return redirect()->route('patrimonio.index')
            ->with('success', 'Item de patrimônio cadastrado com sucesso!');
    }

    public function edit(Patrimonio $patrimonio)
    {
        Gate::authorize('financeiro');

        $patrimonio->load(['manutencoes.user']);

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

        $estadoAnterior = $patrimonio->estado_conservacao;

        $patrimonio->update($validated);

        if ($estadoAnterior !== $validated['estado_conservacao']) {
            $patrimonio->manutencoes()->create([
                'user_id' => auth()->id(),
                'data' => now()->toDateString(),
                'estado_anterior' => $estadoAnterior,
                'estado_novo' => $validated['estado_conservacao'],
                'descricao' => 'Alteração de estado via edição do cadastro.',
            ]);
        }

        return redirect()->route('patrimonio.index')
            ->with('success', 'Patrimônio atualizado com sucesso!');
    }

    public function storeManutencao(Request $request, Patrimonio $patrimonio)
    {
        Gate::authorize('financeiro');

        $request->validate([
            'data' => 'required|date',
            'estado_novo' => 'required|string|max:100',
            'descricao' => 'required|string|max:1000',
        ]);

        $patrimonio->manutencoes()->create([
            'user_id' => auth()->id(),
            'data' => $request->data,
            'estado_anterior' => $patrimonio->estado_conservacao,
            'estado_novo' => $request->estado_novo,
            'descricao' => $request->descricao,
        ]);

        if ($patrimonio->estado_conservacao !== $request->estado_novo) {
            $patrimonio->update(['estado_conservacao' => $request->estado_novo]);
        }

        return back()->with('success', 'Registro de manutenção adicionado.');
    }

    public function destroyManutencao(Patrimonio $patrimonio, PatrimonioManutencao $manutencao)
    {
        Gate::authorize('financeiro');
        abort_if($manutencao->patrimonio_id !== $patrimonio->id, 404);

        $manutencao->delete();

        return back()->with('success', 'Registro removido.');
    }

    public function destroy(Patrimonio $patrimonio)
    {
        Gate::authorize('financeiro');

        $patrimonio->delete();

        return redirect()->route('patrimonio.index')
            ->with('success', 'Item removido do inventário.');
    }
}
