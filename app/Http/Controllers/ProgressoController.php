<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Classe;
use App\Models\Requisito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressoController extends Controller
{
    /**
     * Exibe a página de gestão de classes do desbravador.
     */
    public function index(Desbravador $desbravador)
    {
        // Tenta achar a classe atual pelo nome, ou pega a primeira
        $classeAtual = Classe::where('nome', $desbravador->classe_atual)->first()
            ?? Classe::orderBy('ordem')->first();

        $classes = Classe::orderBy('ordem')->get();

        // Carrega requisitos da classe selecionada (ou atual)
        $classeId = request('classe_id', $classeAtual->id);
        $classeSelecionada = Classe::with('requisitos')->find($classeId);

        // Carrega IDs dos requisitos já cumpridos por este desbravador
        $cumpridosIds = $desbravador->requisitosCumpridos()->pluck('requisitos.id')->toArray();

        return view('desbravadores.progresso', compact(
            'desbravador',
            'classes',
            'classeSelecionada',
            'cumpridosIds'
        ));
    }

    /**
     * Marca ou desmarca um requisito via AJAX ou Form.
     */
    public function toggle(Request $request, Desbravador $desbravador)
    {
        $request->validate([
            'requisito_id' => 'required|exists:requisitos,id'
        ]);

        $reqId = $request->requisito_id;

        // Verifica se já tem
        $existe = $desbravador->requisitosCumpridos()->where('requisito_id', $reqId)->exists();

        if ($existe) {
            $desbravador->requisitosCumpridos()->detach($reqId);
            $msg = 'Requisito desmarcado.';
        } else {
            $desbravador->requisitosCumpridos()->attach($reqId, [
                'user_id' => Auth::id(),
                'data_conclusao' => now()
            ]);
            $msg = 'Requisito concluído!';
        }

        return back()->with('success', $msg);
    }
}
