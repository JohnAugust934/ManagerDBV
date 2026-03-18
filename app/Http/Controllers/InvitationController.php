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
        Gate::authorize('master');
        // Single tenant: mostra todos os convites do sistema
        $invites = Invitation::latest()->get();

        return view('admin.invites.index', compact('invites'));
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

        $club = \App\Models\Club::first(); // Busca o único clube do banco (se já existir)
        $existingInvitation = Invitation::where('email', $request->email)->first();

        // REGRA 1: Só pode haver UM diretor no sistema (já cadastrado ou convidado)
        if ($request->role === 'diretor') {
            $directorExists = User::where('role', 'diretor')->exists() ||
                              Invitation::where('role', 'diretor')
                                  ->whereNull('registered_at')
                                  ->when($existingInvitation, fn ($query) => $query->where('email', '!=', $existingInvitation->email))
                                  ->exists();

            if ($directorExists) {
                return back()->with('error', 'Ação Bloqueada: Só pode existir UM Diretor no sistema. Já existe um cadastrado ou com convite pendente.');
            }
        }

        // REGRA 2: Se o clube ainda não existe, o Master SÓ PODE convidar o Diretor
        if (! $club && $request->role !== 'diretor' && $request->role !== 'master') {
            return back()->with('error', 'Ação Bloqueada: O clube ainda não existe. Você deve convidar o DIRETOR primeiro para que ele configure o clube.');
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

            return back()->with('error', 'Não foi possível preparar o convite agora. Tente novamente.');
        }

        try {
            Mail::to($request->email)->send(new ClubInvitation($invitation));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar e-mail de convite: '.$e->getMessage());

            return redirect()->route('invites.index')->with('warning', $conviteFoiReaproveitado
                ? 'Convite pendente atualizado, mas o e-mail não pôde ser enviado (verifique o SMTP). Copie o link e envie manualmente.'
                : 'Convite gerado, mas o e-mail não pôde ser enviado (verifique o SMTP). Copie o link e envie manualmente.');
        }

        return redirect()->route('invites.index')->with('success', $conviteFoiReaproveitado
            ? 'Convite pendente atualizado e reenviado com sucesso!'
            : 'Convite gerado e enviado com sucesso!');
    }

    public function destroy(Invitation $invite)
    {
        Gate::authorize('master');
        $invite->delete();

        return redirect()->route('invites.index')->with('success', 'Convite cancelado com sucesso!');
    }
}
