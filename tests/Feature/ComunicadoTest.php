<?php

namespace Tests\Feature;

use App\Mail\ComunicadoResponsavel;
use App\Models\Club;
use App\Models\Comunicado;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ComunicadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_comunicado_para_membros_ativos_com_email()
    {
        Mail::fake();

        $clube = Club::create(['nome' => 'Clube Orion', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);

        Desbravador::factory()->create(['unidade_id' => $unidade->id, 'club_id' => $clube->id, 'ativo' => true, 'email' => 'a@x.com']);
        Desbravador::factory()->create(['unidade_id' => $unidade->id, 'club_id' => $clube->id, 'ativo' => true, 'email' => 'b@x.com']);
        // Sem e-mail → não recebe.
        Desbravador::factory()->create(['unidade_id' => $unidade->id, 'club_id' => $clube->id, 'ativo' => true, 'email' => null]);

        $response = $this->actingAs($user)->post(route('comunicados.store'), [
            'titulo' => 'Reunião de pais',
            'corpo' => 'Compareçam no sábado.',
            'destinatarios' => 'ativos',
        ]);

        $response->assertRedirect(route('comunicados.index'));
        Mail::assertSent(ComunicadoResponsavel::class, 2);

        $this->assertDatabaseHas('comunicados', [
            'titulo' => 'Reunião de pais',
            'club_id' => $clube->id,
            'total_enviados' => 2,
        ]);
    }

    public function test_unidade_exige_unidade_id()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $this->actingAs($user)->post(route('comunicados.store'), [
            'titulo' => 'X',
            'corpo' => 'Y',
            'destinatarios' => 'unidade',
        ])->assertSessionHasErrors('unidade_id');
    }

    public function test_nao_aceita_unidade_de_outro_clube()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $outro = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidadeAlheia = Unidade::factory()->create(['club_id' => $outro->id]);

        $this->actingAs($user)->post(route('comunicados.store'), [
            'titulo' => 'X',
            'corpo' => 'Y',
            'destinatarios' => 'unidade',
            'unidade_id' => $unidadeAlheia->id,
        ])->assertSessionHasErrors('unidade_id');

        $this->assertDatabaseMissing('comunicados', ['titulo' => 'X']);
    }

    public function test_show_nao_acessa_comunicado_de_outro_clube()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $outro = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $alheio = Comunicado::create([
            'club_id' => $outro->id,
            'titulo' => 'Secreto',
            'corpo' => '...',
            'destinatarios' => 'ativos',
        ]);

        $this->actingAs($user)->get(route('comunicados.show', $alheio->id))->assertNotFound();
    }
}
