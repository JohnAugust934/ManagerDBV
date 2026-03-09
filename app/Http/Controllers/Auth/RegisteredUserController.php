<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create(Request $request)
    {
        $token = $request->query('token');

        if (! $token) {
            return redirect()->route('login')->with('status', '❌ Acesso negado. Token de convite ausente.');
        }

        // Busca o convite (mesmo que já tenha sido usado, para podermos dar a mensagem correta)
        $invitation = Invitation::where('token', $token)->first();

        if (! $invitation) {
            return redirect()->route('login')->with('status', '❌ Acesso negado. Este convite não existe ou foi removido.');
        }

        // TRAVA 1: Verifica se já foi usado
        if ($invitation->registered_at) {
            return redirect()->route('login')->with('status', '❌ Acesso negado. Este link de convite já foi utilizado.');
        }

        // TRAVA 2: Verifica se expirou
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            return redirect()->route('login')->with('status', '❌ Acesso negado. Este link de convite expirou e não tem mais validade.');
        }

        return view('auth.register-invite', compact('invitation'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required', 'exists:invitations,token'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $invitation = Invitation::where('token', $request->token)->firstOrFail();

        // Verificação final de segurança no momento de salvar
        if ($invitation->registered_at) {
            return back()->withErrors(['email' => 'Este convite já foi utilizado.']);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            return back()->withErrors(['email' => 'Este convite já expirou.']);
        }

        $clubId = $invitation->club_id;
        $novoClubeCriado = false;

        // SE FOR DIRETOR E NÃO TIVER CLUBE VINCULADO AO CONVITE: CRIA UM NOVO
        if ($invitation->role === 'diretor' && is_null($clubId)) {
            $novoClube = Club::create([
                'nome' => 'Clube de '.$request->name.' (Definir Nome)',
                'cidade' => 'Definir Cidade',
            ]);
            $clubId = $novoClube->id;
            $novoClubeCriado = true;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'role' => $invitation->role,
            'club_id' => $clubId,
            'extra_permissions' => $invitation->extra_permissions,
            'is_master' => false,
        ]);

        // Marca o convite como USADO (registra a data e hora atual)
        $invitation->update(['registered_at' => now()]);

        event(new Registered($user));
        Auth::login($user);

        if ($novoClubeCriado) {
            return redirect()->route('club.edit')->with('success', 'Bem-vindo! Por favor, defina o nome e cidade do seu Clube.');
        }

        return redirect(route('dashboard'));
    }
}
