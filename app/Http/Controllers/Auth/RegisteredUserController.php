<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Club;
use App\Models\Invitation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $token = $request->query('token');
        $invitation = Invitation::where('token', $token)->whereNull('used_at')->first();

        if (!$token || !$invitation) {
            return redirect()->route('login')->withErrors(['email' => 'É necessário um convite válido para se registrar.']);
        }

        // Verifica se já existe ALGUM clube cadastrado (Single Tenant logic)
        $needsClubSetup = Club::count() === 0;

        return view('auth.register', [
            'email' => $invitation->email,
            'token' => $token,
            'needsClubSetup' => $needsClubSetup
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. Verificações Comuns
        $request->validate([
            'token' => ['required', 'exists:invitations,token'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $invitation = Invitation::where('token', $request->token)->whereNull('used_at')->firstOrFail();

        // Verifica se o clube já existe
        $existingClub = Club::first();

        if (!$existingClub) {
            // Se NÃO existe clube, valida os dados do clube também
            $request->validate([
                'club_name' => ['required', 'string', 'max:255'],
                'club_city' => ['required', 'string', 'max:255'],
            ]);
        }

        DB::transaction(function () use ($request, $invitation, $existingClub) {

            // Define qual clube usar (ou cria um novo)
            if ($existingClub) {
                $club = $existingClub;
            } else {
                $club = Club::create([
                    'nome' => $request->club_name,
                    'cidade' => $request->club_city,
                    'associacao' => $request->club_associacao ?? null,
                ]);
            }

            // Cria o Usuário vinculado ao clube
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_master' => false,
                'club_id' => $club->id, // Vincula ao clube (novo ou existente)
            ]);

            // Marca convite como usado
            $invitation->update(['used_at' => now()]);

            event(new Registered($user));
            Auth::login($user);
        });

        return redirect(route('dashboard', absolute: false));
    }
}
