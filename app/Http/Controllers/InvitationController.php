<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class InvitationController extends Controller
{
    /**
     * Lista de Convites.
     */
    public function index()
    {
        Gate::authorize('master');

        $invites = Invitation::with('club')->orderBy('created_at', 'desc')->get();
        return view('admin.invites.index', compact('invites'));
    }

    /**
     * Formulário para criar novo convite.
     */
    public function create()
    {
        Gate::authorize('master');

        // Single Tenant: Buscamos o único clube existente (se houver)
        $existingClub = Club::first();

        return view('admin.invites.create', compact('existingClub'));
    }

    /**
     * Gera o link e salva o convite.
     */
    public function store(Request $request)
    {
        Gate::authorize('master');

        $request->validate([
            'email' => ['required', 'email', 'unique:users', 'unique:invitations,email'],
            'role' => ['required', 'string'],
        ]);

        // Lógica Single Tenant:
        // 1. Tenta pegar o clube existente.
        // 2. Se não existir, clubId fica null (indicando que o usuário convidado criará o clube).
        $existingClub = Club::first();
        $clubId = $existingClub ? $existingClub->id : null;

        Invitation::create([
            'email' => $request->email,
            'token' => Str::random(32),
            'role' => $request->role,
            'club_id' => $clubId,
            'extra_permissions' => $request->extra_permissions ?? []
        ]);

        return redirect()->route('invites.index')->with('success', 'Convite gerado com sucesso!');
    }

    /**
     * Remove um convite.
     */
    public function destroy(Invitation $invite)
    {
        Gate::authorize('master');
        $invite->delete();
        return back()->with('success', 'Convite removido.');
    }
}
