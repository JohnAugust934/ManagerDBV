<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'role',
        'club_id',
        'extra_permissions',
        'registered_at'
    ];

    protected $casts = [
        'extra_permissions' => 'array',
        'registered_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
