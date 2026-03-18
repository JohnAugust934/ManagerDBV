<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Passa o convite para a view exatamente como no seu código original
        return view('auth.register-invite', compact('invitation'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'exists:invitations,token'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $resultado = DB::transaction(function () use ($request) {
            $invitation = Invitation::where('token', $request->token)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invitation->registered_at) {
                return ['erro' => 'Este convite já foi utilizado.'];
            }

            if ($invitation->expires_at && $invitation->expires_at->isPast()) {
                return ['erro' => 'Este convite já expirou.'];
            }

            if (User::where('email', $invitation->email)->exists()) {
                return ['erro' => 'Já existe um usuário cadastrado com este e-mail.'];
            }

            // MÁGICA SINGLE-TENANT: Busca o único clube. Se for o primeiro acesso, será null.
            $club = Club::first();

            $user = User::create([
                'name' => $request->name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'role' => $invitation->role,
                'club_id' => $club?->id,
                'extra_permissions' => $invitation->extra_permissions ?? null,
                'is_master' => false,
            ]);

            $invitation->update(['registered_at' => now()]);

            return [
                'user' => $user,
                'club' => $club,
            ];
        });

        if (isset($resultado['erro'])) {
            return back()->withErrors(['email' => $resultado['erro']]);
        }

        $user = $resultado['user'];
        $club = $resultado['club'];

        // Dispara o evento de registro do Laravel (Mantido do seu original!)
        event(new Registered($user));

        Auth::login($user);

        // REDIRECIONAMENTO INTELIGENTE (ONBOARDING):
        // Se não tem clube ainda e ele é o diretor, joga pra tela de criação
        if (! $club && in_array($user->role, ['diretor', 'master'])) {
            return redirect()->route('club.edit')->with('success', 'Bem-vindo! Por favor, defina o nome e a cidade do seu Clube para liberar o sistema.');
        }

        return redirect()->route('dashboard');
    }
}
