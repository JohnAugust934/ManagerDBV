<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadeController extends Controller
{
    public function index()
    {
        // 🔒 TRAVA DE SEGURANÇA: Bloqueia o instrutor
        if (auth()->user()->role === 'instrutor') {
            abort(403, 'Acesso negado. Instrutores não têm permissão para visualizar as unidades.');
        }

        // Busca as unidades pertencentes ao clube do usuário logado
        $unidades = Unidade::where('club_id', auth()->user()->club_id)->get();

        // Carrega a tela com os dados
        return view('unidades.index', compact('unidades'));
    }

    public function show(Unidade $unidade)
    {
        // 🔒 TRAVA DE SEGURANÇA: Bloqueia o instrutor
        if (auth()->user()->role === 'instrutor') {
            abort(403, 'Acesso negado. Instrutores não têm permissão para visualizar as unidades.');
        }

        // Segurança extra: Garante que o usuário só veja unidades do próprio clube
        if ($unidade->club_id !== auth()->user()->club_id) {
            abort(403, 'Acesso negado.');
        }

        // Carrega a tela do painel da unidade (o erro estava aqui, escondido por '//'!)
        return view('unidades.show', compact('unidade'));
    }

    public function create()
    {
        return view('unidades.create', ['usuarios' => $this->usuariosDoClube()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->regras());

        // Força o vínculo com o clube do usuário logado
        $validated['club_id'] = auth()->user()->club_id;

        Unidade::create($validated);

        return redirect()->route('unidades.index')
            ->with('success', 'Unidade criada com sucesso!');
    }

    public function edit(Unidade $unidade)
    {
        $this->authorizeAccess($unidade);

        return view('unidades.edit', [
            'unidade' => $unidade,
            'usuarios' => $this->usuariosDoClube(),
        ]);
    }

    public function update(Request $request, Unidade $unidade)
    {
        $this->authorizeAccess($unidade);

        $validated = $request->validate($this->regras());

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

    public function toggleRanking(Unidade $unidade)
    {
        $this->authorizeAccess($unidade);

        $unidade->update(['no_ranking' => ! $unidade->no_ranking]);

        $status = $unidade->no_ranking ? 'incluída no' : 'excluída do';

        return back()->with('success', "Unidade \"{$unidade->nome}\" {$status} ranking.");
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

    /**
     * Regras de validação compartilhadas entre store e update.
     */
    private function regras(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'grito_guerra' => 'nullable|string',
            'conselheiro' => 'required|string|max:255',
            // Vínculo opcional a um usuário do próprio clube (para conceder a gestão da unidade).
            'conselheiro_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('club_id', auth()->user()->club_id),
            ],
        ];
    }

    /**
     * Usuários do clube disponíveis para vincular como conselheiro responsável.
     */
    private function usuariosDoClube()
    {
        return User::where('club_id', auth()->user()->club_id)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }
}
