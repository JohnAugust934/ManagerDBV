<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ClubManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_club_edit_page()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'Cidade Teste']);
        // CORREÇÃO: Define papel de diretor
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->get(route('club.edit'));

        $response->assertStatus(200);
        $response->assertSee('Clube Teste');
    }

    public function test_user_can_update_club_info_and_upload_logo()
    {
        $club = Club::create(['nome' => 'Clube Velho', 'cidade' => 'Cidade Velha']);
        // CORREÇÃO: Define papel de diretor
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        $file = UploadedFile::fake()->image('logo.jpg');

        $response = $this->actingAs($user)->patch(route('club.update'), [
            'nome' => 'Clube Novo',
            'cidade' => 'Rio',
            'logo' => $file,
        ]);

        $response->assertRedirect();

        $club->refresh();

        $this->assertEquals('Clube Novo', $club->nome);
        $this->assertNotNull($club->logo);
    }
}
