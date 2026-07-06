<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventoRequest;
use App\Http\Requests\UpdateEventoRequest;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Services\ClubContext;
use App\Services\InscricaoEventoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EventoController extends Controller
{
    public function __construct(private readonly InscricaoEventoService $inscricoes) {}

    // ... index, create, store, edit, update (MANTIDOS IGUAIS) ...
    public function index()
    {
        Gate::authorize('eventos');

        $eventos = Evento::withCount('desbravadores')->orderBy('data_inicio', 'desc')->paginate(9)->withQueryString();

        return view('eventos.index', compact('eventos'));
    }

    public function create()
    {
        Gate::authorize('secretaria');

        return view('eventos.create');
    }

    public function store(StoreEventoRequest $request)
    {
        Gate::authorize('secretaria');

        $dados = $request->validated();
        $dados['club_id'] = ClubContext::currentClubId();
        Evento::create($dados);

        return redirect()->route('eventos.index')->with('success', 'Evento criado!');
    }

    public function show(Evento $evento)
    {
        Gate::authorize('eventos');

        $evento->load(['desbravadores.unidade']);

        // CORREÇÃO: Carrega quem NÃO está inscrito para o select
        $inscritosIds = $evento->desbravadores->pluck('id');
        $naoInscritos = Desbravador::where('ativo', true)
            ->whereNotIn('id', $inscritosIds)
            ->orderBy('nome')
            ->get();

        return view('eventos.show', compact('evento', 'naoInscritos'));
    }

    public function edit(Evento $evento)
    {
        Gate::authorize('secretaria');

        return view('eventos.edit', compact('evento'));
    }

    public function update(UpdateEventoRequest $request, Evento $evento)
    {
        Gate::authorize('secretaria');

        $evento->update($request->validated());

        // ALTERADO: Redireciona para a tela de visualização (show) do evento
        return redirect()->route('eventos.show', $evento->id)->with('success', 'Evento atualizado!');
    }

    public function destroy(Evento $evento)
    {
        Gate::authorize('secretaria');

        if ($evento->desbravadores()->count() > 0) {
            return back()->with('error', 'Não é possível excluir evento com inscritos. Remova as inscrições primeiro.');
        }
        $evento->delete();

        return redirect()->route('eventos.index')->with('success', 'Evento removido.');
    }

    // --- MÉTODOS DE INSCRIÇÃO ---

    public function inscrever(Request $request, Evento $evento)
    {
        Gate::authorize('eventos');

        // exists escopado por club_id: a regra exists pura ignora o global scope
        // e aceitaria um desbravador de outro clube (vazamento cross-tenant).
        $request->validate([
            'desbravador_id' => [
                'required',
                Rule::exists('desbravadores', 'id')->where('club_id', ClubContext::currentClubId()),
            ],
        ]);

        if (! $evento->desbravadores()->where('desbravador_id', $request->desbravador_id)->exists()) {
            $evento->desbravadores()->attach($request->desbravador_id, ['pago' => false, 'autorizacao_entregue' => false]);

            return back()->with('success', 'Inscrito com sucesso!');
        }

        return back()->with('info', 'Já estava inscrito.');
    }

    // NOVO: Inscrição em Lote
    public function inscreverEmLote(Request $request, Evento $evento)
    {
        Gate::authorize('eventos');

        $request->validate(['desbravadores' => 'required|array']);

        // Filtra os IDs recebidos pelo global scope do model (tenant ativo) antes
        // de inscrever — descarta IDs de outros clubes sem confiar no input.
        $idsDoClube = Desbravador::whereIn('id', $request->desbravadores)->pluck('id');

        $count = 0;
        foreach ($idsDoClube as $id) {
            if (! $evento->desbravadores()->where('desbravador_id', $id)->exists()) {
                $evento->desbravadores()->attach($id, ['pago' => false, 'autorizacao_entregue' => false]);
                $count++;
            }
        }

        return back()->with('success', "$count desbravadores inscritos!");
    }

    public function removerInscricao(Evento $evento, Desbravador $desbravador)
    {
        Gate::authorize('eventos');

        // Remover um inscrito pago estorna dinheiro do caixa: exige permissão financeira.
        if ($this->inscricoes->removerExigeFinanceiro($evento, $desbravador)) {
            Gate::authorize('financeiro');
        }

        $this->inscricoes->remover($evento, $desbravador);

        return back()->with('success', 'Removido do evento.');
    }

    // AJAX + Integração com Caixa (via InscricaoEventoService)
    public function atualizarStatus(Request $request, Evento $evento, Desbravador $desbravador)
    {
        Gate::authorize('financeiro');

        $campo = $request->campo;
        $valor = filter_var($request->valor, FILTER_VALIDATE_BOOLEAN);

        if ($campo === 'pago') {
            $resultado = $this->inscricoes->definirPago($evento, $desbravador, $valor);

            if ($resultado === null) {
                return response()->json(['error' => 'Inscrição não encontrada para este desbravador.'], 404);
            }

            return response()->json([
                'success' => true,
                'novo_status' => $resultado['novo_status'],
                'status_alterado' => $resultado['status_alterado'],
                'movimentacao_registrada' => $resultado['movimentacao_registrada'],
            ]);
        }

        if ($campo === 'autorizacao_entregue') {
            $evento->desbravadores()->updateExistingPivot($desbravador->id, ['autorizacao_entregue' => $valor]);

            return response()->json(['success' => true, 'novo_status' => $valor]);
        }

        return response()->json(['error' => 'Campo inválido'], 400);
    }

    public function gerarAutorizacao(Evento $evento, Desbravador $desbravador)
    {
        Gate::authorize('eventos');

        // Só emite autorização para quem está de fato inscrito no evento (ambos os
        // models são tenant-scoped → 404 cross-tenant; aqui garantimos o vínculo).
        abort_unless(
            $evento->desbravadores()->where('desbravador_id', $desbravador->id)->exists(),
            404,
            'Este desbravador não está inscrito no evento.'
        );

        $pdf = Pdf::loadView('relatorios.autorizacao', [
            'desbravador' => $desbravador,
            'evento' => $evento,
        ]);

        return $pdf->stream('autorizacao.pdf');
    }
}
