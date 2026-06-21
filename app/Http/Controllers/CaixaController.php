<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CaixaAuditLog;
use App\Services\ClubContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CaixaController extends Controller
{
    public function index()
    {
        Gate::authorize('financeiro');

        $query = Caixa::query();

        $entradas = (clone $query)->where('tipo', 'entrada')->sum('valor');
        $saidas = (clone $query)->where('tipo', 'saida')->sum('valor');
        $saldoAtual = $entradas - $saidas;

        $lancamentos = $query->orderBy('data_movimentacao', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $auditLogs = CaixaAuditLog::where('club_id', ClubContext::currentClubId())
            ->with('usuario')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('financeiro.caixa.index', compact('lancamentos', 'saldoAtual', 'entradas', 'saidas', 'auditLogs'));
    }

    public function create()
    {
        Gate::authorize('financeiro');

        return view('financeiro.caixa.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('financeiro');

        $validado = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'tipo' => 'required|in:entrada,saida',
            'data_movimentacao' => 'required|date',
            'categoria' => 'nullable|string|max:100',
        ]);

        $validado['club_id'] = ClubContext::currentClubId();

        $caixa = \Illuminate\Support\Facades\DB::retryOnDeadlock(fn () => Caixa::create($validado));

        CaixaAuditLog::registrar('criado', $caixa, null, $this->dadosAuditaveis($caixa));

        return redirect()->route('caixa.index')
            ->with('success', 'Movimentação registrada com sucesso!');
    }

    public function edit(Caixa $caixa)
    {
        Gate::authorize('financeiro');

        return view('financeiro.caixa.edit', compact('caixa'));
    }

    public function update(Request $request, Caixa $caixa)
    {
        Gate::authorize('financeiro');

        $validado = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'tipo' => 'required|in:entrada,saida',
            'data_movimentacao' => 'required|date',
            'categoria' => 'nullable|string|max:100',
        ]);

        $antes = $this->dadosAuditaveis($caixa);

        \Illuminate\Support\Facades\DB::retryOnDeadlock(fn () => $caixa->update($validado));

        CaixaAuditLog::registrar('editado', $caixa, $antes, $this->dadosAuditaveis($caixa));

        return redirect()->route('caixa.index')
            ->with('success', 'Lançamento atualizado com sucesso!');
    }

    public function destroy(Caixa $caixa)
    {
        Gate::authorize('financeiro');

        $antes = $this->dadosAuditaveis($caixa);

        // Mantém referência ao club_id antes de deletar para salvar no log.
        $clubId = $caixa->club_id;
        $caixaId = $caixa->id;

        $caixa->delete();

        // Cria o log manualmente pois o model foi deletado.
        CaixaAuditLog::create([
            'caixa_id' => $caixaId,
            'club_id' => $clubId,
            'user_id' => auth()->id(),
            'acao' => 'excluido',
            'dados_antes' => $antes,
            'dados_depois' => null,
            'created_at' => now(),
        ]);

        return redirect()->route('caixa.index')
            ->with('success', 'Lançamento excluído com sucesso.');
    }

    private function dadosAuditaveis(Caixa $caixa): array
    {
        return [
            'descricao' => $caixa->descricao,
            'valor' => (string) $caixa->valor,
            'tipo' => $caixa->tipo,
            'categoria' => $caixa->categoria,
            'data_movimentacao' => $caixa->data_movimentacao?->format('Y-m-d'),
        ];
    }
}
