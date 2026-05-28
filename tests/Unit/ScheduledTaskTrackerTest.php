<?php

namespace Tests\Unit;

use App\Services\ScheduledTaskTracker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ScheduledTaskTrackerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
    }

    public function test_registra_sucesso_no_cache(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $tracker->recordSuccess('backup_run', 'Geração de Backup', [
            'Backup' => 'meu-backup',
            'Disco'  => 'local',
        ]);

        $results = $tracker->getAll();

        $this->assertArrayHasKey('backup_run', $results);
        $this->assertSame('success', $results['backup_run']['status']);
        $this->assertSame('Geração de Backup', $results['backup_run']['label']);
        $this->assertSame('meu-backup', $results['backup_run']['details']['Backup']);
    }

    public function test_registra_falha_no_cache(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $tracker->recordFailure('backup_run', 'Geração de Backup', 'Conexão recusada');

        $results = $tracker->getAll();

        $this->assertArrayHasKey('backup_run', $results);
        $this->assertSame('failure', $results['backup_run']['status']);
        $this->assertSame('Conexão recusada', $results['backup_run']['reason']);
    }

    public function test_acumula_multiplas_tarefas_na_mesma_chave(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $tracker->recordSuccess('backup_run', 'Geração de Backup');
        $tracker->recordSuccess('backup_clean', 'Limpeza de Backups');
        $tracker->recordSuccess('backup_monitor', 'Monitoramento de Backup');

        $results = $tracker->getAll();

        $this->assertCount(3, $results);
        $this->assertArrayHasKey('backup_run', $results);
        $this->assertArrayHasKey('backup_clean', $results);
        $this->assertArrayHasKey('backup_monitor', $results);
    }

    public function test_flush_limpa_todos_os_resultados(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $tracker->recordSuccess('backup_run', 'Geração de Backup');
        $tracker->recordSuccess('backup_clean', 'Limpeza de Backups');

        $this->assertCount(2, $tracker->getAll());

        $tracker->flush();

        $this->assertEmpty($tracker->getAll());
    }

    public function test_retorna_array_vazio_quando_nenhuma_tarefa_registrada(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $this->assertEmpty($tracker->getAll());
    }

    public function test_is_empty_retorna_true_sem_registros(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $this->assertTrue($tracker->isEmpty());
    }

    public function test_is_empty_retorna_false_com_registros(): void
    {
        $tracker = app(ScheduledTaskTracker::class);
        $tracker->recordSuccess('backup_run', 'Geração de Backup');

        $this->assertFalse($tracker->isEmpty());
    }

    public function test_has_failures_retorna_false_apenas_com_sucessos(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $tracker->recordSuccess('backup_run', 'Geração de Backup');
        $tracker->recordSuccess('backup_clean', 'Limpeza de Backups');

        $this->assertFalse($tracker->hasFailures());
    }

    public function test_has_failures_retorna_true_com_ao_menos_uma_falha(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $tracker->recordSuccess('backup_run', 'Geração de Backup');
        $tracker->recordFailure('backup_clean', 'Limpeza de Backups', 'Disco cheio');

        $this->assertTrue($tracker->hasFailures());
    }

    public function test_ultima_entrada_sobrescreve_a_mesma_task_key(): void
    {
        $tracker = app(ScheduledTaskTracker::class);

        $tracker->recordSuccess('backup_run', 'Geração de Backup');
        $tracker->recordFailure('backup_run', 'Geração de Backup', 'Segunda tentativa falhou');

        $results = $tracker->getAll();

        $this->assertCount(1, $results);
        $this->assertSame('failure', $results['backup_run']['status']);
    }

    public function test_chave_de_cache_e_escopada_pela_data_atual(): void
    {
        $tracker = app(ScheduledTaskTracker::class);
        $tracker->recordSuccess('backup_run', 'Geração de Backup');

        $expectedKey = 'scheduled_tasks:daily:'.Carbon::now(config('app.timezone', 'UTC'))->toDateString();

        $this->assertNotNull(Cache::get($expectedKey));
    }

    public function test_resultado_possui_campo_recorded_at_valido(): void
    {
        $tracker = app(ScheduledTaskTracker::class);
        $tracker->recordSuccess('backup_run', 'Geração de Backup');

        $results = $tracker->getAll();

        $this->assertArrayHasKey('recorded_at', $results['backup_run']);
        $this->assertNotEmpty($results['backup_run']['recorded_at']);
    }
}
