<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use LaravelWebauthn\Services\Webauthn;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Login por passkey (asserção WebAuthn), sem senha — acessível ao visitante.
 *
 * É um caminho ALTERNATIVO ao login por senha do Breeze, que permanece intacto.
 * O fluxo tem dois passos: gerar o desafio de asserção ({@see options()}) e
 * verificar a asserção autenticando o usuário ({@see store()}).
 *
 * O e-mail é informado nos dois passos (a lib chaveia o desafio em cache por
 * usuário + host/IP), espelhando o campo de username configurado na lib.
 */
class PasskeyAutenticacaoController extends Controller
{
    /**
     * Gera o desafio (options) de asserção para o usuário do e-mail informado.
     *
     * Corpo: { email }
     * Resposta: { "publicKey": <PublicKeyCredentialRequestOptions> }
     *
     * Mensagem genérica quando o e-mail não existe ou não tem passkey, para não
     * permitir enumeração de usuários.
     */
    public function options(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = $this->resolverUsuario($validated['email']);

        $publicKey = Webauthn::prepareAssertion($user);

        return response()->json(['publicKey' => $publicKey]);
    }

    /**
     * Verifica a asserção e, se válida, autentica o usuário sem senha.
     *
     * Corpo: { email, id, rawId, response{ clientDataJSON, authenticatorData,
     *          signature, userHandle? }, type, remember? }
     * Resposta: { "redirect": <url> } (JSON) ou redirect padrão.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'id' => ['required', 'string'],
            'rawId' => ['required', 'string'],
            'response' => ['required', 'array'],
            'type' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $user = $this->resolverUsuario($validated['email']);

        try {
            $valido = Webauthn::validateAssertion(
                $user,
                $request->only(['id', 'rawId', 'response', 'type']),
            );
        } catch (HttpExceptionInterface|\Throwable $e) {
            $valido = false;
        }

        if (! $valido) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        Auth::login($user, (bool) ($validated['remember'] ?? false));
        $request->session()->regenerate();

        return response()->json([
            'redirect' => route('dashboard', absolute: false),
        ]);
    }

    /**
     * Resolve o usuário pelo e-mail, lançando erro genérico de credenciais
     * quando não existe (evita enumeração de contas).
     */
    private function resolverUsuario(string $email): User
    {
        $user = User::where(Webauthn::username(), $email)->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return $user;
    }
}
