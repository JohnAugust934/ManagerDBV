<?php

namespace App\Services;

use App\Models\AttendanceColumn;
use App\Models\Frequencia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AttendanceColumnService
{
    /**
     * Colunas fixas padrão. Os pontos vêm de Frequencia::PONTOS_LEGADO — fonte
     * única, para o padrão do modo novo não divergir do fallback legado.
     */
    private const DEFAULT_FIXED_COLUMNS = [
        ['key' => 'presente', 'name' => 'Presente', 'sort_order' => 10],
        ['key' => 'pontual', 'name' => 'Pontual', 'sort_order' => 20],
        ['key' => 'biblia', 'name' => 'Biblia', 'sort_order' => 30],
        ['key' => 'uniforme', 'name' => 'Uniforme', 'sort_order' => 40],
    ];

    public function usesLegacyColumns(): bool
    {
        return ! Schema::hasTable('attendance_columns');
    }

    public function ensureFixedColumns(?int $clubId): void
    {
        if ($this->usesLegacyColumns() || empty($clubId) || $clubId <= 0) {
            return;
        }

        foreach (self::DEFAULT_FIXED_COLUMNS as $defaultColumn) {
            AttendanceColumn::firstOrCreate(
                [
                    'club_id' => $clubId,
                    'key' => $defaultColumn['key'],
                ],
                [
                    'name' => $defaultColumn['name'],
                    'points' => Frequencia::PONTOS_LEGADO[$defaultColumn['key']],
                    'is_fixed' => true,
                    'is_active' => true,
                    'sort_order' => $defaultColumn['sort_order'],
                ]
            );
        }
    }

    public function getActiveColumnsForClub(?int $clubId): Collection
    {
        if ($this->usesLegacyColumns() || empty($clubId) || $clubId <= 0) {
            return $this->legacyColumns();
        }

        $this->ensureFixedColumns($clubId);

        return AttendanceColumn::query()
            ->where('club_id', $clubId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getColumnsForManagement(?int $clubId): Collection
    {
        if ($this->usesLegacyColumns() || empty($clubId) || $clubId <= 0) {
            return $this->legacyColumns();
        }

        $this->ensureFixedColumns($clubId);

        return AttendanceColumn::query()
            ->where('club_id', $clubId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function legacyColumns(): Collection
    {
        return collect(self::DEFAULT_FIXED_COLUMNS)->map(function (array $column) {
            return (object) [
                'id' => $column['key'],
                'key' => $column['key'],
                'name' => $column['name'],
                'points' => Frequencia::PONTOS_LEGADO[$column['key']],
                'is_fixed' => true,
                'is_active' => true,
                'sort_order' => $column['sort_order'],
            ];
        });
    }
}
