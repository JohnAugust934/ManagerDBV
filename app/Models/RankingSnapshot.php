<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankingSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'scope',
        'generated_by',
        'entries',
        'generated_at',
    ];

    protected $casts = [
        'entries' => 'array',
        'generated_at' => 'datetime',
    ];
}
