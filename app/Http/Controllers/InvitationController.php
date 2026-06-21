<?php

namespace App\Http\Controllers;

use App\Mail\ClubInvitation;
use App\Models\Invitation;
use App\Models\User;
use App\Services\ClubContext;
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

        // Multi-tenant: cada gestor vê apenas os convites do próprio clube (ou do
        // clube impersonado, no caso do platform admin em modo suporte). Sem clube
        // ativo, o platform admin vê apenas os convites da EQUIPE DA PLATAFORMA.
        $clubId = ClubContext::currentClubId();

        if ($clubId === null) {
            $invites = Invitation::whereNull('club_id')
                ->where('role', 'platform_admin')
                ->latest()
                ->get();

            return view('admin.invites.index', [
                'invites' => $invites,
                'isPlatformContext' => true,
            ]);
        }

        $invites = Invitation::where('club_id', $clubId)
            ->latest()
            ->get();

        return view('admin.invites.index', [
            'invites' => $invites,
            'isPlatformContext' => false,
        ]);
    }

    public function create()
    {
        $this->authorizeAccessManagement();

        $platformContext = $this->isPlatformContext();

        return view('admin.invites.create', [
            'roles' => $platformContext ? ['platform_admin'] : ['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'],
            'isPlatformContext' => $platformContext,
        ]);
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

        // Convite de admin da plataforma: cross-tenant (sem clube), pula as regras
        // de clube (diretor único / clube precisa existir).
        if ($request->role === 'platform_admin') {
            return $this->storePlatformAdminInvite($request);
        }

        // Multi-tenant: o convite pertence ao clube ativo (próprio ou impersonado).
        // No onboarding inicial (diretor antes de criar o clube) ainda pode ser null.
        $club = ClubContext::currentClub();
        $existingInvitation = Invitation::where('email', $request->email)->first();

        // REGRA 1: So pode haver UM diretor por clube (ja cadastrado ou convidado)
        if ($request->role === 'diretor') {
            $clubId = $club?->id;

            $directorExists = User::where('role', 'diretor')
                ->when($clubId, fn ($q) => $q->where('club_id', $clubId))
                ->exists() ||
                              Invitation::where('role', 'diretor')
                                  ->whereNull('registered_at')
                                  ->when($clubId, fn ($q) => $q->where('club_id', $clubId))
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
            Mail::to($request->email)->queue(new ClubInvitation($invitation));
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

    /**
     * Gera/reaproveita um convite de admin da plataforma (cross-tenant, sem clube).
     */
    private function storePlatformAdminInvite(Request $request)
    {
        if (! $this->isPlatformContext()) {
            abort(403, 'Apenas o admin da plataforma pode convidar outros admins da plataforma.');
        }

        $existingInvitation = Invitation::where('email', $request->email)->first();

        if ($existingInvitation?->registered_at) {
            return back()->with('error', 'Este convite já foi utilizado. Como o e-mail não pode ser reutilizado, faça o gerenciamento diretamente no cadastro de usuários.');
        }

        $conviteFoiReaproveitado = false;

        try {
            $invitation = DB::transaction(function () use ($request, $existingInvitation, &$conviteFoiReaproveitado) {
                $dadosDoConvite = [
                    'token' => Str::random(40),
                    'role' => 'platform_admin',
                    'club_id' => null,
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
            Log::error('Erro ao preparar convite de plataforma: '.$e->getMessage());

            return back()->with('error', 'Nao foi possivel preparar o convite agora. Tente novamente.');
        }

        try {
            Mail::to($request->email)->queue(new ClubInvitation($invitation));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar e-mail de convite de plataforma: '.$e->getMessage());

            return redirect()->route('invites.index')->with('warning', $conviteFoiReaproveitado
                ? 'Convite pendente atualizado, mas o e-mail nao pode ser enviado (verifique o SMTP). Copie o link e envie manualmente.'
                : 'Convite gerado, mas o e-mail nao pode ser enviado (verifique o SMTP). Copie o link e envie manualmente.');
        }

        return redirect()->route('invites.index')->with('success', $conviteFoiReaproveitado
            ? 'Convite de admin da plataforma atualizado e reenviado!'
            : 'Convite de admin da plataforma gerado e enviado!');
    }

    public function resend(Invitation $invite)
    {
        $this->authorizeAccessManagement();

        if ($invite->registered_at) {
            return back()->with('error', 'Este convite já foi utilizado e não pode ser reenviado.');
        }

        $invite->update([
            'token' => Str::random(40),
            'expires_at' => now()->addDays(7),
        ]);

        try {
            Mail::to($invite->email)->queue(new ClubInvitation($invite->fresh()));
        } catch (\Exception $e) {
            Log::error('Erro ao reenviar convite: '.$e->getMessage());

            return back()->with('warning', 'Token renovado, mas o e-mail não pôde ser enviado. Copie o link e envie manualmente.');
        }

        return back()->with('success', 'Convite reenviado com sucesso para '.$invite->email.'!');
    }

    public function destroy(Invitation $invite)
    {
        $this->authorizeAccessManagement();

        if ($invite->role === 'platform_admin' && ! auth()->user()->isPlatformAdmin()) {
            abort(403, 'Somente o admin da plataforma pode cancelar convites de plataforma.');
        }

        if ($invite->role === 'master' && ! auth()->user()->podeGerenciarMasters()) {
            abort(403, 'Somente o admin master pode cancelar convites de master.');
        }

        $invite->delete();

        return redirect()->route('invites.index')->with('success', 'Convite cancelado com sucesso!');
    }

    private function authorizeAccessManagement(): void
    {
        Gate::authorize('gestao-acessos');
    }

    /** Sem clube ativo, o platform admin opera o contexto da plataforma. */
    private function isPlatformContext(): bool
    {
        return ClubContext::currentClubId() === null && auth()->user()->isPlatformAdmin();
    }

    private function allowedInvitableRoles(): array
    {
        // Contexto de plataforma: só convida outros admins da plataforma.
        if ($this->isPlatformContext()) {
            return ['platform_admin'];
        }

        if (auth()->user()->podeGerenciarMasters()) {
            return ['master', 'diretor', 'secretario', 'tesoureiro', 'instrutor', 'conselheiro'];
        }

        return ['diretor', 'secretario', 'tesoureiro', 'instrutor', 'conselheiro'];
    }
}
