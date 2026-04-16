<?php

namespace App\Http\Controllers;

use App\Models\Especialidade;
use App\Support\EspecialidadesCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class EspecialidadeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $selectedArea = $request->input('area');
        $somenteAvancadas = $request->boolean('avancadas');
        $investidos = $request->input('investidos'); // com | sem | null

        $searchNormalized = EspecialidadesCatalog::normalizeForSearch($search);

        $version = Cache::get('especialidades:index:version', 1);
        $page = max(1, (int) $request->input('page', 1));

        $cacheKey = 'especialidades:index:' . sha1(json_encode([
            'v' => $version,
            'p' => $page,
            'search' => $searchNormalized,
            'area' => $selectedArea,
            'avancadas' => $somenteAvancadas,
            'investidos' => $investidos,
        ], JSON_UNESCAPED_UNICODE));

        $especialidades = Cache::remember($cacheKey, now()->addMinutes(5), function () use (
            $search,
            $searchNormalized,
            $selectedArea,
            $somenteAvancadas,
            $investidos
        ) {
            $query = Especialidade::query()->withCount('desbravadores');

            if ($search !== '') {
                $query->where(function ($q) use ($searchNormalized) {
                    $q->where('nome_search', 'like', "%{$searchNormalized}%")
                        ->orWhere('area_search', 'like', "%{$searchNormalized}%")
                        ->orWhere('codigo', 'like', "%{$searchNormalized}%");
                });
            }

            if (! empty($selectedArea)) {
                $query->where('area', $selectedArea);
            }

            if ($somenteAvancadas) {
                $query->where('is_avancada', true);
            }

            if ($investidos === 'com') {
                $query->has('desbravadores');
            } elseif ($investidos === 'sem') {
                $query->doesntHave('desbravadores');
            }

            return $query->orderBy('area')
                ->orderBy('nome')
                ->paginate(12)
                ->withQueryString();
        });

        $areas = Cache::remember("especialidades:areas:v{$version}", now()->addMinutes(30), function () {
            return Especialidade::query()
                ->select('area')
                ->distinct()
                ->orderBy('area')
                ->pluck('area');
        });

        return view('especialidades.index', compact(
            'especialidades',
            'search',
            'areas',
            'selectedArea',
            'somenteAvancadas',
            'investidos'
        ));
    }

    public function create()
    {
        return view('especialidades.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('especialidades')->where(function ($query) use ($request) {
                    return $query->where('area', $request->input('area'));
                }),
            ],
            'area' => 'required|string|max:100',
            'cor_fundo' => 'nullable|string|max:7',
        ]);

        $validated['is_oficial'] = false;
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        Especialidade::create($validated);

        return redirect()->route('especialidades.index')
            ->with('success', 'Especialidade cadastrada com sucesso!');
    }

    public function show(Especialidade $especialidade)
    {
        $especialidade->loadCount('desbravadores');
        $especialidade->load('requisitosOficiais');

        return view('especialidades.show', compact('especialidade'));
    }

    public function edit(Especialidade $especialidade)
    {
        return view('especialidades.edit', compact('especialidade'));
    }

    public function update(Request $request, Especialidade $especialidade)
    {
        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('especialidades')
                    ->ignore($especialidade->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('area', $request->input('area'));
                    }),
            ],
            'area' => 'required|string|max:100',
            'cor_fundo' => 'nullable|string|max:7',
        ]);

        $validated['updated_by'] = Auth::id();
        $validated['is_avancada'] = EspecialidadesCatalog::isAdvanced($validated['nome']);

        $especialidade->update($validated);

        return redirect()
            ->route('especialidades.show', $especialidade)
            ->with('success', 'Especialidade atualizada com sucesso!');
    }

    public function destroy(Especialidade $especialidade)
    {
        $especialidade->delete();

        return redirect()
            ->route('especialidades.index')
            ->with('success', 'Especialidade removida com sucesso.');
    }
}
