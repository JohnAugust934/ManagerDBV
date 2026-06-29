<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubBackupLog;
use App\Models\LgpdRegistro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Garante que backup:prune-manifests poda os históricos que crescem sem limite:
 * club_backup_logs (backup por clube) e lgpd_registros (ROPA), além de backup_logs.
 * A janela é BACKUP_LOG_RETENTION_DAYS.
 */
class PruneBackupManifestsTest extends TestCase
{
    use RefreshDatabase;

    private function comData(\Illuminate\Database\Eloquent\Model $registro, \Carbon\Carbon $quando): void
    {
        $registro->forceFill(['created_at' => $quando, 'updated_at' => $quando])->saveQuietly();
    }

    public function test_poda_club_backup_logs_e_ropa_antigos_preserva_recentes(): void
    {
        config(['app.env' => 'testing']);
        putenv('BACKUP_LOG_RETENTION_DAYS=365');

        $clube = Club::create(['nome' => 'Clube Poda', 'cidade' => 'SP']);

        $logAntigo = ClubBackupLog::create(['club_id' => $clube->id, 'disk' => 'local', 'path' => 'a.zip', 'filename' => 'a.zip', 'status' => 'success']);
        $logRecente = ClubBackupLog::create(['club_id' => $clube->id, 'disk' => 'local', 'path' => 'b.zip', 'filename' => 'b.zip', 'status' => 'success']);
        $this->comData($logAntigo, now()->subDays(400));
        $this->comData($logRecente, now()->subDays(10));

        $ropaAntigo = LgpdRegistro::create(['club_id' => $clube->id, 'acao' => 'concessao', 'entidade' => 'desbravador', 'entidade_id' => 1]);
        $ropaRecente = LgpdRegistro::create(['club_id' => $clube->id, 'acao' => 'concessao', 'entidade' => 'desbravador', 'entidade_id' => 2]);
        $this->comData($ropaAntigo, now()->subDays(400));
        $this->comData($ropaRecente, now()->subDays(10));

        $this->artisan('backup:prune-manifests')->assertExitCode(0);

        $this->assertDatabaseMissing('club_backup_logs', ['id' => $logAntigo->id]);
        $this->assertDatabaseHas('club_backup_logs', ['id' => $logRecente->id]);
        $this->assertDatabaseMissing('lgpd_registros', ['id' => $ropaAntigo->id]);
        $this->assertDatabaseHas('lgpd_registros', ['id' => $ropaRecente->id]);
    }
}
