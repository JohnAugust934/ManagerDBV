<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    use HasFactory;

    protected $fillable = ['classe_id', 'codigo', 'descricao', 'categoria'];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
}
