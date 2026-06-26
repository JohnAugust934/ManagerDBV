<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use App\Services\ClubExportService;
use App\Services\ClubImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Invariantes de backup ainda não asseguradas pela suíte existente
 * (BackupSystemTest cobre include/encryption/disco-sem-bucket;
 * BackupRestoreIntegrationTest cobre a restauração ponta a ponta;
 * ClubBackupTest/ClubExportImportRoundTripTest cobrem isolamento de export).
 *
 * Aqui cobrimos: pasta de destino derivada de APP_NAME e o isolamento na
 * IMPORTAÇÃO (restaurar um clube não pode tocar em outro clube já existente).
 */
class BackupInvariantesComplementaresTest extends TestCase
{
    use RefreshDatabase;

    public function test_pasta_de_destino_do_backup_deriva_de_app_name(): void
    {
        // config/backup.php usa env('APP_NAME') tanto no nome do backup quanto no
        // notifiable; ambos devem coincidir com o nome da aplicação. Mudar APP_NAME
        // orfana os backups antigos (por isso BackupController lista recursivamente).
        $this->assertSame(config('app.name'), config('backup.backup.name'));
        $this->assertNotEmpty(config('backup.backup.name'));
        $this->assertIsString(config('backup.backup.name'));
    }

    public function test_importar_um_clube_nao_altera_outro_clube_existente(): void
    {
        // Clube já existente no banco (o "sobrevivente") com dados próprios.
        $clubB = Club::create(['nome' => 'Clube Existente', 'cidade' => 'RJ', 'associacao' => 'APaC']);
        $unidadeB = Unidade::factory()->create(['club_id' => $clubB->id, 'nome' => 'Unidade B']);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id, 'nome' => 'Dbv Sobrevivente']);
        Caixa::create(['descricao' => 'Caixa B', 'valor' => 77, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $clubB->id]);

        $caixasBAntes = Caixa::withoutGlobalScopes()->where('club_id', $clubB->id)->count();
        $dbvBAntes = Desbravador::withoutGlobalScopes()->where('club_id', $clubB->id)->count();

        // Clube de origem, exportado e reimportado como um clube novo.
        $clubA = Club::create(['nome' => 'Clube Origem', 'cidade' => 'SP', 'associacao' => 'APaC']);
        User::factory()->create(['club_id' => $clubA->id, 'role' => 'master', 'email' => 'origem@clube.com']);
        $unidadeA = Unidade::factory()->create(['club_id' => $clubA->id, 'nome' => 'Unidade A']);
        Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'nome' => 'Dbv A']);
        Caixa::create(['descricao' => 'Caixa A', 'valor' => 10, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $clubA->id]);

        $payload = (new ClubExportService)->export($clubA->fresh());
        $report = (new ClubImportService)->import($payload, 'Clube Restaurado');

        $newClubId = $report['club_id'];
        $this->assertNotSame($clubB->id, $newClubId);
        $this->assertNotSame($clubA->id, $newClubId);

        // O clube B permanece intacto — mesma contagem, mesmos registros.
        $this->assertSame($caixasBAntes, Caixa::withoutGlobalScopes()->where('club_id', $clubB->id)->count());
        $this->assertSame($dbvBAntes, Desbravador::withoutGlobalScopes()->where('club_id', $clubB->id)->count());
        $this->assertDatabaseHas('desbravadores', ['id' => $dbvB->id, 'nome' => 'Dbv Sobrevivente', 'club_id' => $clubB->id]);
        $this->assertDatabaseHas('caixas', ['descricao' => 'Caixa B', 'club_id' => $clubB->id]);

        // E o clube restaurado recebeu somente os dados do clube de origem.
        $this->assertDatabaseHas('caixas', ['descricao' => 'Caixa A', 'club_id' => $newClubId]);
        $this->assertSame(0, Caixa::withoutGlobalScopes()->where('club_id', $newClubId)->where('descricao', 'Caixa B')->count());
    }
}
