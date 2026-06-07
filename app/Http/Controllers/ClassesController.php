<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Desbravador;
use App\Models\Requisito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ClassesController extends Controller
{
    public function index()
    {
        $classes = Classe::orderBy('ordem')->get();

        return view('classes.index', compact('classes'));
    }

    public function show(Classe $classe)
    {
        $classe->load('requisitos');

        // GlobalScope DesbravadorClubScope filtra pelo clube automaticamente.
        $desbravadores = Desbravador::where('ativo', true)
            ->where('classe_atual', $classe->id)
            ->with(['requisitosCumpridos' => function ($q) use ($classe) {
                $q->where('classe_id', $classe->id);
            }])
            ->orderBy('nome')
            ->get()
            ->map(function ($dbv) use ($classe) {
                $totalReqs = $classe->requisitos->count();
                $cumpridos = $dbv->requisitosCumpridos->count();

                $dbv->progresso_percentual = $totalReqs > 0 ? round(($cumpridos / $totalReqs) * 100) : 0;
                $dbv->ids_cumpridos = $dbv->requisitosCumpridos->pluck('id')->toArray();

                return $dbv;
            });

        return view('classes.show', compact('classe', 'desbravadores'));
    }

    public function toggle(Request $request)
    {
        Gate::authorize('pedagogico');

        $request->validate([
            'desbravador_id' => 'required|exists:desbravadores,id',
            'requisito_id' => 'required|exists:requisitos,id',
            'concluido' => 'required|boolean',
        ]);

        // GlobalScope garante que o desbravador pertence ao clube do usuário.
        $dbv = Desbravador::findOrFail($request->desbravador_id);

        if ($request->concluido) {
            $dbv->requisitosCumpridos()->attach($request->requisito_id, [
                'user_id' => Auth::id(),
                'data_conclusao' => now(),
            ]);
            $msg = 'Requisito assinado!';
        } else {
            $dbv->requisitosCumpridos()->detach($request->requisito_id);
            $msg = 'Assinatura removida.';
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function storeRequisito(Request $request, Classe $classe)
    {
        Gate::authorize('pedagogico');

        $request->validate([
            'codigo' => 'nullable|string|max:20',
            'descricao' => 'required|string|max:500',
            'categoria' => 'nullable|string|max:100',
        ]);

        $classe->requisitos()->create([
            'codigo' => $request->codigo,
            'descricao' => $request->descricao,
            'categoria' => $request->categoria,
        ]);

        return back()->with('success', 'Requisito adicionado com sucesso.');
    }

    public function updateRequisito(Request $request, Classe $classe, Requisito $requisito)
    {
        Gate::authorize('pedagogico');
        abort_if($requisito->classe_id !== $classe->id, 404);

        $request->validate([
            'codigo' => 'nullable|string|max:20',
            'descricao' => 'required|string|max:500',
            'categoria' => 'nullable|string|max:100',
        ]);

        $requisito->update([
            'codigo' => $request->codigo,
            'descricao' => $request->descricao,
            'categoria' => $request->categoria,
        ]);

        return back()->with('success', 'Requisito atualizado.');
    }

    public function destroyRequisito(Classe $classe, Requisito $requisito)
    {
        Gate::authorize('pedagogico');
        abort_if($requisito->classe_id !== $classe->id, 404);

        // Remove o vínculo com desbravadores antes de excluir
        $requisito->desbravadores()->detach();
        $requisito->delete();

        return back()->with('success', 'Requisito excluído.');
    }
}
