<?php

namespace Tests\Unit;

use App\Models\AttendanceColumn;
use App\Models\Club;
use App\Services\AttendanceColumnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AttendanceColumnService em isolamento: criação idempotente das colunas fixas,
 * listagem ativa e fail-safe sem clube.
 */
class AttendanceColumnServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceColumnService $service;

    private int $clubId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AttendanceColumnService;
        $this->clubId = Club::create(['nome' => 'Clube Unit', 'cidade' => 'SP'])->id;
    }

    public function test_ensure_fixed_columns_e_idempotente(): void
    {
        $this->service->ensureFixedColumns($this->clubId);
        $this->service->ensureFixedColumns($this->clubId);

        // Sem duplicar: continua com as 4 fixas.
        $this->assertSame(4, AttendanceColumn::where('club_id', $this->clubId)->where('is_fixed', true)->count());
    }

    public function test_ensure_fixed_columns_ignora_clube_invalido(): void
    {
        $this->service->ensureFixedColumns(null);
        $this->service->ensureFixedColumns(0);

        $this->assertSame(0, AttendanceColumn::count());
    }

    public function test_get_active_columns_retorna_apenas_ativas(): void
    {
        $this->service->ensureFixedColumns($this->clubId);
        AttendanceColumn::where('club_id', $this->clubId)->where('key', 'pontual')->update(['is_active' => false]);

        $ativas = $this->service->getActiveColumnsForClub($this->clubId);

        $this->assertCount(3, $ativas);
        $this->assertFalse($ativas->contains('key', 'pontual'));
    }

    public function test_get_columns_for_management_inclui_inativas(): void
    {
        $this->service->ensureFixedColumns($this->clubId);
        AttendanceColumn::where('club_id', $this->clubId)->where('key', 'pontual')->update(['is_active' => false]);

        $todas = $this->service->getColumnsForManagement($this->clubId);

        $this->assertCount(4, $todas);
    }
}
