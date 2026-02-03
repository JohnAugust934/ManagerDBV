<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FrequenciaController extends Controller
{
    // NOVO: Tela de Histórico Mensal
    public function index(Request $request)
    {
        // Define Mês e Ano (Pega do request ou usa o atual)
        $mes = $request->input('mes', now()->month);
        $ano = $request->input('ano', now()->year);

        // Busca todas as datas que tiveram reunião neste mês/ano (para montar o cabeçalho da tabela)
        // Isso evita mostrar colunas de dias que não houve clube.
        $datasReunioes = Frequencia::whereYear('data', $ano)
            ->whereMonth('data', $mes)
            ->selectRaw('DATE(data) as data_reuniao')
            ->distinct()
            ->orderBy('data_reuniao')
            ->pluck('data_reuniao');

        // Busca Desbravadores ativos com suas frequências filtradas pelo mês/ano
        $desbravadores = Desbravador::with(['unidade', 'frequencias' => function ($query) use ($mes, $ano) {
            $query->whereYear('data', $ano)->whereMonth('data', $mes);
        }])
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('frequencia.index', compact('desbravadores', 'datasReunioes', 'mes', 'ano'));
    }

    public function create()
    {
        $unidades = Unidade::with(['desbravadores' => function ($q) {
            $q->where('ativo', true)->orderBy('nome');
        }])->get()->filter(function ($unidade) {
            return Gate::allows('gerir-unidade', $unidade);
        });

        return view('frequencia.create', compact('unidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|date',
            'presencas' => 'required|array',
        ]);

        foreach ($request->presencas as $id => $dados) {
            $dbv = Desbravador::with('unidade')->find($id);

            if (! $dbv) {
                continue;
            }

            if (Gate::denies('gerir-unidade', $dbv->unidade)) {
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
}
