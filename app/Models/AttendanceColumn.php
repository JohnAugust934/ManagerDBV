<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceColumn extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'club_id',
        'key',
        'name',
        'points',
        'is_fixed',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'points' => 'integer',
        'is_fixed' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(FrequenciaColumnValue::class);
    }
}
