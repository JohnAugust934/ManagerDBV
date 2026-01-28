<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'club_id',   // <--- OBRIGATÓRIO PARA O CLUBE
        'is_master', // <--- OBRIGATÓRIO PARA O ADMIN MASTER
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_master' => 'boolean',
        ];
    }

    /**
     * Relacionamento: Usuário pertence a um Clube.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
