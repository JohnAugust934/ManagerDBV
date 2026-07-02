<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ClubContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class UsuarioController extends Controller
{
    public function create()
    {
        $this->authorizeAccessManagement();

        // No contexto de plataforma (sem clube ativo) novos admins entram por
        // convite — nao ha criacao direta.
        if ($this->isPlatformContext()) {
            return redirect()->route('invites.index')
                ->with('info', 'Para adicionar um admin da plataforma, gere um convite.');
        }

        return view('usuarios.create', [
            'assignableRoles' => $this->allowedAssignableRoles(),
            'canGrantAccessManagement' => auth()->user()->podeGerenciarMasters(),
            'isPlatformTarget' => false,
        ]);
    }

    public function index()
    {
        $this->authorizeAccessManagement();

        $clubId = ClubContext::currentClubId();

        if ($clubId === null) {
            // Contexto de plataforma: so o platform admin chega aqui (middleware
            // garante). Mostra a EQUIPE DA PLATAFORMA — os demais platform admins.
            $users = User::where('is_platform_admin', true)
                ->orderBy('name')
                ->get();

            return view('usuarios.index', [
                'users' => $users,
                'isPlatformContext' => true,
            ]);
        }

        // Contexto de clube (proprio ou em modo suporte): SOMENTE o clube ativo.
        $query = User::where('club_id', $clubId)->orderBy('name');

        // Quem nao pode gerir masters do clube nao os enxerga/gerencia.
        if (! auth()->user()->podeGerenciarMasters()) {
            $query->where('role', '!=', 'master');
        }

        return view('usuarios.index', [
            'users' => $query->get(),
            'isPlatformContext' => false,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccessManagement();

        // Criacao direta so existe dentro de um clube. Admins de plataforma sao
        // adicionados por convite.
        if ($this->isPlatformContext()) {
            abort(403, 'Adicione admins da plataforma por convite.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:'.implode(',', $this->allowedAssignableRoles())],
            'extra_permissions' => ['nullable', 'array'],
            'extra_permissions.*' => ['string'],
        ]);

        $extraPermissions = $this->sanitizeExtraPermissions($validated['extra_permissions'] ?? []);

        // forceCreate: role/club_id/extra_permissions são campos de privilégio (fora
        // de $fillable). Os valores já passaram por validação/allowlist acima.
        User::forceCreate([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'club_id' => ClubContext::currentClubId(),
            'extra_permissions' => $extraPermissions,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario criado com sucesso!');
    }

    public function edit(User $usuario)
    {
        $this->authorizeAccessManagement();
        $this->ensureCanManageTargetUser($usuario);

        return view('usuarios.edit', [
            'usuario' => $usuario,
            'assignableRoles' => $this->allowedAssignableRoles($usuario),
            'isPlatformTarget' => $usuario->is_platform_admin,
            'canAssignMaster' => auth()->user()->podeGerenciarMasters(),
            'canGrantAccessManagement' => auth()->user()->podeGerenciarMasters(),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $this->authorizeAccessManagement();
        $this->ensureCanManageTargetUser($usuario);

        // Admin de plataforma nao tem cargo de clube nem permissoes modulares:
        // edita-se apenas identificacao/senha, mantendo o cargo de plataforma.
        if ($usuario->is_platform_admin) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$usuario->id],
            ]);

            $dados = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if ($request->filled('password')) {
                $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
                $dados['password'] = Hash::make($request->password);
            }

            $usuario->update($dados);

            return redirect()->route('usuarios.index')->with('success', 'Admin da plataforma atualizado!');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$usuario->id],
            'role' => ['required', 'in:'.implode(',', $this->allowedAssignableRoles($usuario))],
            'extra_permissions' => ['nullable', 'array'],
            'extra_permissions.*' => ['string'],
        ]);

        $dados = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'extra_permissions' => $this->sanitizeExtraPermissions($validated['extra_permissions'] ?? []),
        ];

        // Autogestão de privilégio: ninguem edita o proprio cargo/permissoes (evita
        // auto-promocao de quem tem gestao_acessos). Mantem os valores atuais.
        if ($usuario->id === auth()->id()) {
            $dados['role'] = $usuario->role;
            $dados['extra_permissions'] = $usuario->extra_permissions;
        }

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $dados['password'] = Hash::make($request->password);
        }

        // forceFill: role/extra_permissions são campos de privilégio (fora de $fillable).
        $usuario->forceFill($dados)->save();

        return redirect()->route('usuarios.index')->with('success', 'Usuario atualizado!');
    }

    public function destroy(User $usuario)
    {
        $this->authorizeAccessManagement();
        $this->ensureCanManageTargetUser($usuario);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'Voce nao pode se excluir.');
        }

        $usuario->delete();

        return back()->with('success', 'Usuario removido.');
    }

    private function authorizeAccessManagement(): void
    {
        Gate::authorize('gestao-acessos');
    }

    /** Sem clube ativo, o platform admin opera o contexto da plataforma. */
    private function isPlatformContext(): bool
    {
        return ClubContext::currentClubId() === null && auth()->user()->isPlatformAdmin();
    }

    private function ensureCanManageTargetUser(User $usuario): void
    {
        $authUser = auth()->user();
        $clubId = ClubContext::currentClubId();

        // Contexto de plataforma: so o platform admin, e so sobre outros platform admins.
        if ($clubId === null) {
            if (! $authUser->isPlatformAdmin() || ! $usuario->is_platform_admin) {
                abort(403, 'Voce nao pode gerenciar este usuario.');
            }

            return;
        }

        // Contexto de clube (proprio ou impersonado): o alvo precisa ser do clube ativo.
        if ($usuario->club_id !== $clubId) {
            abort(403, 'Voce nao pode gerenciar usuarios de outro clube.');
        }

        // E so quem pode gerir masters (master do clube ou platform admin) gerencia masters.
        if ($usuario->role === 'master' && ! $authUser->podeGerenciarMasters()) {
            abort(403, 'Somente o admin master pode gerenciar usuarios master.');
        }
    }

    /**
     * Cargos atribuiveis no contexto atual. Um alvo platform admin so admite o
     * proprio cargo de plataforma.
     */
    private function allowedAssignableRoles(?User $target = null): array
    {
        if ($target?->is_platform_admin) {
            return ['platform_admin'];
        }

        if (auth()->user()->podeGerenciarMasters()) {
            return ['master', 'diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'];
        }

        return ['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'];
    }

    private function sanitizeExtraPermissions(array $permissions): array
    {
        $allowed = array_keys(User::PERMISSOES);
        $normalized = array_values(array_unique(array_intersect($permissions, $allowed)));

        if (! auth()->user()->podeGerenciarMasters() && in_array('gestao_acessos', $normalized, true)) {
            throw ValidationException::withMessages([
                'extra_permissions' => 'Somente o admin master pode conceder a permissao de Gestao de Acessos.',
            ]);
        }

        return $normalized;
    }
}
