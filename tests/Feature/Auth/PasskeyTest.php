<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use LaravelWebauthn\Services\Webauthn\CredentialAssertionValidator;
use LaravelWebauthn\Services\Webauthn\CredentialAttestationValidator;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Uid\NilUuid;
use Webauthn\CredentialRecord;
use Webauthn\TrustPath\EmptyTrustPath;

/*
 * Backend de passkeys (WebAuthn) — registro/gerência (usuário autenticado) e
 * login por asserção (sem senha). A criptografia WebAuthn em si é exercitada
 * pela suíte da própria lib; aqui validamos a integração standalone (rotas,
 * controllers, persistência, login e isolamento por usuário), substituindo os
 * validadores de atestação/asserção por fakes no container.
 */

/**
 * Insere uma credencial diretamente, sem passar pelo fluxo de atestação, para
 * cenários de listagem/remoção e isolamento.
 */
function criarPasskey(User $user, string $nome = 'Chave'): int
{
    return DB::table('webauthn_keys')->insertGetId([
        'user_id' => $user->id,
        'name' => $nome,
        'credentialId' => 'cred-'.$user->id.'-'.$nome,
        'type' => 'public-key',
        'transports' => '[]',
        'attestationType' => 'none',
        'trustPath' => '{}',
        'aaguid' => '',
        'credentialPublicKey' => 'pk',
        'counter' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/** Faz o validador de atestação devolver uma credencial válida para o usuário. */
function fakeAtestacaoValida(User $user): void
{
    $record = new CredentialRecord(
        publicKeyCredentialId: 'cred-id-novo',
        type: 'public-key',
        transports: [],
        attestationType: 'none',
        trustPath: new EmptyTrustPath,
        aaguid: new NilUuid,
        credentialPublicKey: 'public-key-bytes',
        userHandle: (string) $user->id,
        counter: 0,
    );

    test()->mock(CredentialAttestationValidator::class)
        ->shouldReceive('__invoke')
        ->andReturn($record);
}

// --- Registro de credencial (usuário autenticado) ---

test('gera options de registro para usuário autenticado', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('passkeys.options'));

    $response->assertOk()
        ->assertJsonStructure(['publicKey' => ['challenge', 'rp', 'user', 'pubKeyCredParams']]);
});

test('options de registro exige autenticação', function () {
    $this->postJson(route('passkeys.options'))->assertUnauthorized();
});

test('valida atestação e persiste a credencial do usuário', function () {
    $user = User::factory()->create();
    fakeAtestacaoValida($user);

    $payload = [
        'id' => 'cred-id-novo',
        'rawId' => 'cred-id-novo',
        'response' => ['clientDataJSON' => 'x', 'attestationObject' => 'y'],
        'type' => 'public-key',
        'name' => 'Meu celular',
    ];

    $response = $this->actingAs($user)->postJson(route('passkeys.store'), $payload);

    $response->assertCreated()->assertJsonPath('passkey.name', 'Meu celular');
    expect(DB::table('webauthn_keys')->where('user_id', $user->id)->count())->toBe(1);
});

// --- Listagem e remoção (isolamento por usuário) ---

test('lista apenas as passkeys do próprio usuário', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();
    criarPasskey($user, 'Minha');
    criarPasskey($outro, 'Alheia');

    $response = $this->actingAs($user)->getJson(route('passkeys.index'));

    $response->assertOk()->assertJsonCount(1, 'passkeys')
        ->assertJsonPath('passkeys.0.name', 'Minha');
});

test('usuário remove a própria passkey', function () {
    $user = User::factory()->create();
    $id = criarPasskey($user);

    $this->actingAs($user)->deleteJson(route('passkeys.destroy', $id))->assertOk();

    expect(DB::table('webauthn_keys')->where('id', $id)->exists())->toBeFalse();
});

test('usuário não remove passkey de outro usuário', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();
    $idAlheio = criarPasskey($outro);

    $this->actingAs($user)->deleteJson(route('passkeys.destroy', $idAlheio))->assertNotFound();

    expect(DB::table('webauthn_keys')->where('id', $idAlheio)->exists())->toBeTrue();
});

