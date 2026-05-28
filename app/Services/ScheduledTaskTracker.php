<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Rastreia o resultado de tarefas agendadas em Cache para compor
 * o relatório diário consolidado.
 *
 * Cada tarefa registra seu resultado (success | failure) durante a
 * madrugada. O comando DailyBackupReport lê esses dados às 05:00,
 * envia a notificação consolidada e limpa o Cache.
 */
class ScheduledTaskTracker
{
    /** Chave de cache padrão para os resultados do dia. */
    private const CACHE_PREFIX = 'scheduled_tasks:daily:';

    /** TTL de segurança: 24 horas garante que dados não vazem entre dias. */
    private const TTL_HOURS = 24;

    /**
     * Registra o sucesso silencioso de uma tarefa agendada.
     *
     * @param  string  $taskKey    Identificador único da tarefa (ex: 'backup_run')
     * @param  string  $label      Nome legível (ex: 'Geração de Backup')
     * @param  array<string, string>  $details  Detalhes adicionais opcionais
     */
    public function recordSuccess(string $taskKey, string $label, array $details = []): void
    {
        $this->writeEntry($taskKey, [
            'status'      => 'success',
            'label'       => $label,
            'details'     => $details,
            'recorded_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Registra a falha de uma tarefa agendada.
     *
     * @param  string  $taskKey  Identificador único da tarefa
     * @param  string  $label    Nome legível
     * @param  string  $reason   Mensagem de erro
     */
    public function recordFailure(string $taskKey, string $label, string $reason): void
    {
        $this->writeEntry($taskKey, [
            'status'      => 'failure',
            'label'       => $label,
            'reason'      => $reason,
            'recorded_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Retorna todos os resultados registrados para o dia atual.
     *
     * @return array<string, array{status: string, label: string, details?: array<string, string>, reason?: string, recorded_at: string}>
     */
    public function getAll(): array
    {
        return Cache::get($this->cacheKey(), []);
    }

    /**
     * Remove todos os resultados do dia do Cache.
     * Deve ser chamado pelo DailyBackupReport após o envio do resumo.
     */
    public function flush(): void
    {
        Cache::forget($this->cacheKey());
    }

    /**
     * Indica se há ao menos uma tarefa com falha registrada.
     */
    public function hasFailures(): bool
    {
        return collect($this->getAll())
            ->contains(fn (array $entry): bool => $entry['status'] === 'failure');
    }

    /**
     * Indica se nenhuma tarefa foi registrada ainda (útil para detectar
     * o cenário em que todas as tarefas falharam antes de registrar).
     */
    public function isEmpty(): bool
    {
        return empty($this->getAll());
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function writeEntry(string $taskKey, array $entry): void
    {
        $key  = $this->cacheKey();
        $data = Cache::get($key, []);

        $data[$taskKey] = $entry;

        Cache::put($key, $data, now()->addHours(self::TTL_HOURS));
    }

    /**
     * Gera a chave de Cache escopada pela data de hoje no fuso da aplicação,
     * evitando colisões entre dias distintos.
     */
    private function cacheKey(): string
    {
        return self::CACHE_PREFIX . Carbon::now(config('app.timezone', 'UTC'))->toDateString();
    }
}
