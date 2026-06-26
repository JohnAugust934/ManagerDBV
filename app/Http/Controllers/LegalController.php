<?php

namespace App\Http\Controllers;

use App\Services\LgpdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    public function privacidade()
    {
        return view('legal.politica-privacidade');
    }

    public function termos()
    {
        return view('legal.termos-de-uso');
    }

    /**
     * Tela de aceite obrigatório dos termos para usuários que ainda não aceitaram
     * (cadastrados antes da exigência LGPD). Quem já aceitou é mandado direto ao painel.
     */
    public function mostrarAceiteTermos(Request $request)
    {
        if ($request->user()->termos_aceitos_em !== null) {
            return redirect()->route('dashboard');
        }

        return view('legal.aceitar-termos');
    }

    /**
     * Registra o aceite: grava o timestamp no usuário e o consentimento no ROPA (Art. 37).
     */
    public function registrarAceiteTermos(Request $request): RedirectResponse
    {
        $request->validate(['aceite_termos' => ['accepted']]);

        $user = $request->user();

        // Idempotente: se outra aba já aceitou, não sobrescreve o timestamp original.
        if ($user->termos_aceitos_em === null) {
            $user->forceFill(['termos_aceitos_em' => now()])->save();

            LgpdService::registrar(
                acao: 'consentimento',
                entidade: 'usuario',
                entidadeId: $user->id,
                metadados: ['tipo' => 'termos_de_uso_e_privacidade', 'origem' => 'aceite_retroativo'],
                request: $request,
            );
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Obrigado! Seu aceite dos termos foi registrado.');
    }
}
