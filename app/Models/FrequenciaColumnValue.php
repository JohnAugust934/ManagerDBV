<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrequenciaColumnValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'frequencia_id',
        'attendance_column_id',
        'checked',
        'points_awarded',
    ];

    protected $casts = [
        'checked' => 'boolean',
        'points_awarded' => 'integer',
    ];

    public function frequencia(): BelongsTo
    {
        return $this->belongsTo(Frequencia::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(AttendanceColumn::class, 'attendance_column_id');
    }
}

