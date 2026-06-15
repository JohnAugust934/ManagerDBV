<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Historico de backups: cada linha registra o resultado da verificacao de
 * integridade de um backup gerado (por disco), incluindo o manifest com
 * checksums por arquivo. Alimentado por TelegramNotifier::handleBackupWasSuccessful.
 */
class BackupLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'backup_name',
        'filename',
        'path',
        'status',
        'size_bytes',
        'files_count',
        'checksum',
        'has_database',
        'has_uploads',
        'problems',
        'manifest',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'files_count' => 'integer',
        'has_database' => 'boolean',
        'has_uploads' => 'boolean',
        'manifest' => 'array',
    ];

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }
}
