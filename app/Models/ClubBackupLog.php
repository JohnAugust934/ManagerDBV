<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubBackupLog extends Model
{
    protected $table = 'club_backup_logs';

    protected $fillable = [
        'club_id',
        'disk',
        'path',
        'filename',
        'status',
        'size_bytes',
        'checksum',
        'has_uploads',
        'error',
        'created_by',
        'origin',
    ];

    protected $casts = [
        'has_uploads' => 'boolean',
        'size_bytes' => 'integer',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }
}
