<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules;

class UsuarioController extends Controller
{
    public function index()
    {
        Gate::authorize('master');

        // Lógica corrigida:
        // Se for Master (super admin), vê TODOS.
        // Se futuramente um Diretor tiver acesso a essa tela, vê apenas do seu clube.
        if (auth()->user()->isMaster()) {
            $users = User::orderBy('name')->get();
        } else {
            $users = User::where('club_id', auth()->user()->club_id)->orderBy('name')->get();
        }

        return view('usuarios.index', compact('users'));
    }

    public function edit(User $usuario)
    {
        Gate::authorize('master');

        // Impede editar usuários de outros clubes (caso tenhamos multi-tenancy no futuro)
        // Mas permite se quem edita é Master
        if (!auth()->user()->isMaster() && $usuario->club_id !== auth()->user()->club_id) {
            abort(403);
        }

        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        Gate::authorize('master');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'role' => ['required', 'string'],
            'extra_permissions' => ['nullable', 'array']
        ]);

        $dados = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'extra_permissions' => $request->extra_permissions ?? []
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $dados['password'] = Hash::make($request->password);
        }

        $usuario->update($dados);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado!');
    }

    public function destroy(User $usuario)
    {
        Gate::authorize('master');
        if ($usuario->id === auth()->id()) return back()->with('error', 'Você não pode se excluir.');

        $usuario->delete();
        return back()->with('success', 'Usuário removido.');
    }
}
