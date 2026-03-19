<?php

$backupDestinationDisks = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('BACKUP_DESTINATION_DISKS', 'local,r2'))
)));

$backupMonitorDisks = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('BACKUP_MONITOR_DISKS', implode(',', $backupDestinationDisks ?: ['local'])))
)));

$backupNotificationMailTo = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('BACKUP_NOTIFICATIONS_MAIL_TO', env('MAIL_FROM_ADDRESS', '')))
)));

$backupMailNotificationsEnabled = filter_var(env('BACKUP_MAIL_NOTIFICATIONS', false), FILTER_VALIDATE_BOOL);

return [

    'backup' => [
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('framework'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
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
            'continue_on_failure' => false,
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => env('BACKUP_ARCHIVE_ENCRYPTION', 'default'),
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
             * TEMPO DE RETENÇÃO DE BACKUP:
             * Mantém rigidamente os backups dos últimos 7 dias.
             * Tudo mais antigo que 7 dias será automaticamente deletado.
             */
            'keep_all_backups_for_days' => 15,
            'keep_daily_backups_for_days' => 0,
            'keep_weekly_backups_for_weeks' => 0,
            'keep_monthly_backups_for_months' => 0,
            'keep_yearly_backups_for_years' => 0,

            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],

        'tries' => 1,
        'retry_delay' => 0,
    ],

];
