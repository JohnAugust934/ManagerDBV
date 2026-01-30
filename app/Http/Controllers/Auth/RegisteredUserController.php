<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invitation;
use App\Models\Club;
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
        if (!$token) abort(403, 'Token inválido.');

        $invitation = Invitation::where('token', $token)->whereNull('registered_at')->firstOrFail();
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

        if ($invitation->registered_at) {
            return back()->withErrors(['email' => 'Convite já utilizado.']);
        }

        $clubId = $invitation->club_id;
        $novoClubeCriado = false;

        // SE FOR DIRETOR E NÃO TIVER CLUBE VINCULADO AO CONVITE: CRIA UM NOVO
        if ($invitation->role === 'diretor' && is_null($clubId)) {
            $novoClube = Club::create([
                'nome' => 'Clube de ' . $request->name . ' (Definir Nome)',
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

        $invitation->update(['registered_at' => now()]);

        event(new Registered($user));
        Auth::login($user);

        // SE ACABOU DE CRIAR O CLUBE, MANDA EDITAR OS DADOS
        if ($novoClubeCriado) {
            return redirect()->route('club.edit')->with('success', 'Bem-vindo! Por favor, defina o nome e cidade do seu Clube.');
        }

        return redirect(route('dashboard'));
    }
}
