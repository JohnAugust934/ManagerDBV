<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
 * UI de passkeys (Tarefa 2.2): seção no perfil, botão na tela de login e banner
 * pós-login dismissível com persistência por usuário. Aqui validamos render e o
 * endpoint de dispensa do banner; o handshake WebAuthn real é conferência manual.
 */

/** Insere uma credencial diretamente (espelha o helper do PasskeyTest). */
function criarPasskeyUi(User $user, string $nome = 'Chave'): int
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

// --- Tela de login (guest) ---

test('tela de login exibe a opção de entrar com passkey', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Entrar com passkey')
        ->assertSee(route('passkeys.login.options'));
});

// --- Perfil (seção de passkeys) ---

test('perfil exibe a seção de passkeys', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Passkeys')
        ->assertSee('Cadastrar passkey')
        ->assertSee(route('passkeys.index'));
});

// --- Banner pós-login ---

test('banner de passkey aparece para quem não tem passkey nem dispensou', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Entre sem senha com uma passkey');
});

test('banner some depois de dispensado', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('passkeys.banner.dismiss'))
        ->assertOk()
        ->assertJson(['dismissed' => true]);

    expect($user->refresh()->passkey_banner_dispensado_em)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('Entre sem senha com uma passkey');
});

test('banner não aparece para quem já tem passkey', function () {
    $user = User::factory()->create();
    criarPasskeyUi($user);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('Entre sem senha com uma passkey');
});

test('dispensar banner exige autenticação', function () {
    $this->postJson(route('passkeys.banner.dismiss'))->assertUnauthorized();
});

// --- Modelo: regra de exibição do banner ---

test('deveVerBannerPasskey reflete passkey e dispensa', function () {
    $user = User::factory()->create();
    expect($user->deveVerBannerPasskey())->toBeTrue();

    $user->forceFill(['passkey_banner_dispensado_em' => now()])->save();
    expect($user->refresh()->deveVerBannerPasskey())->toBeFalse();

    $outro = User::factory()->create();
    criarPasskeyUi($outro);
    expect($outro->deveVerBannerPasskey())->toBeFalse();
});
