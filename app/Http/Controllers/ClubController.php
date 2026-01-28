<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClubController extends Controller
{
    /**
     * Exibe o formulário de edição do clube.
     */
    public function edit(): View
    {
        $club = auth()->user()->club;

        if (!$club) {
            abort(404, 'Nenhum clube vinculado a este usuário.');
        }

        return view('club.edit', compact('club'));
    }

    /**
     * Atualiza os dados do clube.
     */
    public function update(Request $request): RedirectResponse
    {
        $club = auth()->user()->club;

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'associacao' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        // Upload do Logo
        if ($request->hasFile('logo')) {
            // Apaga o antigo se existir
            if ($club->logo && Storage::disk('public')->exists($club->logo)) {
                Storage::disk('public')->delete($club->logo);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $path;
        }

        $club->update($validated);

        return back()->with('success', 'Dados do clube atualizados com sucesso!');
    }

    /**
     * Remove o brasão do clube.
     */
    public function destroyLogo(): RedirectResponse
    {
        $club = auth()->user()->club;

        if ($club && $club->logo) {
            // Remove o arquivo físico
            if (Storage::disk('public')->exists($club->logo)) {
                Storage::disk('public')->delete($club->logo);
            }

            // Remove a referência no banco
            $club->update(['logo' => null]);
        }

        return back()->with('success', 'Brasão removido com sucesso!');
    }
}
