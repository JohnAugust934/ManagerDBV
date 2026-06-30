<?php

namespace App\Http\Controllers;

use App\Mail\ComunicadoResponsavel;
use App\Models\Club;
use App\Models\Comunicado;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Rules\UnidadePertenceAoClube;
use App\Services\ClubContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class ComunicadoController extends Controller
{
    public function index()
    {
        Gate::authorize('secretaria');

        $comunicados = Comunicado::with('unidade')->latest()->paginate(15)->withQueryString();

        return view('comunicados.index', compact('comunicados'));
    }

    public function create()
    {
        Gate::authorize('secretaria');

        $unidades = Unidade::where('club_id', ClubContext::currentClubId())->orderBy('nome')->get(['id', 'nome']);

        return view('comunicados.create', compact('unidades'));
    }

    public function store(Request $request)
    {
        Gate::authorize('secretaria');

        $dados = $request->validate([
            'titulo' => 'required|string|max:255',
            'corpo' => 'required|string|max:5000',
            'destinatarios' => 'required|in:todos,ativos,unidade',
            'unidade_id' => ['nullable', 'required_if:destinatarios,unidade', new UnidadePertenceAoClube],
        ]);

        $clubId = ClubContext::currentClubId();
        abort_unless($clubId, 403);

        $comunicado = Comunicado::create([
            'club_id' => $clubId,
            'criado_por' => auth()->id(),
            'titulo' => $dados['titulo'],
            'corpo' => $dados['corpo'],
            'destinatarios' => $dados['destinatarios'],
            'unidade_id' => $dados['destinatarios'] === 'unidade' ? $dados['unidade_id'] : null,
        ]);

        // Destinatários com e-mail (o ClubScope já restringe ao clube ativo).
        $query = Desbravador::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('ativo', true);

        if ($dados['destinatarios'] === 'unidade') {
            $query->where('unidade_id', $dados['unidade_id']);
        }

        $desbravadores = $query->get(['id', 'nome', 'email', 'nome_responsavel']);
        $clube = Club::find($clubId);
        $enviados = 0;

        foreach ($desbravadores as $desb) {
            try {
                Mail::to($desb->email)->send(new ComunicadoResponsavel($comunicado, $desb->nome, $clube->nome));
                $enviados++;
            } catch (\Throwable) {
                // Falhas individuais não interrompem o lote.
            }
        }

        $comunicado->update(['total_enviados' => $enviados, 'enviado_em' => now()]);

        return redirect()->route('comunicados.index')
            ->with('success', "Comunicado enviado para {$enviados} destinatário(s).");
    }

    public function show(Comunicado $comunicado)
    {
        Gate::authorize('secretaria');

        return view('comunicados.show', compact('comunicado'));
    }
}
