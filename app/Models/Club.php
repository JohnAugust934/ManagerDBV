<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cidade',
        'associacao',
        'logo', // Novo campo
    ];

    // Helper para pegar a URL do logo ou uma imagem padrão
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        // Retorna um placeholder se não tiver logo
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nome) . '&color=7F9CF5&background=EBF4FF';
    }
}
