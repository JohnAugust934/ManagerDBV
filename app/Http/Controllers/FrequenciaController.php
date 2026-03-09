<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Unidade;
use Illuminate\Http\Request;

class FrequenciaController extends Controller
{
    // Tela de Histórico Mensal
    public function index(Request $request)
    {
        // Define Mês e Ano (Pega do request ou usa o atual)
        $mes = $request->input('mes', now()->month);
        $ano = $request->input('ano', now()->year);

        // Busca todas as datas que tiveram reunião neste mês/ano
        $datasReunioes = Frequencia::whereYear('data', $ano)
            ->whereMonth('data', $mes)
            ->selectRaw('DATE(data) as data_reuniao')
            ->distinct()
            ->orderBy('data_reuniao')
            ->pluck('data_reuniao');

        // Busca Desbravadores ativos com suas frequências filtradas pelo mês/ano
        // CORREÇÃO: Verifica o clube através do relacionamento com a unidade
        $desbravadores = Desbravador::with(['unidade', 'frequencias' => function ($query) use ($mes, $ano) {
            $query->whereYear('data', $ano)->whereMonth('data', $mes);
        }])
            ->whereHas('unidade', function ($query) {
                $query->where('club_id', auth()->user()->club_id); // Segurança
            })
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('frequencia.index', compact('desbravadores', 'datasReunioes', 'mes', 'ano'));
    }

    public function create()
    {
        // Busca todas as unidades do clube com seus desbravadores ativos
        $unidades = Unidade::with(['desbravadores' => function ($q) {
            $q->where('ativo', true)->orderBy('nome');
        }])
            ->where('club_id', auth()->user()->club_id) // Segurança: Apenas unidades do clube
            ->get();

        return view('frequencia.create', compact('unidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|date',
            'presencas' => 'required|array',
        ]);

        foreach ($request->presencas as $id => $dados) {
            // CORREÇÃO: Carrega a unidade junto para checar a qual clube o desbravador pertence
            $dbv = Desbravador::with('unidade')->find($id);

            // Ignora se não achou o desbravador ou se a unidade dele for de outro clube
            if (! $dbv || $dbv->unidade->club_id !== auth()->user()->club_id) {
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
