<?php

namespace Tests\Feature;

use App\Models\Ata;
use App\Models\Club;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_editar_uma_ata()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $ata = Ata::factory()->create([
            'titulo' => 'Ata original',
            'hora_inicio' => '19:30',
            'hora_fim' => '21:00',
            'local' => 'Sala principal',
            'conteudo' => 'Conteudo original',
        ]);

        $response = $this->actingAs($user)->put(route('atas.update', $ata), [
            'titulo' => 'Ata revisada',
            'data_reuniao' => now()->format('Y-m-d'),
            'hora_inicio' => '19:30',
            'hora_fim' => '21:00',
            'local' => 'Sala principal',
            'participantes' => 'Diretoria',
            'conteudo' => 'Conteudo atualizado da ata.',
        ]);

        $response->assertRedirect(route('atas.show', $ata));
        $this->assertDatabaseHas('atas', [
            'id' => $ata->id,
            'titulo' => 'Ata revisada',
            'conteudo' => 'Conteudo atualizado da ata.',
        ]);
    }

    public function test_usuario_pode_excluir_uma_ata()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $ata = Ata::factory()->create([
            'titulo' => 'Ata para exclusao',
            'hora_inicio' => '19:30',
            'hora_fim' => '21:00',
            'local' => 'Sala principal',
        ]);

        $response = $this->actingAs($user)->delete(route('atas.destroy', $ata));

        $response->assertRedirect(route('atas.index'));
        $this->assertDatabaseMissing('atas', ['id' => $ata->id]);
    }

    public function test_relatorio_de_impressao_da_ata_exibe_layout_padronizado()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $ata = Ata::factory()->create([
            'titulo' => 'Reuniao da Diretoria',
            'hora_inicio' => '19:30',
            'hora_fim' => '21:00',
            'local' => 'Sala principal',
        ]);

        $response = $this->actingAs($user)->get(route('atas.show', $ata));

        $response->assertOk();
        $response->assertSeeText('Documento oficial padronizado para secretaria');
        $response->assertSeeText('Imprimir');
        $response->assertSeeText('Reuniao da Diretoria');
        $response->assertSeeText('Sala principal');
    }

    public function test_pode_gerar_pdf_da_ata_em_nova_rota_de_impressao()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $ata = Ata::factory()->create([
            'titulo' => 'Ata de Impressao',
            'hora_inicio' => '19:30',
            'hora_fim' => '21:00',
            'local' => 'Sala principal',
        ]);

        $pdfWrapper = \Mockery::mock(DomPdfWrapper::class);
        $pdfWrapper->shouldReceive('setPaper')->once()->with('a4')->andReturnSelf();
        $pdfWrapper->shouldReceive('stream')->once()->andReturn(response('pdf', 200, ['content-type' => 'application/pdf']));

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data) use ($ata) {
                $this->assertSame('relatorios.ata', $view);
                $this->assertSame($ata->id, $data['ata']->id);

                return true;
            })
            ->andReturn($pdfWrapper);

        $response = $this->actingAs($user)->get(route('atas.print', $ata));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
