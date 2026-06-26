<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LaravelWebauthn\Services\Webauthn;

/**
 * Gerência de passkeys (credenciais WebAuthn) do PRÓPRIO usuário autenticado.
 *
 * O login por passkey é um método ALTERNATIVO e aditivo à senha do Breeze — a
 * senha continua válida. Aqui ficam: geração do desafio de registro, validação
 * da atestação e persistência, além de listar/remover as credenciais do usuário.
 * A autenticação por asserção (login sem senha) fica em {@see PasskeyAutenticacaoController}.
 *
 * As rotas são standalone (não usam Fortify); consomem apenas os serviços de
 * options/validação da lib asbiin/laravel-webauthn via a fachada Webauthn.
 */
class PasskeyController extends Controller
{
    /**
     * Lista as passkeys do usuário autenticado.
     *
     * Resposta: { "passkeys": [ { id, name, type, transports, created_at, updated_at } ] }
     */
    public function index(Request $request): JsonResponse
    {
        $passkeys = Webauthn::model()::where('user_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['passkeys' => $passkeys]);
    }

    /**
     * Gera o desafio (options) para registrar uma nova passkey.
     *
     * O desafio é guardado em cache pela lib (chaveado por usuário + host/IP) e
     * conferido no passo de verificação ({@see store()}).
     *
     * Resposta: { "publicKey": <PublicKeyCredentialCreationOptions> }
     */
    public function options(Request $request): JsonResponse
    {
        $publicKey = Webauthn::prepareAttestation($request->user());

        return response()->json(['publicKey' => $publicKey]);
    }

    /**
     * Valida a atestação devolvida pelo autenticador e persiste a credencial.
     *
     * Corpo: { id, rawId, response{ clientDataJSON, attestationObject }, type, name? }
     * Resposta (201): { "passkey": { id, name, type, ... } }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string'],
            'rawId' => ['required', 'string'],
            'response' => ['required', 'array'],
            'type' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $nome = $validated['name'] ?? 'Passkey';

        try {
            $passkey = Webauthn::validateAttestation(
                $request->user(),
                $request->only(['id', 'rawId', 'response', 'type']),
                $nome,
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'passkey' => 'Não foi possível validar a passkey. Tente registrar novamente.',
            ]);
        }

        return response()->json(['passkey' => $passkey], 201);
    }

    /**
     * Remove uma passkey do PRÓPRIO usuário. O filtro por user_id garante que um
     * usuário nunca remova credencial de outro (404 caso não seja dele).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        Webauthn::model()::where('user_id', $request->user()->getAuthIdentifier())
            ->findOrFail($id)
            ->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * Marca o convite (banner) de cadastro de passkey como dispensado para o
     * usuário autenticado, persistindo a escolha para que não reapareça.
     */
    public function dispensarBanner(Request $request): JsonResponse
    {
        $request->user()->forceFill([
            'passkey_banner_dispensado_em' => now(),
        ])->save();

        return response()->json(['dismissed' => true]);
    }
}
