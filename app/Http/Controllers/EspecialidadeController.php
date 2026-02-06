<?php

namespace App\Http\Controllers;

use App\Models\Especialidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspecialidadeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Especialidade::query();

        // OTIMIZAÇÃO: Conta quantos desbravadores possuem a especialidade
        // Isso assume que no Model Especialidade existe o relacionamento:
        // public function desbravadores() { return $this->belongsToMany(Desbravador::class); }
        $query->withCount('desbravadores');

        if ($search) {
            $term = strtolower($search);
            $query->where(function ($q) use ($term) {
                $q->where(DB::raw('lower(nome)'), 'like', "%{$term}%")
                    ->orWhere(DB::raw('lower(area)'), 'like', "%{$term}%");
            });
        }

        // Ordena por Área e depois por Nome
        $especialidades = $query->orderBy('area')
            ->orderBy('nome')
            ->paginate(12)
            ->withQueryString();

        return view('especialidades.index', compact('especialidades', 'search'));
    }

    public function create()
    {
        return view('especialidades.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:especialidades',
            'area' => 'required|string|max:100',
            'cor_hex' => 'nullable|string|max:7',
        ]);

        Especialidade::create($validated);

        return redirect()->route('especialidades.index')
            ->with('success', 'Especialidade cadastrada com sucesso!');
    }
}
