<?php

/*
 * Descarta discos de nuvem (s3/r2) sem bucket configurado. Sem isso, um disco
 * de nuvem mal configurado deixa o bucket nulo e o Flysystem lanca TypeError ao
 * inicializar, abortando o backup inteiro (inclusive o local). Discos locais
 * sao sempre mantidos.
 */
$keepUsableDisks = static function (array $disks): array {
    $cloudBucketEnv = ['s3' => 'AWS_BUCKET', 'r2' => 'R2_BUCKET'];

    return array_values(array_filter($disks, static function (string $disk) use ($cloudBucketEnv): bool {
        if (! isset($cloudBucketEnv[$disk])) {
            return true;
        }

        $bucket = env($cloudBucketEnv[$disk]);

        return is_string($bucket) && trim($bucket) !== '';
    }));
};

$backupDestinationDisks = $keepUsableDisks(array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('BACKUP_DESTINATION_DISKS', 'local,r2'))
))));

$backupMonitorDisks = $keepUsableDisks(array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('BACKUP_MONITOR_DISKS', implode(',', $backupDestinationDisks ?: ['local'])))
))));

$backupNotificationMailFallback = env('MAIL_FROM_ADDRESS', 'hello@example.com');
if (! is_string($backupNotificationMailFallback) || trim($backupNotificationMailFallback) === '') {
    $backupNotificationMailFallback = 'hello@example.com';
}

$backupNotificationMailRaw = env('BACKUP_NOTIFICATIONS_MAIL_TO');
if (! is_string($backupNotificationMailRaw) || trim($backupNotificationMailRaw) === '') {
    $backupNotificationMailRaw = $backupNotificationMailFallback;
}

$backupNotificationMailTo = array_values(array_filter(array_map(
    'trim',
    explode(',', $backupNotificationMailRaw)
)));

$backupMailNotificationsEnabled = filter_var(env('BACKUP_MAIL_NOTIFICATIONS', false), FILTER_VALIDATE_BOOL);

/*
 * Criptografia do zip (AES via ZipArchive) depende de uma libzip compilada com
 * suporte a cifragem. A hospedagem atual (Hostinger, PHP 8.4/alt-php) EXPOE a
 * constante ZipArchive::EM_AES_256, entao o spatie acredita que pode cifrar,
 * mas a libzip em runtime NAO cifra: o listener EncryptBackupArchive chama
 * setEncryptionIndex() e o ZipArchive::close() quebra com
 * "ZipArchive::close(): Invalid argument", abortando o backup inteiro DEPOIS
 * de o zip ja ter sido criado. Como o spatie so tenta cifrar quando ha senha,
 * o ambiente local (sem BACKUP_ARCHIVE_PASSWORD) sempre funcionou e a producao
 * (com senha) nunca funcionou.
 *
 * Por isso a criptografia fica DESLIGADA por padrao, mesmo que exista um
 * BACKUP_ARCHIVE_PASSWORD remanescente no .env. So habilite
 * (BACKUP_ARCHIVE_ENCRYPTION_ENABLED=true) num ambiente cujo libzip realmente
 * cifre. Os backups seguem protegidos por ficarem em disco privado (fora do
 * webroot) e em bucket R2 privado.
 */
$archiveEncryptionEnabled = filter_var(env('BACKUP_ARCHIVE_ENCRYPTION_ENABLED', false), FILTER_VALIDATE_BOOL);
$archivePassword = null;
$archiveEncryption = false;
if ($archiveEncryptionEnabled) {
    $rawArchivePassword = env('BACKUP_ARCHIVE_PASSWORD');
    $archivePassword = (is_string($rawArchivePassword) && $rawArchivePassword !== '') ? $rawArchivePassword : null;
    $archiveEncryption = env('BACKUP_ARCHIVE_ENCRYPTION', 'default');
}

