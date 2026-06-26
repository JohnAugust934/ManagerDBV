<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Services\ClubContext;
use App\Services\ClubExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClubController extends Controller
{
    public function edit()
    {
        Gate::authorize('secretaria');
        $club = ClubContext::currentClub(); // Clube ativo do tenant (respeita impersonação)

        return view('club.edit', compact('club'));
    }

    public function update(Request $request)
    {
        Gate::authorize('secretaria');

        $request->validate([
            'nome' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'associacao' => 'required|string|max:255',
            'logo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        $club = ClubContext::currentClub();

        if (! $club) {
            // O DIRETOR ESTÁ CRIANDO O CLUBE AGORA!
            $club = Club::create([
                'nome' => $request->nome,
                'cidade' => $request->cidade,
                'associacao' => $request->associacao,
            ]);

            // MÁGICA: Vincula o clube recém criado aos usuários órfãos (Master e Diretor).
            // NUNCA inclui o platform admin (cross-tenant), que deve permanecer sem clube.
            \App\Models\User::whereNull('club_id')
                ->where('is_platform_admin', false)
                ->update(['club_id' => $club->id]);

            // Atualiza a sessão atual do Diretor
            auth()->user()->refresh();
        } else {
            $club->update($request->only(['nome', 'cidade', 'associacao']));
        }

        if ($request->hasFile('logo')) {
            if ($club->logo) {
                Storage::disk('public')->delete($club->logo);
            }
            $ext = $request->file('logo')->extension();
            $path = $request->file('logo')->storeAs('logos', Str::uuid().'.'.$ext, 'public');
            $club->update(['logo' => $path]);
        }

        return back()->with('status', 'club-updated')->with('success', 'Informações do clube salvas com sucesso!');
    }

    public function removeLogo()
    {
        Gate::authorize('secretaria');
        $club = ClubContext::currentClub();

        if ($club && $club->logo) {
            Storage::disk('public')->delete($club->logo);
            $club->update(['logo' => null]);
        }

        return back()->with('success', 'Brasão removido com sucesso!');
    }

    /**
     * Exportação dos dados do próprio clube (JSON). Restrita ao master do clube,
     * que não tem acesso ao backup completo do banco (responsabilidade da plataforma).
     */
    public function exportarDados(ClubExportService $exporter): StreamedResponse
    {
        Gate::authorize('master');

        $club = auth()->user()->club;

        abort_if(! $club, 404, 'Nenhum clube vinculado à sua conta.');

        $data = $exporter->export($club);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response()->streamDownload(
            fn () => print ($json),
            $exporter->filename($club),
            ['Content-Type' => 'application/json']
        );
    }
}
