<?php

namespace App\Http\Controllers;

use App\Models\ConsentimentoPrivacidade;
use App\Models\Desbravador;
use App\Services\ClubContext;
use App\Services\LgpdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Consentimento LGPD do desbravador: aceite, revogação e registro da via física.
 *
 * Autorização e isolamento de tenant vêm do grupo de rotas (can:secretaria) e do
 * global scope (route-model binding de outro clube resulta em 404). Toda operação
 * também é registrada no ROPA via LgpdService (Art. 37).
 */
class ConsentimentoPrivacidadeController extends Controller
{
    public function index(Desbravador $desbravador)
    {
        $consentimentos = $desbravador->consentimentosPrivacidade()
            ->orderByDesc('created_at')
            ->get();

        $ativo = $desbravador->consentimentoPrivacidadeAtivo();

        return view('privacidade.index', compact('desbravador', 'consentimentos', 'ativo'));
    }

    public function aceitar(Request $request, Desbravador $desbravador): RedirectResponse
    {
        $data = $request->validate([
            'versao_termo' => ['nullable', 'string', 'max:20'],
            'responsavel_nome' => ['nullable', 'string', 'max:255'],
        ]);

        $versao = $data['versao_termo'] ?? config('privacidade.versao_termo');
        $responsavel = $data['responsavel_nome'] ?? $desbravador->nome_responsavel;

        $consentimento = ConsentimentoPrivacidade::create([
            'desbravador_id' => $desbravador->id,
            'responsavel_nome' => $responsavel,
            'versao_termo' => $versao,
            'termo_snapshot' => $this->renderizarTermo($versao),
            'aceito_em' => now(),
            'aceito_ip' => $request->ip(),
        ]);

        // Mantém as colunas legadas do cadastro sincronizadas com a fonte da verdade.
        $desbravador->forceFill([
            'consentimento_lgpd' => true,
            'consentimento_lgpd_em' => now(),
            'consentimento_lgpd_responsavel' => $responsavel,
        ])->save();

        LgpdService::registrar(
            acao: 'consentimento',
            entidade: 'desbravador',
            entidadeId: $desbravador->id,
            metadados: ['tipo' => 'privacidade', 'versao_termo' => $versao, 'consentimento_id' => $consentimento->id],
            request: $request,
        );

        return back()->with('success', 'Consentimento registrado com sucesso.');
    }

    public function revogar(Request $request, Desbravador $desbravador, ConsentimentoPrivacidade $consentimento): RedirectResponse
    {
        abort_if($consentimento->desbravador_id !== $desbravador->id, 404);
        abort_unless($consentimento->estaAtivo(), 422, 'Este consentimento já não está mais ativo.');

        $data = $request->validate([
            'motivo_revogacao' => ['required', 'string', 'max:1000'],
        ]);

        $consentimento->update([
            'revogado_em' => now(),
            'revogado_por' => $request->user()->name,
            'motivo_revogacao' => $data['motivo_revogacao'],
        ]);

        // Reflete a revogação nas colunas legadas do cadastro (sem apagar histórico).
        $desbravador->forceFill(['consentimento_lgpd' => false])->save();

        LgpdService::registrar(
            acao: 'revogacao_consentimento',
            entidade: 'desbravador',
            entidadeId: $desbravador->id,
            metadados: ['tipo' => 'privacidade', 'consentimento_id' => $consentimento->id, 'motivo' => $data['motivo_revogacao']],
            request: $request,
        );

        return back()->with('success', 'Revogação registrada. O consentimento anterior permanece no histórico.');
    }

    /**
     * Registra que a via física assinada foi recebida. Se não houver consentimento
     * ativo, cria um novo aceite (a via física também é fonte da verdade — mantém
     * um único fluxo de consentimento).
     */
    public function viaFisicaRecebida(Request $request, Desbravador $desbravador): RedirectResponse
    {
        $request->validate([
            'arquivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $consentimento = $desbravador->consentimentoPrivacidadeAtivo();

        $caminho = null;
        if ($request->hasFile('arquivo')) {
            $caminho = $request->file('arquivo')->store("consentimentos/{$desbravador->id}", 'public');
        }

        if ($consentimento) {
            $consentimento->update([
                'via_fisica_recebida_em' => now(),
                'via_fisica_caminho' => $caminho ?? $consentimento->via_fisica_caminho,
            ]);
        } else {
            $versao = config('privacidade.versao_termo');
            $consentimento = ConsentimentoPrivacidade::create([
                'desbravador_id' => $desbravador->id,
                'responsavel_nome' => $desbravador->nome_responsavel,
                'versao_termo' => $versao,
                'termo_snapshot' => $this->renderizarTermo($versao),
                'aceito_em' => now(),
                'aceito_ip' => $request->ip(),
                'via_fisica_recebida_em' => now(),
                'via_fisica_caminho' => $caminho,
            ]);

            $desbravador->forceFill([
                'consentimento_lgpd' => true,
                'consentimento_lgpd_em' => now(),
                'consentimento_lgpd_responsavel' => $desbravador->nome_responsavel,
            ])->save();
        }

        LgpdService::registrar(
            acao: 'consentimento',
            entidade: 'desbravador',
            entidadeId: $desbravador->id,
            metadados: ['tipo' => 'privacidade_via_fisica', 'consentimento_id' => $consentimento->id],
            request: $request,
        );

        return back()->with('success', 'Via física registrada com sucesso.');
    }

    private function renderizarTermo(string $versao): string
    {
        return view('privacidade.termo', [
            'versao' => $versao,
            'clubeNome' => ClubContext::currentClub()?->nome ?? config('app.name'),
        ])->render();
    }
}
