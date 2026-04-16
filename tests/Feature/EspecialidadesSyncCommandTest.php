<?php

namespace Tests\Feature;

use App\Models\Especialidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EspecialidadesSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_dry_run_nao_grava_dados(): void
    {
        Http::fake([
            'https://mda.wiki.br/*' => Http::response($this->catalogHtml(), 200),
        ]);

        Artisan::call('especialidades:sync-oficiais', ['--dry-run' => true]);

        $this->assertDatabaseCount('especialidades', 0);
        $this->assertStringContainsString('dry-run', Artisan::output());
    }

    public function test_sync_grava_catalogo_e_requisitos(): void
    {
        Http::fake([
            'https://mda.wiki.br/ADRA' => Http::response($this->catalogHtml(), 200),
            'https://mda.wiki.br/Artes_e_Habilidades_Manuais' => Http::response('', 200),
            'https://mda.wiki.br/Atividades_Agr%C3%ADcolas' => Http::response('', 200),
            'https://mda.wiki.br/Atividades_Mission%C3%A1rias_e_Comunit%C3%A1rias' => Http::response('', 200),
            'https://mda.wiki.br/Atividades_Profissionais' => Http::response('', 200),
            'https://mda.wiki.br/Atividades_Recreativas' => Http::response('', 200),
            'https://mda.wiki.br/Ci%C3%AAncia_e_Sa%C3%BAde' => Http::response('', 200),
            'https://mda.wiki.br/Estudos_da_Natureza' => Http::response('', 200),
            'https://mda.wiki.br/Habilidades_Dom%C3%A9sticas' => Http::response('', 200),
            'https://mda.wiki.br/Mestrados' => Http::response('', 200),
            'https://mda.wiki.br/Especialidade_de_*' => Http::response($this->requirementsHtml(), 200),
        ]);

        Artisan::call('especialidades:sync-oficiais', ['--requirements' => true, '--limit' => 1]);

        $this->assertDatabaseHas('especialidades', [
            'codigo' => 'AD-001',
            'nome' => 'Alívio da Fome',
            'is_oficial' => true,
        ]);

        $esp = Especialidade::where('codigo', 'AD-001')->firstOrFail();

        $this->assertGreaterThan(
            0,
            DB::table('especialidade_requisitos')->where('especialidade_id', $esp->id)->count()
        );
    }

    private function catalogHtml(): string
    {
        return "<figure><figcaption><b>AD-001</b><br><a href='https://mda.wiki.br/Especialidade_de_Alívio_da_Fome'>Alívio da Fome</a></figcaption></figure>";
    }

    private function requirementsHtml(): string
    {
        return "<ol><li><span class='texto'>Primeiro requisito.</span></li><li><span class='texto'>Segundo requisito.</span></li></ol>";
    }
}
