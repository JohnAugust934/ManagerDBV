<?php

namespace Tests\Feature;

use App\Models\AttendanceColumn;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\FrequenciaColumnValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gestão de colunas da chamada (AttendanceColumnController): criação/edição,
 * proteção das colunas fixas, guarda contra exclusão de coluna já usada e
 * isolamento por club_id.
 */
class AttendanceColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lista_colunas_fixas_padrao(): void
    {
        ['master' => $master] = criarClubeComDados('Clube A');

        $this->actingAs($master)->get(route('frequencia.columns.index'))
            ->assertOk()
            ->assertViewHas('columns');

        // ensureFixedColumns criou as 4 colunas fixas padrão.
        $this->assertSame(4, AttendanceColumn::where('is_fixed', true)->count());
    }

    public function test_update_renomeia_coluna_e_cria_nova(): void
    {
        ['club' => $club, 'master' => $master] = criarClubeComDados('Clube A');
        $this->actingAs($master)->get(route('frequencia.columns.index')); // dispara ensureFixedColumns

        $presente = AttendanceColumn::where('club_id', $club->id)->where('key', 'presente')->firstOrFail();

        $this->actingAs($master)->put(route('frequencia.columns.update'), [
            'columns' => [
                $presente->id => ['name' => 'Presença', 'points' => 8],
            ],
            'new_columns' => [
                ['name' => 'Versículo', 'points' => 3],
            ],
        ])->assertRedirect(route('frequencia.columns.index'));

        $this->assertDatabaseHas('attendance_columns', ['id' => $presente->id, 'name' => 'Presença', 'points' => 8]);
        $this->assertDatabaseHas('attendance_columns', ['club_id' => $club->id, 'name' => 'Versículo', 'points' => 3, 'is_fixed' => false]);
    }

    public function test_nao_remove_coluna_fixa(): void
    {
        ['club' => $club, 'master' => $master] = criarClubeComDados('Clube A');
        $this->actingAs($master)->get(route('frequencia.columns.index'));

        $fixa = AttendanceColumn::where('club_id', $club->id)->where('key', 'presente')->firstOrFail();

        $this->actingAs($master)->delete(route('frequencia.columns.destroy', $fixa))
            ->assertRedirect(route('frequencia.columns.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('attendance_columns', ['id' => $fixa->id]);
    }

    public function test_remove_coluna_customizada_nao_usada(): void
    {
        ['club' => $club, 'master' => $master] = criarClubeComDados('Clube A');

        $custom = AttendanceColumn::create(['club_id' => $club->id, 'key' => null, 'name' => 'Extra', 'points' => 4, 'is_fixed' => false, 'is_active' => true, 'sort_order' => 110]);

        $this->actingAs($master)->delete(route('frequencia.columns.destroy', $custom))
            ->assertRedirect(route('frequencia.columns.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('attendance_columns', ['id' => $custom->id]);
    }

    public function test_nao_remove_coluna_ja_usada_em_chamada(): void
    {
        ['club' => $club, 'master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');

        $custom = AttendanceColumn::create(['club_id' => $club->id, 'key' => null, 'name' => 'Extra', 'points' => 4, 'is_fixed' => false, 'is_active' => true, 'sort_order' => 110]);
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);
        $freq = Frequencia::create(['desbravador_id' => $dbv->id, 'data' => now()]);
        FrequenciaColumnValue::create(['frequencia_id' => $freq->id, 'attendance_column_id' => $custom->id, 'checked' => true, 'points_awarded' => 4]);

        $this->actingAs($master)->delete(route('frequencia.columns.destroy', $custom))
            ->assertRedirect(route('frequencia.columns.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('attendance_columns', ['id' => $custom->id]);
    }

    public function test_nao_remove_coluna_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $colunaB = AttendanceColumn::create(['club_id' => $clubB->id, 'key' => null, 'name' => 'Alheia', 'points' => 4, 'is_fixed' => false, 'is_active' => true, 'sort_order' => 110]);

        // O global scope de tenant esconde a coluna de outro clube no model
        // binding → 404 (fail-closed), nunca expõe o recurso alheio.
        $this->actingAs($masterA)->delete(route('frequencia.columns.destroy', $colunaB))
            ->assertNotFound();

        $this->assertDatabaseHas('attendance_columns', ['id' => $colunaB->id]);
    }
}
