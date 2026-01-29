<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Caixa;
use App\Models\Unidade;
use App\Models\Frequencia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total de Desbravadores
        $totalMembros = Desbravador::count();

        // 2. Saldo em Caixa (Entradas - Saídas)
        $entradas = Caixa::where('tipo', 'entrada')->sum('valor');
        $saidas = Caixa::where('tipo', 'saida')->sum('valor');
        $saldoAtual = $entradas - $saidas;

        // 3. Aniversariantes do Mês
        $mesAtual = Carbon::now()->month;

        // Busca todos do mês e ordena pela coleção (agnóstico de banco de dados)
        $aniversariantes = Desbravador::whereMonth('data_nascimento', $mesAtual)
            ->get()
            ->sortBy(function ($dbv) {
                return $dbv->data_nascimento->day;
            });

        // 4. Ranking das Unidades (Lógica existente aprimorada)
        $ranking = Unidade::all()->map(function ($unidade) {
            return [
                'nome' => $unidade->nome,
                'pontos' => $unidade->pontuacao_total, // Usa o accessor do Model
                'membros' => $unidade->desbravadores->count()
            ];
        })->sortByDesc('pontos')->values();

        // 5. Dados para Gráfico de Frequência (Últimas 4 reuniões)
        $datasReunioes = Frequencia::select('data')
            ->distinct()
            ->orderBy('data', 'desc')
            ->take(4)
            ->pluck('data')
            ->sort()
            ->values();

        $graficoFrequencia = [];
        $totalAtivos = Desbravador::count(); // Pega o total uma vez para otimizar

        foreach ($datasReunioes as $data) {
            // whereDate garante que ignoramos a hora (00:00:00) na comparação
            $presentes = Frequencia::whereDate('data', $data)->where('presente', true)->count();

            $graficoFrequencia[] = [
                'data' => Carbon::parse($data)->format('d/m'),
                'presentes' => $presentes,
                'percentual' => $totalAtivos > 0 ? round(($presentes / $totalAtivos) * 100) : 0
            ];
        }

        return view('dashboard', compact(
            'totalMembros',
            'saldoAtual',
            'aniversariantes',
            'ranking',
            'graficoFrequencia'
        ));
    }
}
