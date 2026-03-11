<?php

namespace App\Http\Controllers;

use App\Mail\ClubInvitation;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index()
    {
        Gate::authorize('master');
        $invitations = Invitation::where('club_id', auth()->user()->club_id)->latest()->get();

        return view('admin.invites.index', compact('invitations'));
    }

    public function create()
    {
        Gate::authorize('master');

        return view('admin.invites.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('master');

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:master,diretor,secretario,tesoureiro,instrutor,conselheiro',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
        ]);

        $token = Str::random(40);

        $invitation = Invitation::create([
            'email' => $request->email,
            'token' => $token,
            'role' => $request->role,
            'club_id' => auth()->user()->club_id,
            'expires_at' => now()->addDays(7),
        ]);

        try {
            // Dispara o e-mail
            Mail::to($request->email)->send(new ClubInvitation($invitation));
        } catch (\Exception $e) {
            // Se o e-mail falhar, não perde o convite, apenas avisa na tela
            Log::error('Erro ao enviar e-mail de convite: '.$e->getMessage());

            return redirect()->route('invites.index')->with('warning', 'Convite gerado, mas ocorreu um erro ao enviar o e-mail. Você pode copiar o link da tabela e enviar manualmente.');
        }

        return redirect()->route('invites.index')->with('success', 'Convite gerado e e-mail enviado com sucesso!');
    }

    public function destroy(Invitation $invite)
    {
        Gate::authorize('master');

        if ($invite->club_id !== auth()->user()->club_id) {
            abort(403);
        }

        $invite->delete();

        return redirect()->route('invites.index')->with('success', 'Convite cancelado/removido com sucesso!');
    }
}
