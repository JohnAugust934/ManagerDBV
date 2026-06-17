<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'scope',
        'club_id',
        'generated_by',
        'entries',
        'generated_at',
    ];

    protected $casts = [
        'entries' => 'array',
        'generated_at' => 'datetime',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
