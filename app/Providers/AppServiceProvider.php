<?php

namespace App\Providers;

use App\Models\Desbravador;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use App\Models\User;
use App\Services\TelegramNotifier;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
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
        // O pacote de passkeys (asbiin/laravel-webauthn) é orientado a Fortify e
        // registra automaticamente um conjunto próprio de rotas sob o prefixo
        // "webauthn". Aqui usamos Breeze e expomos rotas/controllers próprios
        // (ver routes/auth.php → "passkeys.*"), consumindo apenas os serviços de
        // challenge/validação da lib. Desligamos o auto-registro para não expor
        // uma segunda superfície de autenticação não usada.
        \LaravelWebauthn\Services\Webauthn::ignoreRoutes();
    }

    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        $this->configureBrazilianLocale();
        $this->configureAuthNotifications();
        $this->registerTelegramBackupListeners();
        $this->registerOperationalListeners();

        if (app()->isLocal()) {
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                if ($query->time > 500) {
                    logger()->warning('Slow query detectada', [
                        'sql' => $query->sql,
                        'time_ms' => $query->time,
                    ]);
                }
            });
        }

        // Slow query log em produção, opt-in via LOG_SLOW_QUERIES=true.
        // Alternativa ao slow query log nativo do MySQL quando o plano não dá acesso ao my.cnf.
        if (app()->isProduction() && env('LOG_SLOW_QUERIES', false)) {
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                if ($query->time > 2000) {
                    \Illuminate\Support\Facades\Log::warning('Slow query em produção', [
                        'sql' => $query->sql,
                        'time_ms' => $query->time,
                    ]);
                }
            });
        }

        // Rate limiting por tenant: 10 gerações de relatório por minuto por clube.
        // Evita que um único clube sobrecarregue o sistema com PDFs pesados em lote.
        RateLimiter::for('relatorios', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->club_id ?? $request->ip());
        });

        // Macro para retry automático em deadlocks MySQL (SQLSTATE 40001).
        // Uso: DB::retryOnDeadlock(fn() => Caixa::create([...]));
        \Illuminate\Support\Facades\DB::macro('retryOnDeadlock', function (callable $callback, int $maxAttempts = 3) {
            $attempt = 0;
            while (true) {
                try {
                    return \Illuminate\Support\Facades\DB::transaction($callback);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($attempt++ >= $maxAttempts || $e->getCode() !== '40001') {
                        throw $e;
                    }
                    usleep(100_000 * $attempt); // backoff linear: 100ms, 200ms, 300ms
                }
            }
        });

        // Super admin de plataforma (cross-tenant). Controla o painel /platform.
        Gate::define('platform-admin', function (User $user) {
            return $user->is_platform_admin === true;
        });

        // "master" = dono do clube. Distinto do platform admin (que opera cross-tenant
        // pelo painel da plataforma, não pelas telas administrativas de um clube).
        Gate::define('master', function (User $user) {
            return $user->role === 'master' && ! $user->is_platform_admin;
        });

        Gate::define('gestao-acessos', fn (User $user) => $user->temPermissao('gestao_acessos'));
        Gate::define('financeiro', fn (User $user) => $user->temPermissao('financeiro'));
        Gate::define('secretaria', fn (User $user) => $user->temPermissao('secretaria'));
        Gate::define('unidades', fn (User $user) => $user->temPermissao('unidades'));
        Gate::define('pedagogico', fn (User $user) => $user->temPermissao('pedagogico'));
        Gate::define('eventos', fn (User $user) => $user->temPermissao('eventos'));
        Gate::define('relatorios', fn (User $user) => $user->temPermissao('relatorios'));
        Gate::define('gerenciar-colunas-chamada', fn (User $user) => $user->is_platform_admin || in_array($user->role, ['master', 'diretor', 'secretario'], true));

        Gate::define('gerir-unidade', function (User $user, $unidade = null) {
            if ($user->temPermissao('unidades')) {
                return true;
            }

            if ($unidade && ($user->role === 'conselheiro' || $user->role === 'instrutor')) {
                // Prefere o vínculo robusto por usuário; cai no nome apenas por compatibilidade
                // com unidades cadastradas antes da coluna conselheiro_user_id.
                if (! is_null($unidade->conselheiro_user_id)) {
                    return (int) $unidade->conselheiro_user_id === (int) $user->id;
                }

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

    public static function snapshotRankingYear(int $year, int $clubId, ?int $generatedBy = null): void
    {
        // Usa o mesmo RankingScorer das telas ao vivo (RankingController) — única
        // fonte da pontuação, garantindo que snapshot e tela nunca divirjam.
        $scorer = app(\App\Services\RankingScorer::class);

        $unitEntries = $scorer->unidades($clubId, $year)
            ->map(fn (Unidade $unidade) => [
                'id' => $unidade->id,
                'nome' => $unidade->nome,
                'pontos' => $scorer->stats($unidade->desbravadores)['total'],
            ])
            ->sortByDesc('pontos')
            ->values()
            ->all();

        $memberEntries = $scorer->desbravadores($clubId, $year)
            ->map(fn (Desbravador $desbravador) => [
                'id' => $desbravador->id,
                'nome' => $desbravador->nome,
                'unidade' => $desbravador->unidade->nome ?? 'Sem unidade',
                'pontos' => $scorer->stats(collect([$desbravador]))['total'],
            ])
            ->sortByDesc('pontos')
            ->values()
            ->all();

        RankingSnapshot::updateOrCreate(
            ['year' => $year, 'scope' => 'unidades', 'club_id' => $clubId],
            ['generated_by' => $generatedBy, 'entries' => $unitEntries, 'generated_at' => now()]
        );

        RankingSnapshot::updateOrCreate(
            ['year' => $year, 'scope' => 'desbravadores', 'club_id' => $clubId],
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
