<?php

namespace App\Services;

use App\Models\AttendanceColumn;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AttendanceColumnService
{
    private const DEFAULT_FIXED_COLUMNS = [
        ['key' => 'presente', 'name' => 'Presente', 'points' => 10, 'sort_order' => 10],
        ['key' => 'pontual', 'name' => 'Pontual', 'points' => 5, 'sort_order' => 20],
        ['key' => 'biblia', 'name' => 'Biblia', 'points' => 5, 'sort_order' => 30],
        ['key' => 'uniforme', 'name' => 'Uniforme', 'points' => 10, 'sort_order' => 40],
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
                    'points' => $defaultColumn['points'],
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
                'points' => $column['points'],
                'is_fixed' => true,
                'is_active' => true,
                'sort_order' => $column['sort_order'],
            ];
        });
    }
}
