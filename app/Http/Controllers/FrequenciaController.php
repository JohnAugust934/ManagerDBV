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

        $mes = (int) $request->input('mes', now()->month);
        $ano = (int) $request->input('ano', now()->year);

        if ($mes < 1 || $mes > 12) {
            $mes = (int) now()->month;
        }

        if ($ano < 2000 || $ano > 2100) {
            $ano = (int) now()->year;
        }

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
            'presencas' => 'array',
            'unidades_submetidas' => 'required|array',
            'unidades_submetidas.*' => 'integer',
        ]);

        $clubId = auth()->user()->club_id;

        if (empty($clubId) || $clubId <= 0) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Usuario sem clube vinculado. Vincule um clube para registrar chamada.');
        }

        // Apenas processar desbravadores das unidades que foram efetivamente submetidas no formulário.
        // Isso garante que chamadas de outras unidades (feitas por outros usuários no mesmo dia)
        // não sejam sobrescritas.
        $unidadesSubmetidas = array_map('intval', $request->input('unidades_submetidas', []));

        // Desbravadores válidos: pertencentes às unidades submetidas e ao clube do usuário
        $desbravadoresValidos = Desbravador::with('unidade')
            ->whereHas('unidade', function ($query) use ($clubId, $unidadesSubmetidas) {
                $query->where('club_id', $clubId)
                      ->whereIn('id', $unidadesSubmetidas);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->all(); // array [id => index] para lookup O(1)

        $presencas = $request->input('presencas', []);

        if ($this->attendanceColumnService->usesLegacyColumns()) {
            foreach ($presencas as $id => $dados) {
                $id = (int) $id;

                // Ignorar desbravadores que não pertencem às unidades submetidas
                if (! array_key_exists($id, $desbravadoresValidos)) {
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

            // Desbravadores das unidades submetidas que não vieram no POST (nenhuma checkbox marcada)
            // devem ter a frequência zerada para aquela data
            $idsEnviados = array_map('intval', array_keys($presencas));
            $idsAusentesNaoEnviados = array_diff(array_keys($desbravadoresValidos), $idsEnviados);

            foreach ($idsAusentesNaoEnviados as $id) {
                Frequencia::updateOrCreate(
                    ['desbravador_id' => $id, 'data' => $request->data],
                    ['presente' => false, 'pontual' => false, 'biblia' => false, 'uniforme' => false]
                );
            }

            return redirect()->route('frequencia.index')->with('success', 'Chamada realizada com sucesso!');
        }

        $columns = $this->attendanceColumnService->getActiveColumnsForClub($clubId)->keyBy('id');
        $fixedColumns = $columns->where('is_fixed', true)->keyBy('key');

        DB::transaction(function () use ($request, $clubId, $columns, $fixedColumns, $desbravadoresValidos, $presencas) {
            foreach ($presencas as $id => $dados) {
                $id = (int) $id;

                // Ignorar desbravadores que não pertencem às unidades submetidas
                if (! array_key_exists($id, $desbravadoresValidos)) {
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

            // Desbravadores das unidades submetidas sem nenhuma checkbox marcada = ausentes
            $idsEnviados = array_map('intval', array_keys($presencas));
            $idsAusentesNaoEnviados = array_diff(array_keys($desbravadoresValidos), $idsEnviados);

            foreach ($idsAusentesNaoEnviados as $id) {
                $frequencia = Frequencia::updateOrCreate(
                    ['desbravador_id' => $id, 'data' => $request->data],
                    ['presente' => false, 'pontual' => false, 'biblia' => false, 'uniforme' => false]
                );

                foreach ($columns as $columnId => $column) {
                    $frequencia->columnValues()->updateOrCreate(
                        ['attendance_column_id' => $column->id],
                        ['checked' => false, 'points_awarded' => 0]
                    );
                }
            }
        });

        return redirect()->route('frequencia.index')->with('success', 'Chamada realizada com sucesso!');
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
