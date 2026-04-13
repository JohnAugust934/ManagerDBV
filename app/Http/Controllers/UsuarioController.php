<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        return view('usuarios.create', [
            'canGrantAccessManagement' => auth()->user()->isMaster(),
        ]);
    }

    public function index()
    {
        $this->authorizeAccessManagement();

        if (auth()->user()->isMaster()) {
            $users = User::orderBy('name')->get();
        } else {
            $users = User::where('club_id', auth()->user()->club_id)
                ->where('role', '!=', 'master')
                ->orderBy('name')
                ->get();
        }

        return view('usuarios.index', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccessManagement();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:'.implode(',', $this->allowedAssignableRoles())],
            'extra_permissions' => ['nullable', 'array'],
            'extra_permissions.*' => ['string'],
        ]);

        $extraPermissions = $this->sanitizeExtraPermissions($validated['extra_permissions'] ?? []);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'club_id' => auth()->user()->club_id,
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
            'canAssignMaster' => auth()->user()->isMaster(),
            'canGrantAccessManagement' => auth()->user()->isMaster(),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $this->authorizeAccessManagement();
        $this->ensureCanManageTargetUser($usuario);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$usuario->id],
            'role' => ['required', 'in:'.implode(',', $this->allowedAssignableRoles())],
            'extra_permissions' => ['nullable', 'array'],
            'extra_permissions.*' => ['string'],
        ]);

        $dados = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'extra_permissions' => $this->sanitizeExtraPermissions($validated['extra_permissions'] ?? []),
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $dados['password'] = Hash::make($request->password);
        }

        $usuario->update($dados);

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

    private function ensureCanManageTargetUser(User $usuario): void
    {
        $authUser = auth()->user();

        if ($authUser->isMaster()) {
            return;
        }

        if ($usuario->role === 'master') {
            abort(403, 'Somente o admin master pode gerenciar usuarios master.');
        }

        if ($usuario->club_id !== $authUser->club_id) {
            abort(403, 'Voce nao pode gerenciar usuarios de outro clube.');
        }
    }

    private function allowedAssignableRoles(): array
    {
        if (auth()->user()->isMaster()) {
            return ['master', 'diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'];
        }

        return ['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'];
    }

    private function sanitizeExtraPermissions(array $permissions): array
    {
        $allowed = array_keys(User::PERMISSOES);
        $normalized = array_values(array_unique(array_intersect($permissions, $allowed)));

        if (! auth()->user()->isMaster() && in_array('gestao_acessos', $normalized, true)) {
            throw ValidationException::withMessages([
                'extra_permissions' => 'Somente o admin master pode conceder a permissao de Gestao de Acessos.',
            ]);
        }

        return $normalized;
    }
}
