<?php

namespace App\Services;

use App\Support\OperationalWindow;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class TelegramNotifier
{
    public function notifyAdministrativeAction(string $title, array $details = [], string $status = 'info'): void
    {
        if (! $this->adminNotificationsEnabled()) {
            return;
        }

        $badges = [
            'success' => '🟢',
            'warning' => '🟡',
            'error' => '🔴',
            'info' => '🔵',
        ];

        $icon = $badges[$status] ?? $badges['info'];

        $lines = [
            "<b>{$icon} {$this->escape($title)}</b>",
            "<b>Sistema:</b> {$this->escape(config('app.name', 'Sistema'))}",
            "<b>Ambiente:</b> {$this->escape(app()->environment())}",
            "<b>Quando:</b> {$this->escape($this->timestamp())}",
        ];

        foreach ($details as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $lines[] = "<b>{$this->escape((string) $label)}:</b> {$this->escape($this->limit($this->stringify($value), 800))}";
        }

        $this->send(implode("\n", $lines));
    }

    public function notifyBackupEvent(object $event): void
    {
        if (! $this->adminNotificationsEnabled()) {
            return;
        }

        /** @var ScheduledTaskTracker $tracker */
        $tracker = app(ScheduledTaskTracker::class);

        // ── SUCESSOS: armazena no Cache silenciosamente ───────────────────────
        // O resumo consolidado será enviado pelo DailyBackupReport às 05:00.

        if ($event instanceof BackupWasSuccessful) {
            $tracker->recordSuccess('backup_run', 'Geração de Backup', [
                'Backup' => $event->backupName,
                'Disco'  => $event->diskName,
            ]);

            return;
        }

        if ($event instanceof CleanupWasSuccessful) {
            $tracker->recordSuccess('backup_clean', 'Limpeza de Backups', [
                'Backup' => $event->backupName,
                'Disco'  => $event->diskName,
            ]);

            return;
        }

        if ($event instanceof HealthyBackupWasFound) {
            $tracker->recordSuccess('backup_monitor', 'Monitoramento de Backup', [
                'Backup' => $event->backupName,
                'Disco'  => $event->diskName,
            ]);

            return;
        }

        // ── FALHAS: notifica o Telegram imediatamente ─────────────────────────

        if ($event instanceof BackupHasFailed) {
            $tracker->recordFailure(
                'backup_run',
                'Geração de Backup',
                $event->exception->getMessage()
            );

            $this->notifyScheduledFailure('Falha ao gerar backup', [
                'Backup' => $event->backupName ?: 'Nao informado',
                'Disco'  => $event->diskName ?: 'Nao informado',
                'Erro'   => $event->exception->getMessage(),
            ]);

            return;
        }

        if ($event instanceof CleanupHasFailed) {
            $tracker->recordFailure(
                'backup_clean',
                'Limpeza de Backups',
                $event->exception->getMessage()
            );

            $this->notifyScheduledFailure('Falha na limpeza de backups', [
                'Backup' => $event->backupName ?: 'Nao informado',
                'Disco'  => $event->diskName ?: 'Nao informado',
                'Erro'   => $event->exception->getMessage(),
            ]);

            return;
        }

        if ($event instanceof UnhealthyBackupWasFound) {
            $tracker->recordFailure(
                'backup_monitor',
                'Monitoramento de Backup',
                $this->formatFailures($event->failureMessages)
            );

            $this->notifyScheduledFailure('Monitoramento detectou problema no backup', [
                'Backup' => $event->backupName,
                'Disco'  => $event->diskName,
                'Falhas' => $this->formatFailures($event->failureMessages),
            ]);
        }
    }

    /**
     * Dispara imediatamente um alerta de falha em tarefa agendada.
     * Deve ser chamado quando qualquer rotina da madrugada falhar.
     *
     * @param  string  $title    Título do alerta
     * @param  array<string, string>  $details  Contexto adicional
     */
    public function notifyScheduledFailure(string $title, array $details = []): void
    {
        $this->notifyAdministrativeAction($title, $details, 'error');
    }

    /**
     * Envia a mensagem de resumo consolidado ao Telegram.
     * Chamado exclusivamente pelo DailyBackupReport às 05:00.
     *
     * @param  array<string, array{status: string, label: string, details?: array<string, string>, reason?: string, recorded_at: string}>  $results
     */
    public function sendDailySummary(array $results): void
    {
        if (! $this->adminNotificationsEnabled()) {
            return;
        }

        $appName = $this->escape(config('app.name', 'Sistema'));
        $env     = $this->escape(app()->environment());
        $date    = Carbon::now(config('app.timezone'))->format('d/m/Y');
        $time    = Carbon::now(config('app.timezone'))->format('H:i:s');

        $lines = [
            "<b>📋 Relatório Diário — {$appName}</b>",
            "<b>Sistema:</b> {$appName} | <b>Ambiente:</b> {$env}",
            "<b>Data:</b> {$date}",
            '',
        ];

        if (empty($results)) {
            $lines[] = '⚠️ Nenhuma tarefa registrou resultado esta madrugada.';
        } else {
            foreach ($results as $entry) {
                $icon  = $entry['status'] === 'success' ? '✅' : '🔴';
                $label = $this->escape($entry['label']);

                if ($entry['status'] === 'success') {
                    $lines[] = "<b>{$icon} {$label}</b> — Sucesso";

                    foreach ($entry['details'] ?? [] as $key => $value) {
                        $lines[] = "   <b>{$this->escape((string) $key)}:</b> {$this->escape($this->stringify($value))}";
                    }
                } else {
                    $lines[] = "<b>{$icon} {$label}</b> — Falha";
                    $lines[] = '   <b>Erro:</b> '.$this->escape($this->limit((string) ($entry['reason'] ?? ''), 300));
                }

                $lines[] = '';
            }
        }

        $lines[] = "<i>🕐 Gerado às {$time}</i>";

        $this->send(implode("\n", $lines));
    }

    public function notifyException(Throwable $exception): void
    {
        if (! $this->errorNotificationsEnabled() || $this->shouldIgnoreException($exception)) {
            return;
        }

        if ($this->shouldSuppressTransientDatabaseException($exception)) {
            return;
        }

        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $url = request()?->fullUrl();
        $method = request()?->method();

        if (! $this->shouldSendExceptionNotification($exception, $statusCode, $method, $url)) {
            return;
        }

        $this->send(implode("\n", [
            '<b>🚨 Erro no sistema</b>',
            '<b>Sistema:</b> '.$this->escape(config('app.name', 'Sistema')),
            '<b>Ambiente:</b> '.$this->escape(app()->environment()),
            '<b>Quando:</b> '.$this->escape($this->timestamp()),
            '<b>Status:</b> '.$this->escape((string) $statusCode),
            '<b>Tipo:</b> '.$this->escape($exception::class),
            '<b>Mensagem:</b> '.$this->escape($this->limit($exception->getMessage(), 800)),
            '<b>Metodo:</b> '.$this->escape($method ?: 'Console'),
            '<b>URL:</b> '.$this->escape($url ?: config('app.url')),
            '<b>Arquivo:</b> '.$this->escape($exception->getFile().':'.$exception->getLine()),
        ]));
    }

    public function adminNotificationsEnabled(): bool
    {
        return $this->isEnabled() && (bool) config('services.telegram.admin_notifications', true);
    }

    public function errorNotificationsEnabled(): bool
    {
        return $this->isEnabled() && (bool) config('services.telegram.error_notifications', true);
    }

    private function isEnabled(): bool
    {
        return (bool) config('services.telegram.enabled')
            && filled(config('services.telegram.bot_token'))
            && filled(config('services.telegram.chat_id'));
    }

    private function send(string $message): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        // O Telegram rejeita mensagens acima de 4096 caracteres (HTTP 400
        // "message is too long"). Truncamos como rede de seguranca para que
        // nenhuma notificacao com payload grande derrube o fluxo principal.
        $message = $this->limit($message, 4096);

        try {
            Http::asForm()
                ->timeout((int) config('services.telegram.timeout', 10))
                ->post($this->endpoint(), [
                    'chat_id' => config('services.telegram.chat_id'),
                    'text' => $message,
                    'parse_mode' => config('services.telegram.parse_mode', 'HTML'),
                    'disable_web_page_preview' => true,
                ])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
        } catch (Throwable) {
            // Silencia qualquer falha do Telegram para nunca derrubar o fluxo principal.
        }
    }

    private function shouldIgnoreException(Throwable $exception): bool
    {
        return $exception instanceof NotFoundHttpException
            || $exception instanceof MethodNotAllowedHttpException
            || $exception instanceof ValidationException
            || $exception instanceof AuthenticationException
            || $exception instanceof AuthorizationException
            || $exception instanceof HttpResponseException;
    }

    private function endpoint(): string
    {
        return 'https://api.telegram.org/bot'.config('services.telegram.bot_token').'/sendMessage';
    }

    private function shouldSendExceptionNotification(
        Throwable $exception,
        int $statusCode,
        ?string $method,
        ?string $url
    ): bool {
        $dedupSeconds = max(0, (int) config('services.telegram.error_dedup_seconds', 300));
        if ($dedupSeconds === 0) {
            return true;
        }

        $fingerprint = implode('|', [
            app()->environment(),
            (string) $statusCode,
            $exception::class,
            $this->limit($exception->getMessage(), 300),
            (string) $method,
            (string) $url,
            $exception->getFile(),
            (string) $exception->getLine(),
        ]);

        $key = 'telegram:error:dedup:'.sha1($fingerprint);

        return Cache::add($key, now()->timestamp, $dedupSeconds);
    }

    private function shouldSuppressTransientDatabaseException(Throwable $exception): bool
    {
        if (! (bool) config('services.telegram.suppress_transient_db_errors', true)) {
            return false;
        }

        if (! $this->isTransientDatabaseConnectivityException($exception)) {
            return false;
        }

        $windows = (string) config('services.telegram.transient_db_suppress_windows', '02:45-04:45');
        if (trim($windows) === '') {
            return false;
        }

        return OperationalWindow::isNowInAnyWindow($windows, (string) config('app.timezone', 'UTC'));
    }

    private function isTransientDatabaseConnectivityException(Throwable $exception): bool
    {
        $needlePatterns = [
            'sqlstate[hy000] [2002] connection refused',
            'sqlstate[hy000] [2002] no such file or directory',
            'sqlstate[hy000] [2006] mysql server has gone away',
            'sqlstate[08006]',
            'sqlstate[08001]',
            'could not connect to server',
            'server has gone away',
        ];

        $current = $exception;
        while ($current !== null) {
            $message = strtolower($current->getMessage());

            foreach ($needlePatterns as $pattern) {
                if (str_contains($message, $pattern)) {
                    return true;
                }
            }

            $current = $current->getPrevious();
        }

        return false;
    }

    private function timestamp(): string
    {
        return Carbon::now(config('app.timezone'))->format('d/m/Y H:i:s');
    }

    private function formatFailures(Collection $failures): string
    {
        return $failures
            ->map(fn (array $failure) => ($failure['check'] ?? 'checagem').': '.($failure['message'] ?? 'sem detalhes'))
            ->implode(' | ');
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function limit(string $value, int $length): string
    {
        return mb_strlen($value) > $length
            ? mb_substr($value, 0, $length - 3).'...'
            : $value;
    }

    private function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Sim' : 'Nao';
        }

        if (is_array($value)) {
            return implode(', ', array_map(fn ($item) => $this->stringify($item), $value));
        }

        return (string) $value;
    }

    /**
     * Publish a raw message to Telegram without additional formatting.
     *
     * @param  string  $message  The message to send
     */
    public function publish(string $message): void
    {
        $this->send($message);
    }
}