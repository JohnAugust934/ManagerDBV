<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index()
    {
        Gate::authorize('master');
        $invites = Invitation::with('club')->orderBy('created_at', 'desc')->get();

        return view('admin.invites.index', compact('invites'));
    }

    public function create()
    {
        Gate::authorize('master');
        $existingClub = Club::first();

        return view('admin.invites.create', compact('existingClub'));
    }

    public function store(Request $request)
    {
        Gate::authorize('master');

        $request->validate([
            'email' => ['required', 'email', 'unique:users,email', 'unique:invitations,email'],
            'role' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $existingClub = Club::first();
        $clubId = $existingClub ? $existingClub->id : null;

        Invitation::create([
            'email' => $request->email,
            'token' => Str::random(32),
            'role' => $request->role,
            'club_id' => $clubId,
            'expires_at' => $request->expires_at,
            'extra_permissions' => $request->extra_permissions ?? [],
        ]);

        return redirect()->route('invites.index')->with('success', 'Convite gerado com sucesso!');
    }

    public function destroy(Invitation $invite)
    {
        Gate::authorize('master');
        $invite->delete();

        return back()->with('success', 'Convite removido.');
    }
}