// --- Login por asserção (sem senha) ---

test('gera options de login por passkey para o e-mail informado', function () {
    $user = User::factory()->create();
    criarPasskey($user);

    $response = $this->postJson(route('passkeys.login.options'), ['email' => $user->email]);

    $response->assertOk()->assertJsonStructure(['publicKey' => ['challenge']]);
});

test('asserção válida autentica o usuário sem senha', function () {
    $user = User::factory()->create();

    $this->mock(CredentialAssertionValidator::class)
        ->shouldReceive('__invoke')
        ->andReturn(true);

    $payload = [
        'email' => $user->email,
        'id' => 'cred-id',
        'rawId' => 'cred-id',
        'response' => ['clientDataJSON' => 'x', 'authenticatorData' => 'y', 'signature' => 'z'],
        'type' => 'public-key',
    ];

    $response = $this->postJson(route('passkeys.login'), $payload);

    $response->assertOk()->assertJsonPath('redirect', route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
});

test('asserção inválida não autentica', function () {
    $user = User::factory()->create();

    $this->mock(CredentialAssertionValidator::class)
        ->shouldReceive('__invoke')
        ->andReturn(false);

    $response = $this->postJson(route('passkeys.login'), [
        'email' => $user->email,
        'id' => 'cred-id',
        'rawId' => 'cred-id',
        'response' => ['clientDataJSON' => 'x'],
        'type' => 'public-key',
    ]);

    $response->assertStatus(422);
    $this->assertGuest();
});

test('login por passkey com e-mail inexistente devolve erro genérico', function () {
    $response = $this->postJson(route('passkeys.login.options'), ['email' => 'naoexiste@clube.com']);

    $response->assertStatus(422)->assertJsonValidationErrors('email');
});

// --- Login por passkey SEM e-mail (usernameless / discoverable credentials) ---

/**
 * Insere uma credencial cujo credentialId é um base64url real, para o lookup
 * por rawId do fluxo usernameless. Retorna o rawId a enviar na asserção.
 */
function criarPasskeyDiscoverable(User $user): string
{
    $binario = 'cred-binario-'.$user->id;
    $rawId = Base64UrlSafe::encodeUnpadded($binario);

    DB::table('webauthn_keys')->insert([
        'user_id' => $user->id,
        'name' => 'Discoverable',
        'credentialId' => $rawId,
        'type' => 'public-key',
        'transports' => '[]',
        'attestationType' => 'none',
        'trustPath' => '{}',
        'aaguid' => '',
        'credentialPublicKey' => 'pk',
        'counter' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $rawId;
}

test('gera options de login sem e-mail (usernameless)', function () {
    $response = $this->postJson(route('passkeys.login.options'), []);

    $response->assertOk()->assertJsonStructure(['publicKey' => ['challenge']]);
});

test('asserção sem e-mail autentica o dono da credencial', function () {
    $user = User::factory()->create();
    $rawId = criarPasskeyDiscoverable($user);

    $this->mock(CredentialAssertionValidator::class)
        ->shouldReceive('__invoke')
        ->andReturn(true);

    $response = $this->postJson(route('passkeys.login'), [
        'id' => $rawId,
        'rawId' => $rawId,
        'response' => ['clientDataJSON' => 'x', 'authenticatorData' => 'y', 'signature' => 'z'],
        'type' => 'public-key',
    ]);

    $response->assertOk()->assertJsonPath('redirect', route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
});

test('asserção sem e-mail com credencial desconhecida devolve erro genérico', function () {
    $response = $this->postJson(route('passkeys.login'), [
        'id' => Base64UrlSafe::encodeUnpadded('inexistente'),
        'rawId' => Base64UrlSafe::encodeUnpadded('inexistente'),
        'response' => ['clientDataJSON' => 'x', 'authenticatorData' => 'y', 'signature' => 'z'],
        'type' => 'public-key',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('email');
    $this->assertGuest();
});

// --- Convivência com o login por senha do Breeze ---

test('login por senha do Breeze continua funcionando com passkeys instaladas', function () {
    $user = User::factory()->create();
    criarPasskey($user);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});
