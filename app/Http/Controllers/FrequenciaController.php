<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Unidade;
use App\Services\AttendanceColumnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrequenciaController extends Controller
{
    public function __construct(private readonly AttendanceColumnService $attendanceColumnService)
    {
    }

    public function index(Request $request)
    {
        $clubId = auth()->user()->club_id;
        if (empty($clubId) || $clubId <= 0) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Usuario sem clube vinculado. Vincule um clube para usar o modulo de frequencia.');
        }

        $mes = $request->input('mes', now()->month);
        $ano = $request->input('ano', now()->year);

        $datasReunioes = Frequencia::whereYear('data', $ano)
            ->whereMonth('data', $mes)
            ->selectRaw('DATE(data) as data_reuniao')
            ->distinct()
            ->orderBy('data_reuniao')
            ->pluck('data_reuniao');

        $desbravadores = Desbravador::with(['unidade', 'frequencias' => function ($query) use ($mes, $ano) {
            $query->whereYear('data', $ano)->whereMonth('data', $mes);
        }])
            ->whereHas('unidade', function ($query) {
                $query->where('club_id', auth()->user()->club_id);
            })
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('frequencia.index', compact('desbravadores', 'datasReunioes', 'mes', 'ano'));
    }

    public function create()
    {
        $clubId = auth()->user()->club_id;

        $unidades = Unidade::with(['desbravadores' => function ($query) {
            $query->where('ativo', true)->orderBy('nome');
        }])
            ->where('club_id', $clubId)
            ->get();

        $columns = $this->attendanceColumnService->getActiveColumnsForClub($clubId);
        $usesLegacyColumns = $this->attendanceColumnService->usesLegacyColumns();

        return view('frequencia.create', compact('unidades', 'columns', 'usesLegacyColumns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|date',
            'presencas' => 'required|array',
        ]);

        $clubId = auth()->user()->club_id;

        if (empty($clubId) || $clubId <= 0) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Usuario sem clube vinculado. Vincule um clube para registrar chamada.');
        }

        if ($this->attendanceColumnService->usesLegacyColumns()) {
            foreach ($request->presencas as $id => $dados) {
                $dbv = Desbravador::with('unidade')->find($id);

                if (! $dbv || ! $dbv->unidade || (int) $dbv->unidade->club_id !== $clubId) {
                    continue;
                }

                Frequencia::updateOrCreate(
                    [
                        'desbravador_id' => $id,
                        'data' => $request->data,
                    ],
                    [
                        'presente' => isset($dados['presente']),
                        'pontual' => isset($dados['pontual']),
                        'biblia' => isset($dados['biblia']),
                        'uniforme' => isset($dados['uniforme']),
                    ]
                );
            }

            return redirect()->route('dashboard')->with('success', 'Chamada realizada com sucesso!');
        }

        $columns = $this->attendanceColumnService->getActiveColumnsForClub($clubId)->keyBy('id');
        $fixedColumns = $columns->where('is_fixed', true)->keyBy('key');

        DB::transaction(function () use ($request, $clubId, $columns, $fixedColumns) {
            foreach ($request->presencas as $id => $dados) {
                $dbv = Desbravador::with('unidade')->find($id);

                if (! $dbv || ! $dbv->unidade || (int) $dbv->unidade->club_id !== $clubId) {
                    continue;
                }

                $selectedColumnIds = collect(array_keys($dados['colunas'] ?? []))
                    ->map(fn ($value) => (int) $value)
                    ->all();

                $frequencia = Frequencia::updateOrCreate(
                    [
                        'desbravador_id' => $id,
                        'data' => $request->data,
                    ],
                    [
                        'presente' => $this->isFixedColumnChecked('presente', $fixedColumns, $selectedColumnIds),
                        'pontual' => $this->isFixedColumnChecked('pontual', $fixedColumns, $selectedColumnIds),
                        'biblia' => $this->isFixedColumnChecked('biblia', $fixedColumns, $selectedColumnIds),
                        'uniforme' => $this->isFixedColumnChecked('uniforme', $fixedColumns, $selectedColumnIds),
                    ]
                );

                foreach ($columns as $columnId => $column) {
                    $checked = in_array((int) $columnId, $selectedColumnIds, true);

                    $frequencia->columnValues()->updateOrCreate(
                        ['attendance_column_id' => $column->id],
                        [
                            'checked' => $checked,
                            'points_awarded' => $checked ? (int) $column->points : 0,
                        ]
                    );
                }
            }
        });

        return redirect()->route('dashboard')->with('success', 'Chamada realizada com sucesso!');
    }

    private function isFixedColumnChecked(string $columnKey, $fixedColumns, array $selectedColumnIds): bool
    {
        $fixedColumn = $fixedColumns->get($columnKey);

        if (! $fixedColumn) {
            return false;
        }

        return in_array((int) $fixedColumn->id, $selectedColumnIds, true);
    }
}
