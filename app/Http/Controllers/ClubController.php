<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ClubController extends Controller
{
    public function edit()
    {
        Gate::authorize('secretaria');
        $club = Club::first(); // Sempre pega o único clube do sistema

        return view('club.edit', compact('club'));
    }

    public function update(Request $request)
    {
        Gate::authorize('secretaria');

        $request->validate([
            'nome' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'associacao' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $club = Club::first();

        if (! $club) {
            // O DIRETOR ESTÁ CRIANDO O CLUBE AGORA!
            $club = Club::create([
                'nome' => $request->nome,
                'cidade' => $request->cidade,
                'associacao' => $request->associacao,
            ]);

            // MÁGICA: Vincula o ID deste clube recém criado a todos os usuários órfãos no banco (Master e Diretor)
            \App\Models\User::whereNull('club_id')->update(['club_id' => $club->id]);

            // Atualiza a sessão atual do Diretor
            auth()->user()->refresh();
        } else {
            $club->update($request->only(['nome', 'cidade', 'associacao']));
        }

        if ($request->hasFile('logo')) {
            if ($club->logo) {
                Storage::disk('public')->delete($club->logo);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $club->update(['logo' => $path]);
        }

        return back()->with('status', 'club-updated')->with('success', 'Informações do clube salvas com sucesso!');
    }

    public function removeLogo()
    {
        Gate::authorize('secretaria');
        $club = Club::first();

        if ($club && $club->logo) {
            Storage::disk('public')->delete($club->logo);
            $club->update(['logo' => null]);
        }

        return back()->with('success', 'Brasão removido com sucesso!');
    }
}
