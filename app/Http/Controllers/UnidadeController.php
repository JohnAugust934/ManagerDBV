<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    public function index()
    {
        // Traz apenas as unidades do clube do usuário logado
        $unidades = Unidade::where('club_id', auth()->user()->club_id)
            ->withCount('desbravadores')
            ->orderBy('nome')
            ->get();

        return view('unidades.index', compact('unidades'));
    }

    public function create()
    {
        return view('unidades.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'grito_guerra' => 'nullable|string',
            'conselheiro' => 'required|string|max:255',
        ]);

        // Força o vínculo com o clube do usuário logado
        $validated['club_id'] = auth()->user()->club_id;

        Unidade::create($validated);

        return redirect()->route('unidades.index')
            ->with('success', 'Unidade criada com sucesso!');
    }

    public function edit(Unidade $unidade)
    {
        $this->authorizeAccess($unidade);

        return view('unidades.edit', compact('unidade'));
    }

    public function update(Request $request, Unidade $unidade)
    {
        $this->authorizeAccess($unidade);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'grito_guerra' => 'nullable|string',
            'conselheiro' => 'required|string|max:255',
        ]);

        $unidade->update($validated);

        return redirect()->route('unidades.index')
            ->with('success', 'Unidade atualizada com sucesso!');
    }

    public function destroy(Unidade $unidade)
    {
        $this->authorizeAccess($unidade);

        // Proteção: Não apaga se tiver membros
        if ($unidade->desbravadores()->exists()) {
            return back()->with('error', 'Não é possível excluir esta unidade pois existem desbravadores vinculados a ela.');
        }

        $unidade->delete();

        return redirect()->route('unidades.index')
            ->with('success', 'Unidade excluída com sucesso!');
    }

    /**
     * Verifica se a unidade pertence ao clube do usuário
     */
    private function authorizeAccess(Unidade $unidade)
    {
        if ($unidade->club_id !== auth()->user()->club_id) {
            abort(403, 'Acesso não autorizado a esta unidade.');
        }
    }
}
