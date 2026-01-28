<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClubManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_club_edit_page()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id]);

        $response = $this->actingAs($user)->get(route('club.edit'));

        $response->assertStatus(200);
        $response->assertSee('Clube Teste');
    }

    public function test_user_can_update_club_info_and_upload_logo()
    {
        Storage::fake('public');

        $club = Club::create(['nome' => 'Clube Antigo', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id]);

        $file = UploadedFile::fake()->image('brasao.jpg');

        $response = $this->actingAs($user)->patch(route('club.update'), [
            'nome' => 'Clube Novo',
            'cidade' => 'Rio',
            'logo' => $file,
        ]);

        $response->assertRedirect();

        $club->refresh();

        $this->assertEquals('Clube Novo', $club->nome);
        $this->assertNotNull($club->logo);
        Storage::disk('public')->assertExists($club->logo);
    }
}
