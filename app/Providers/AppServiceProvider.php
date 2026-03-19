<?php

namespace App\Providers;

use App\Models\User;
use App\Models\RankingSnapshot;
use App\Services\TelegramNotifier;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\QueueBusy;
use App\Models\Desbravador;
use App\Models\Unidade;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureBrazilianLocale();
        $this->configureAuthNotifications();
        $this->registerTelegramBackupListeners();
        $this->registerOperationalListeners();

        Gate::define('master', function (User $user) {
            return $user->role === 'master';
        });

        Gate::define('financeiro', fn (User $user) => $user->temPermissao('financeiro'));
        Gate::define('secretaria', fn (User $user) => $user->temPermissao('secretaria'));
        Gate::define('unidades', fn (User $user) => $user->temPermissao('unidades'));
        Gate::define('pedagogico', fn (User $user) => $user->temPermissao('pedagogico'));
        Gate::define('eventos', fn (User $user) => $user->temPermissao('eventos'));
        Gate::define('relatorios', fn (User $user) => $user->temPermissao('relatorios'));

        Gate::define('gerir-unidade', function (User $user, $unidade = null) {
            if ($user->temPermissao('unidades')) {
                return true;
            }

            if ($unidade && ($user->role === 'conselheiro' || $user->role === 'instrutor')) {
                return $unidade->conselheiro === $user->name;
            }

            return false;
        });
    }

    private function configureBrazilianLocale(): void
    {
        $locale = $this->normalizeLocale(config('app.locale', 'pt_BR'));
        $fallbackLocale = $this->normalizeLocale(config('app.fallback_locale', 'pt_BR'));

        config([
            'app.locale' => $locale,
            'app.fallback_locale' => $fallbackLocale,
        ]);

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR.utf8', 'pt_BR', 'Portuguese_Brazil.1252');
    }

    private function configureAuthNotifications(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
            $name = trim((string) ($notifiable->name ?? ''));

            return (new MailMessage)
                ->subject('Redefinicao de senha - '.config('app.name'))
                ->greeting($name !== '' ? 'Ola, '.$name.'!' : 'Ola!')
                ->line('Recebemos uma solicitacao para redefinir a senha da sua conta no '.config('app.name').'.')
                ->action('Redefinir senha', $url)
                ->line("Este link e valido por {$minutes} minutos.")
                ->line('Se voce nao fez essa solicitacao, pode ignorar este e-mail com seguranca.')
                ->salutation('Equipe '.config('app.name'));
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $name = trim((string) ($notifiable->name ?? ''));

            return (new MailMessage)
                ->subject('Confirmacao de e-mail - '.config('app.name'))
                ->greeting($name !== '' ? 'Ola, '.$name.'!' : 'Ola!')
                ->line('Confirme seu endereco de e-mail para concluir o acesso ao sistema.')
                ->action('Confirmar e-mail', $url)
                ->line('Se voce nao criou essa conta, nenhuma acao adicional e necessaria.')
                ->salutation('Equipe '.config('app.name'));
        });
    }

    private function registerTelegramBackupListeners(): void
    {
        $events = [
            BackupWasSuccessful::class,
            BackupHasFailed::class,
            CleanupWasSuccessful::class,
            CleanupHasFailed::class,
            HealthyBackupWasFound::class,
            UnhealthyBackupWasFound::class,
        ];

        foreach ($events as $eventClass) {
            Event::listen($eventClass, function (object $event) {
                app(TelegramNotifier::class)->notifyBackupEvent($event);
            });
        }
    }

    private function registerOperationalListeners(): void
    {
        Event::listen(QueueBusy::class, function (QueueBusy $event) {
            app(TelegramNotifier::class)->notifyAdministrativeAction('Fila acima do limite configurado', [
                'Conexao' => $event->connection,
                'Fila' => $event->queue,
                'Tamanho atual' => $event->size,
            ], 'warning');
        });

        Event::listen(JobFailed::class, function (JobFailed $event) {
            app(TelegramNotifier::class)->notifyAdministrativeAction('Falha em job da fila', [
                'Conexao' => $event->connectionName,
                'Job' => $event->job->resolveName(),
                'Erro' => $event->exception->getMessage(),
            ], 'error');
        });
    }

    public static function snapshotRankingYear(int $year, ?int $generatedBy = null): void
    {
        $unitEntries = Unidade::with(['desbravadores.frequencias' => fn ($query) => $query->whereYear('data', $year)])
            ->orderBy('nome')
            ->get()
            ->map(function (Unidade $unidade) {
                $members = $unidade->desbravadores;
                $points = $members->sum(fn ($desbravador) => $desbravador->frequencias->sum('pontos'));
                $memberCount = $members->count();

                return [
                    'id' => $unidade->id,
                    'name' => $unidade->nome,
                    'members' => $memberCount,
                    'points' => $points,
                    'average' => $memberCount > 0 ? round($points / $memberCount, 1) : 0.0,
                ];
            })
            ->sortByDesc('points')
            ->values()
            ->map(function (array $entry, int $index) {
                $entry['position'] = $index + 1;

                return $entry;
            })
            ->all();

        $memberEntries = Desbravador::with(['unidade', 'frequencias' => fn ($query) => $query->whereYear('data', $year)])
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(function (Desbravador $desbravador) {
                return [
                    'id' => $desbravador->id,
                    'name' => $desbravador->nome,
                    'unit' => $desbravador->unidade->nome ?? 'Sem unidade',
                    'presences' => $desbravador->frequencias->where('presente', true)->count(),
                    'points' => $desbravador->frequencias->sum('pontos'),
                ];
            })
            ->sortByDesc('points')
            ->values()
            ->map(function (array $entry, int $index) {
                $entry['position'] = $index + 1;

                return $entry;
            })
            ->all();

        RankingSnapshot::updateOrCreate(
            ['year' => $year, 'scope' => 'unidades'],
            ['generated_by' => $generatedBy, 'entries' => $unitEntries, 'generated_at' => now()]
        );

        RankingSnapshot::updateOrCreate(
            ['year' => $year, 'scope' => 'desbravadores'],
            ['generated_by' => $generatedBy, 'entries' => $memberEntries, 'generated_at' => now()]
        );
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = str_replace('-', '_', trim($locale));

        return match (strtolower($normalized)) {
            'br', 'pt', 'pt_br' => 'pt_BR',
            default => $normalized === '' ? 'pt_BR' : $normalized,
        };
    }
}
