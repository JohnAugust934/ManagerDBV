<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
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

            $lines[] = "<b>{$this->escape((string) $label)}:</b> {$this->escape($this->stringify($value))}";
        }

        $this->send(implode("\n", $lines));
    }

    public function notifyBackupEvent(object $event): void
    {
        if (! $this->adminNotificationsEnabled()) {
            return;
        }

        if ($event instanceof BackupWasSuccessful) {
            $this->notifyAdministrativeAction('Backup concluido com sucesso', [
                'Backup' => $event->backupName,
                'Disco' => $event->diskName,
            ], 'success');

            return;
        }

        if ($event instanceof BackupHasFailed) {
            $this->notifyAdministrativeAction('Falha ao gerar backup', [
                'Backup' => $event->backupName ?: 'Nao informado',
                'Disco' => $event->diskName ?: 'Nao informado',
                'Erro' => $event->exception->getMessage(),
            ], 'error');

            return;
        }

        if ($event instanceof CleanupWasSuccessful) {
            $this->notifyAdministrativeAction('Limpeza de backups concluida', [
                'Backup' => $event->backupName,
                'Disco' => $event->diskName,
            ], 'success');

            return;
        }

        if ($event instanceof CleanupHasFailed) {
            $this->notifyAdministrativeAction('Falha na limpeza de backups', [
                'Backup' => $event->backupName ?: 'Nao informado',
                'Disco' => $event->diskName ?: 'Nao informado',
                'Erro' => $event->exception->getMessage(),
            ], 'error');

            return;
        }

        if ($event instanceof HealthyBackupWasFound) {
            $this->notifyAdministrativeAction('Monitoramento de backup saudavel', [
                'Backup' => $event->backupName,
                'Disco' => $event->diskName,
            ], 'success');

            return;
        }

        if ($event instanceof UnhealthyBackupWasFound) {
            $this->notifyAdministrativeAction('Monitoramento detectou problema no backup', [
                'Backup' => $event->backupName,
                'Disco' => $event->diskName,
                'Falhas' => $this->formatFailures($event->failureMessages),
            ], 'warning');
        }
    }

    public function notifyException(Throwable $exception): void
    {
        if (! $this->errorNotificationsEnabled() || $this->shouldIgnoreException($exception)) {
            return;
        }

        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $url = request()?->fullUrl();
        $method = request()?->method();

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
}
