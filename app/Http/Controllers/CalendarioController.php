<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Frequencia;
use App\Services\ClubContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $clubId = ClubContext::currentClubId();
        abort_unless($clubId, 403);

        $mes = (int) $request->input('mes', now()->month);
        $ano = (int) $request->input('ano', now()->year);

        // Sanitiza para um mês válido — evita Carbon::create lançar com entrada arbitrária.
        $mes = max(1, min(12, $mes));

        $inicio = Carbon::create($ano, $mes, 1)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        // 1. Datas de reuniões (frequência) — o ClubScope já filtra por clube.
        $reunioes = Frequencia::whereBetween('data', [$inicio, $fim])
            ->selectRaw('DATE(data) as data_reuniao')
            ->distinct()
            ->pluck('data_reuniao')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'));

        // 2. Eventos no mês.
        $eventos = Evento::where(function ($q) use ($inicio, $fim) {
            $q->whereBetween('data_inicio', [$inicio, $fim])
                ->orWhereBetween('data_fim', [$inicio, $fim]);
        })->get(['id', 'nome', 'data_inicio', 'data_fim', 'local']);

        // 3. Aniversariantes do mês — ordenados por dia em PHP (DB-agnóstico).
        $aniversariantes = Desbravador::ativos()
            ->whereMonth('data_nascimento', $mes)
            ->get(['id', 'nome', 'data_nascimento', 'unidade_id'])
            ->map(fn ($d) => [
                'dia' => Carbon::parse($d->data_nascimento)->day,
                'nome' => $d->nome,
                'idade' => Carbon::parse($d->data_nascimento)->age,
            ])
            ->sortBy('dia')
            ->values();

        return view('calendario.index', compact(
            'mes', 'ano', 'inicio', 'reunioes', 'eventos', 'aniversariantes'
        ));
    }
}
