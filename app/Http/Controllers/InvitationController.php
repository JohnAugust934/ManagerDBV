<?php

namespace App\Http\Controllers;

use App\Mail\ClubInvitation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index()
    {
        $this->authorizeAccessManagement();

        // Single tenant: mostra todos os convites do sistema
        $invites = Invitation::latest()->get();

        return view('admin.invites.index', compact('invites'));
    }

    public function create()
    {
        $this->authorizeAccessManagement();

        return view('admin.invites.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAccessManagement();

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:'.implode(',', $this->allowedInvitableRoles()),
        ], [
            'email.unique' => 'Este e-mail ja esta cadastrado no sistema.',
        ]);

        $club = \App\Models\Club::first(); // Busca o unico clube do banco (se ja existir)
        $existingInvitation = Invitation::where('email', $request->email)->first();

        // REGRA 1: So pode haver UM diretor no sistema (ja cadastrado ou convidado)
        if ($request->role === 'diretor') {
            $directorExists = User::where('role', 'diretor')->exists() ||
                              Invitation::where('role', 'diretor')
                                  ->whereNull('registered_at')
                                  ->when($existingInvitation, fn ($query) => $query->where('email', '!=', $existingInvitation->email))
                                  ->exists();

            if ($directorExists) {
                return back()->with('error', 'Acao bloqueada: so pode existir UM Diretor no sistema. Ja existe um cadastrado ou com convite pendente.');
            }
        }

        // REGRA 2: Se o clube ainda nao existe, o Master so pode convidar o Diretor
        if (! $club && $request->role !== 'diretor' && $request->role !== 'master') {
            return back()->with('error', 'Acao bloqueada: o clube ainda nao existe. Voce deve convidar o DIRETOR primeiro para que ele configure o clube.');
        }

        if ($existingInvitation?->registered_at) {
            return back()->with('error', 'Este convite já foi utilizado. Como o e-mail não pode ser reutilizado, faça o gerenciamento diretamente no cadastro de usuários.');
        }

        $conviteFoiReaproveitado = false;

        try {
            $invitation = DB::transaction(function () use ($request, $club, $existingInvitation, &$conviteFoiReaproveitado) {
                $dadosDoConvite = [
                    'token' => Str::random(40),
                    'role' => $request->role,
                    'club_id' => $club?->id,
                    'expires_at' => now()->addDays(7),
                    'registered_at' => null,
                ];

                if ($existingInvitation) {
                    $existingInvitation->update($dadosDoConvite);
                    $conviteFoiReaproveitado = true;

                    return $existingInvitation->fresh();
                }

                return Invitation::create([
                    'email' => $request->email,
                    ...$dadosDoConvite,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Erro ao preparar convite: '.$e->getMessage());

            return back()->with('error', 'Nao foi possivel preparar o convite agora. Tente novamente.');
        }

        try {
            Mail::to($request->email)->send(new ClubInvitation($invitation));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar e-mail de convite: '.$e->getMessage());

            return redirect()->route('invites.index')->with('warning', $conviteFoiReaproveitado
                ? 'Convite pendente atualizado, mas o e-mail nao pode ser enviado (verifique o SMTP). Copie o link e envie manualmente.'
                : 'Convite gerado, mas o e-mail nao pode ser enviado (verifique o SMTP). Copie o link e envie manualmente.');
        }

        return redirect()->route('invites.index')->with('success', $conviteFoiReaproveitado
            ? 'Convite pendente atualizado e reenviado com sucesso!'
            : 'Convite gerado e enviado com sucesso!');
    }

    public function destroy(Invitation $invite)
    {
        $this->authorizeAccessManagement();

        if (! auth()->user()->isMaster() && $invite->role === 'master') {
            abort(403, 'Somente o admin master pode cancelar convites de master.');
        }

        $invite->delete();

        return redirect()->route('invites.index')->with('success', 'Convite cancelado com sucesso!');
    }

    private function authorizeAccessManagement(): void
    {
        Gate::authorize('gestao-acessos');
    }

    private function allowedInvitableRoles(): array
    {
        if (auth()->user()->isMaster()) {
            return ['master', 'diretor', 'secretario', 'tesoureiro', 'instrutor', 'conselheiro'];
        }

        return ['diretor', 'secretario', 'tesoureiro', 'instrutor', 'conselheiro'];
    }
}
