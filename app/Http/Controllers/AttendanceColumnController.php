<?php

namespace App\Http\Controllers;

use App\Models\AttendanceColumn;
use App\Services\AttendanceColumnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AttendanceColumnController extends Controller
{
    public function __construct(private readonly AttendanceColumnService $attendanceColumnService)
    {
    }

    public function index()
    {
        Gate::authorize('gerenciar-colunas-chamada');

        $clubId = auth()->user()->club_id;
        if (empty($clubId) || $clubId <= 0) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Usuario sem clube vinculado. Vincule um clube para gerenciar colunas da chamada.');
        }

        $columns = $this->attendanceColumnService->getColumnsForManagement($clubId);
        $legacyMode = $this->attendanceColumnService->usesLegacyColumns();

        if (! $legacyMode && $columns->isNotEmpty()) {
            $columns->loadCount('values');
            $columns = $columns->map(function (AttendanceColumn $column) {
                $column->can_delete = ! $column->is_fixed && ((int) $column->values_count === 0);

                return $column;
            });
        }

        return view('frequencia.columns', compact('columns', 'legacyMode'));
    }

    public function update(Request $request)
    {
        Gate::authorize('gerenciar-colunas-chamada');

        if ($this->attendanceColumnService->usesLegacyColumns()) {
            return redirect()
                ->route('frequencia.columns.index')
                ->with('error', 'Atualizacao pendente: execute as migrations para habilitar a gestao de colunas.');
        }

        $validated = $request->validate([
            'columns' => 'nullable|array',
            'columns.*.name' => 'required|string|max:60',
            'columns.*.points' => 'required|integer|min:1|max:10',
            'new_columns' => 'nullable|array',
            'new_columns.*.name' => 'nullable|string|max:60',
            'new_columns.*.points' => 'nullable|integer|min:1|max:10',
        ]);

        $clubId = auth()->user()->club_id;
        if (empty($clubId) || $clubId <= 0) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Usuario sem clube vinculado. Vincule um clube para gerenciar colunas da chamada.');
        }
        $clubColumns = $this->attendanceColumnService->getColumnsForManagement($clubId)->keyBy('id');

        DB::transaction(function () use ($validated, $clubColumns, $clubId) {
            foreach (($validated['columns'] ?? []) as $columnId => $columnInput) {
                $column = $clubColumns->get((int) $columnId);

                if (! $column) {
                    continue;
                }

                $column->update([
                    'name' => trim((string) $columnInput['name']),
                    'points' => (int) $columnInput['points'],
                ]);
            }

            $nextSortOrder = (int) AttendanceColumn::where('club_id', $clubId)->max('sort_order');
            if ($nextSortOrder < 100) {
                $nextSortOrder = 100;
            }

            foreach (($validated['new_columns'] ?? []) as $newColumn) {
                $name = trim((string) ($newColumn['name'] ?? ''));
                $points = $newColumn['points'] ?? null;

                if ($name === '' || $points === null) {
                    continue;
                }

                $nextSortOrder += 10;

                AttendanceColumn::create([
                    'club_id' => $clubId,
                    'key' => null,
                    'name' => $name,
                    'points' => (int) $points,
                    'is_fixed' => false,
                    'is_active' => true,
                    'sort_order' => $nextSortOrder,
                ]);
            }
        });

        return redirect()
            ->route('frequencia.columns.index')
            ->with('success', 'Colunas da chamada atualizadas com sucesso.');
    }

    public function destroy(AttendanceColumn $attendanceColumn)
    {
        Gate::authorize('gerenciar-colunas-chamada');

        if ($this->attendanceColumnService->usesLegacyColumns()) {
            return redirect()
                ->route('frequencia.columns.index')
                ->with('error', 'Atualizacao pendente: execute as migrations para habilitar a gestao de colunas.');
        }

        $clubId = auth()->user()->club_id;
        if (empty($clubId) || $clubId <= 0) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Usuario sem clube vinculado. Vincule um clube para gerenciar colunas da chamada.');
        }
        if ((int) $attendanceColumn->club_id !== $clubId) {
            abort(403);
        }

        if ($attendanceColumn->is_fixed) {
            return redirect()
                ->route('frequencia.columns.index')
                ->with('error', 'Colunas fixas nao podem ser removidas.');
        }

        $used = $attendanceColumn->values()->exists();
        if ($used) {
            return redirect()
                ->route('frequencia.columns.index')
                ->with('error', 'Essa coluna nao pode ser removida porque ja foi usada em chamada.');
        }

        $attendanceColumn->delete();

        return redirect()
            ->route('frequencia.columns.index')
            ->with('success', 'Coluna removida com sucesso.');
    }
}