return [

    'backup' => [
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [
            'files' => [
                // Faz backup APENAS dos uploads dos usuarios. O codigo da aplicacao
                // esta versionado no Git (recuperavel) e a restauracao so consome o
                // dump do banco + arquivos sob /app/public/ (ver BackupController::
                // restore). Zipar base_path() inteiro era desnecessario e fragil:
                // arquivos volateis (sessao, cache, logs, .git, o proprio zip
                // temporario) mudavam durante a execucao e quebravam o fechamento
                // do arquivo com "ZipArchive::close(): Invalid argument".
                'include' => [
                    storage_path('app/public'),
                ],
                'exclude' => [],
                'follow_links' => false,
                // Resiliencia: um diretorio sem permissao de leitura nao deve
                // abortar o backup inteiro.
                'ignore_unreadable_directories' => true,
                'relative_path' => null,
            ],
            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_timestamp_format' => null,
        'database_dump_filename_base' => 'database',
        'database_dump_file_extension' => '',

        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9,
            'filename_prefix' => '',
            'disks' => [
                ...($backupDestinationDisks ?: ['local']),
            ],
            'continue_on_failure' => filter_var(env('BACKUP_CONTINUE_ON_FAILURE', true), FILTER_VALIDATE_BOOL),
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => $archivePassword,
        'encryption' => $archiveEncryption,
        'verify_backup' => env('BACKUP_VERIFY', true),
        'tries' => 1,
        'retry_delay' => 0,
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => $backupMailNotificationsEnabled ? ['mail'] : [],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => $backupMailNotificationsEnabled ? ['mail'] : [],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => $backupMailNotificationsEnabled ? ['mail'] : [],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => $backupMailNotificationsEnabled ? ['mail'] : [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => $backupMailNotificationsEnabled ? ['mail'] : [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => $backupMailNotificationsEnabled ? ['mail'] : [],
        ],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
        'mail' => [
            'to' => $backupNotificationMailTo,

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'DBV Manager')),
            ],
        ],
        'slack' => [
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL', ''),
            'channel' => env('BACKUP_SLACK_CHANNEL'),
            'username' => env('BACKUP_SLACK_USERNAME'),
            'icon' => env('BACKUP_SLACK_ICON'),
        ],
        'discord' => [
            'webhook_url' => env('BACKUP_DISCORD_WEBHOOK_URL', ''),
            'username' => env('BACKUP_DISCORD_USERNAME', ''),
            'avatar_url' => env('BACKUP_DISCORD_AVATAR_URL', ''),
        ],
        'webhook' => [
            'url' => env('BACKUP_WEBHOOK_URL', ''),
        ],
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => $backupMonitorDisks ?: ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => (int) env('BACKUP_MONITOR_MAX_AGE_DAYS', 1),
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => (int) env('BACKUP_MONITOR_MAX_STORAGE_MB', 5000),
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            /*
             * TEMPO DE RETENÇÃO DE BACKUP (configurável por .env, sem deploy de codigo):
             *
             * - BACKUP_RETENTION_DAYS: knob principal — mantem TODOS os backups
             *   dos ultimos N dias (default 15). A ROTACAO em si e feita pelo
             *   comando `backup:clean` do spatie (agendado as 04:00), nao por
             *   logica propria — evita duplicar/competir com o pacote.
             * - BACKUP_KEEP_ALL_DAYS: override avancado; se definido, prevalece
             *   sobre BACKUP_RETENTION_DAYS para esta regra especifica.
             * - As regras granulares (daily/weekly/monthly/yearly) ficam em 0 por
             *   padrao. Para reter historico de longo prazo sem acumular tudo,
             *   ative-as no .env (ex.: KEEP_MONTHLY_MONTHS=6 guarda 1 backup por
             *   mes nos ultimos 6 meses). Como os uploads sao pequenos (backup
             *   real de producao ~1.5 MB), reter mais nao estoura o limite de MB.
             * - BACKUP_MAX_STORAGE_MB: quando o total ultrapassa este teto, o
             *   spatie apaga os mais antigos primeiro (default 5000 MB).
             */
            // BACKUP_ANTIGO: 'keep_all_backups_for_days' => (int) env('BACKUP_KEEP_ALL_DAYS', 15),
            'keep_all_backups_for_days' => (int) env('BACKUP_KEEP_ALL_DAYS', (int) env('BACKUP_RETENTION_DAYS', 15)),
            'keep_daily_backups_for_days' => (int) env('BACKUP_KEEP_DAILY_DAYS', 0),
            'keep_weekly_backups_for_weeks' => (int) env('BACKUP_KEEP_WEEKLY_WEEKS', 0),
            'keep_monthly_backups_for_months' => (int) env('BACKUP_KEEP_MONTHLY_MONTHS', 0),
            'keep_yearly_backups_for_years' => (int) env('BACKUP_KEEP_YEARLY_YEARS', 0),

            'delete_oldest_backups_when_using_more_megabytes_than' => (int) env('BACKUP_MAX_STORAGE_MB', 5000),
        ],

        'tries' => 1,
        'retry_delay' => 0,
    ],

    /*
     * Verificacao de integridade PROPRIA (alem do verify nativo do spatie).
     * Roda apos cada backup bem-sucedido (por disco) em BackupIntegrityVerifier,
     * acionado pelo TelegramNotifier::notifyBackupEvent. Um zip "verde" porem
     * vazio/truncado/sem dump deixa de passar silenciosamente: vira alerta
     * imediato no Telegram. Nao e lido pelo spatie (chave ignorada por ele).
     */
    'integrity' => [
        'enabled' => filter_var(env('BACKUP_INTEGRITY_CHECK_ENABLED', true), FILTER_VALIDATE_BOOL),
        // Tamanho minimo aceitavel do zip; abaixo disso e considerado vazio/truncado.
        'min_size_kb' => (int) env('BACKUP_MIN_SIZE_KB', 50),
    ],

];
