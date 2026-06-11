<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDesbravadorRequest;
use App\Http\Requests\UpdateDesbravadorRequest;
use App\Models\Classe;
use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesbravadorController extends Controller
{
    public function index(Request $request)
    {
        // GlobalScope DesbravadorClubScope aplica o filtro de clube automaticamente.
        $query = Desbravador::with(['unidade', 'classe'])->orderBy('nome');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $normalizedSearch = mb_strtolower($search, 'UTF-8');
            $searchPattern = "%{$normalizedSearch}%";

            $query->where(function ($q) use ($searchPattern) {
                $q->whereRaw('LOWER(nome) LIKE ?', [$searchPattern])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$searchPattern])
                    ->orWhereRaw('LOWER(cpf) LIKE ?', [$searchPattern]);
            });
        }

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        $status = $request->input('status', 'ativos');
        if ($status === 'ativos') {
            $query->where('ativo', true);
        } elseif ($status === 'inativos') {
            $query->where('ativo', false);
        }

        $desbravadores = $query->paginate(10);

        return view('desbravadores.index', compact('desbravadores', 'status'));
    }

    public function create()
    {
        // Mostra apenas unidades do clube do usuário.
        $unidades = Unidade::where('club_id', auth()->user()->club_id)->orderBy('nome')->get();
        $classes = Classe::orderBy('ordem')->get();

        return view('desbravadores.create', compact('unidades', 'classes'));
    }

    public function store(StoreDesbravadorRequest $request)
    {
        $dados = $request->validated();

        $dados['ativo'] = true;
        unset($dados['foto']); // UploadedFile não pode ir para create(); tratado abaixo

        $desbravador = Desbravador::create($dados);

        if ($request->hasFile('foto')) {
            $desbravador->update(['foto' => $this->processarFoto($request->file('foto'))]);
        }

        return redirect()->route('desbravadores.index')->with('success', 'Desbravador cadastrado com sucesso!');
    }

    public function show(Desbravador $desbravador)
    {
        // GlobalScope garante que apenas desbravadores do clube são acessíveis.
        $desbravador->load([
            'unidade',
            'classe',
            'criadoPor:id,name',
            'atualizadoPor:id,name',
            'especialidades',
            'frequencias' => function ($q) {
                $q->orderBy('data', 'desc')->take(5);
            },
            'mensalidades' => function ($q) {
                $q->orderByDesc('ano')->orderByDesc('mes');
            },
            'eventos' => function ($q) {
                $q->orderByDesc('data_inicio');
            },
        ]);

        return view('desbravadores.show', compact('desbravador'));
    }

    public function edit(Desbravador $desbravador)
    {
        $unidades = Unidade::where('club_id', auth()->user()->club_id)->orderBy('nome')->get();
        $classes = Classe::orderBy('ordem')->get();

        return view('desbravadores.edit', compact('desbravador', 'unidades', 'classes'));
    }

    public function update(UpdateDesbravadorRequest $request, Desbravador $desbravador)
    {
        $dados = $request->validated();

        $dados['ativo'] = $request->has('ativo');
        unset($dados['foto']); // foto é tratada separadamente para não sobrescrever com null

        if ($request->hasFile('foto')) {
            if ($desbravador->foto) {
                Storage::disk('public')->delete($desbravador->foto);
            }
            $dados['foto'] = $this->processarFoto($request->file('foto'));
        }

        $desbravador->update($dados);

        return redirect()->route('desbravadores.show', $desbravador)->with('success', 'Dados atualizados!');
    }

    public function destroy(Desbravador $desbravador)
    {
        DB::transaction(function () use ($desbravador) {
            $desbravador->delete();
        });

        return redirect()
            ->route('desbravadores.index')
            ->with('success', 'Desbravador excluído com sucesso. Todos os dados vinculados foram removidos.');
    }

    public function avancarClasse(Desbravador $desbravador)
    {
        $desbravador->load('classe');

        if (! $desbravador->classe) {
            return back()->with('error', 'Este desbravador não possui classe atual definida.');
        }

        $proximaClasse = Classe::where('ordem', '>', $desbravador->classe->ordem)
            ->orderBy('ordem')
            ->first();

        if (! $proximaClasse) {
            return back()->with('error', 'Este desbravador já está na classe mais avançada.');
        }

        $desbravador->update(['classe_atual' => $proximaClasse->id]);

        return back()->with('success', "Classe avançada para {$proximaClasse->nome} com sucesso!");
    }

    public function removerFoto(Desbravador $desbravador)
    {
        if ($desbravador->foto) {
            Storage::disk('public')->delete($desbravador->foto);
            $desbravador->update(['foto' => null]);
        }

        return back()->with('success', 'Foto removida com sucesso.');
    }

    private function processarFoto(\Illuminate\Http\UploadedFile $arquivo): string
    {
        $imagem = match ($arquivo->getMimeType()) {
            'image/png' => imagecreatefrompng($arquivo->getPathname()),
            'image/webp' => imagecreatefromwebp($arquivo->getPathname()),
            default => imagecreatefromjpeg($arquivo->getPathname()),
        };

        $larguraOriginal = imagesx($imagem);
        $alturaOriginal = imagesy($imagem);

        $max = 400;
        if ($larguraOriginal > $max || $alturaOriginal > $max) {
            $ratio = min($max / $larguraOriginal, $max / $alturaOriginal);
            $novaLargura = (int) round($larguraOriginal * $ratio);
            $novaAltura = (int) round($alturaOriginal * $ratio);

            $redimensionada = imagecreatetruecolor($novaLargura, $novaAltura);
            imagecopyresampled($redimensionada, $imagem, 0, 0, 0, 0, $novaLargura, $novaAltura, $larguraOriginal, $alturaOriginal);
            imagedestroy($imagem);
            $imagem = $redimensionada;
        }

        $caminho = 'fotos/'.Str::uuid().'.jpg';
        $destino = Storage::disk('public')->path($caminho);

        Storage::disk('public')->makeDirectory('fotos');
        imagejpeg($imagem, $destino, 85);
        imagedestroy($imagem);

        return $caminho;
    }

    public function gerenciarEspecialidades(Desbravador $desbravador)
    {
        $especialidades = Especialidade::orderBy('nome')->get();

        return view('desbravadores.especialidades', compact('desbravador', 'especialidades'));
    }

    public function salvarEspecialidades(Request $request, Desbravador $desbravador)
    {
        $request->validate([
            'especialidades' => 'array',
            'especialidades.*' => 'exists:especialidades,id',
            'data_conclusao' => 'required|date',
        ]);

        if ($request->has('especialidades')) {
            $syncData = [];
            foreach ($request->especialidades as $espId) {
                $syncData[$espId] = ['data_conclusao' => $request->data_conclusao];
            }
            $desbravador->especialidades()->syncWithoutDetaching($syncData);

            return back()->with('success', 'Especialidades adicionadas com sucesso!');
        }

        return back()->with('warning', 'Nenhuma especialidade selecionada.');
    }

    public function removerEspecialidade(Desbravador $desbravador, $especialidadeId)
    {
        $desbravador->especialidades()->detach($especialidadeId);

        return back()->with('success', 'Especialidade removida.');
    }
}
