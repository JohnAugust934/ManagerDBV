<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportacaoDesbravadorTest extends TestCase
{
    use RefreshDatabase;

    private function csv(string $conteudo): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv').'.csv';
        file_put_contents($path, $conteudo);

        return new UploadedFile($path, 'membros.csv', 'text/csv', null, true);
    }

    public function test_fluxo_preview_e_confirmacao_importa_membros()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);

        $csv = $this->csv("nome;data_nascimento;sexo;email\n".
            "Maria Importada;10/05/2012;F;maria@email.com\n".
            "Pedro Importado;22/08/2011;M;pedro@email.com\n");

        $preview = $this->actingAs($user)->post(route('desbravadores.importar.preview'), [
            'arquivo' => $csv,
            'unidade_id' => $unidade->id,
        ]);
        $preview->assertOk();
        $preview->assertSee('Maria Importada');
        $preview->assertSee('Pedro Importado');

        $confirm = $this->actingAs($user)->post(route('desbravadores.importar.confirmar'));
        $confirm->assertRedirect(route('desbravadores.index'));

        $this->assertDatabaseHas('desbravadores', [
            'nome' => 'Maria Importada',
            'unidade_id' => $unidade->id,
            'club_id' => $clube->id,
        ]);
        $this->assertEquals(2, Desbravador::count());
    }

    public function test_preview_rejeita_unidade_de_outro_clube()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $outro = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidadeAlheia = Unidade::factory()->create(['club_id' => $outro->id]);

        $csv = $this->csv("nome;data_nascimento;sexo\nMaria Importada;10/05/2012;F\n");

        $this->actingAs($user)->post(route('desbravadores.importar.preview'), [
            'arquivo' => $csv,
            'unidade_id' => $unidadeAlheia->id,
        ])->assertSessionHasErrors('unidade_id');

        $this->assertEquals(0, Desbravador::count());
    }

    public function test_linha_invalida_e_ignorada()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);

        // Segunda linha sem data válida / nome curto.
        $csv = $this->csv("nome;data_nascimento;sexo\n".
            "Valido Silva;10/05/2012;M\n".
            "X;data-ruim;M\n");

        $this->actingAs($user)->post(route('desbravadores.importar.preview'), [
            'arquivo' => $csv,
            'unidade_id' => $unidade->id,
        ])->assertOk()->assertSee('Linha 3');

        $this->actingAs($user)->post(route('desbravadores.importar.confirmar'))
            ->assertRedirect(route('desbravadores.index'));

        $this->assertEquals(1, Desbravador::count());
    }
}
