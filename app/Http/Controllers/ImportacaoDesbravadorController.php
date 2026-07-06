<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Unidade;
use App\Rules\UnidadePertenceAoClube;
use App\Services\ClubContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class ImportacaoDesbravadorController extends Controller
{
    // Campos aceitos no CSV (chave = coluna esperada no cabeçalho, valor = label).
    private const CAMPOS = [
        'nome' => 'Nome completo *',
        'data_nascimento' => 'Data de nascimento (DD/MM/AAAA) *',
        'sexo' => 'Sexo (M/F) *',
        'email' => 'E-mail',
        'telefone' => 'Telefone',
        'nome_responsavel' => 'Nome do responsável',
        'telefone_responsavel' => 'Telefone do responsável',
    ];

    public function index()
    {
        Gate::authorize('secretaria');

        $unidades = Unidade::where('club_id', ClubContext::currentClubId())
            ->orderBy('nome')->get(['id', 'nome']);

        return view('desbravadores.importar', ['unidades' => $unidades, 'campos' => self::CAMPOS]);
    }

    /**
     * Recebe o CSV, valida linha a linha e devolve um preview — não persiste nada.
     */
    public function preview(Request $request)
    {
        Gate::authorize('secretaria');

        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048',
            'unidade_id' => ['required', new UnidadePertenceAoClube],
        ]);

        $linhas = $this->lerCsv($request->file('arquivo'));

        if (count($linhas) < 2) {
            return back()->withErrors(['arquivo' => 'O arquivo CSV parece vazio ou sem cabeçalho.']);
        }

        $cabecalho = array_map('trim', $linhas[0]);
        $dados = array_slice($linhas, 1);

        $preview = [];
        $erros = [];

        foreach ($dados as $i => $linha) {
            if (count($linha) !== count($cabecalho)) {
                continue;
            }
            $row = array_combine($cabecalho, $linha);

            // sexo é obrigatório: a coluna é NOT NULL (M/F). Normaliza antes de validar.
            $row['sexo'] = strtoupper(trim($row['sexo'] ?? ''));

            $v = Validator::make($row, [
                'nome' => 'required|string|min:3',
                'data_nascimento' => ['required', 'regex:/^\d{2}\/\d{2}\/\d{4}$/'],
                'sexo' => 'required|in:M,F',
            ], [
                'sexo.required' => 'sexo é obrigatório (M ou F)',
                'sexo.in' => 'sexo deve ser M ou F',
            ]);

            if ($v->fails()) {
                $erros[] = 'Linha '.($i + 2).': '.implode(', ', $v->errors()->all());

                continue;
            }

            $existente = Desbravador::where('nome', $row['nome'] ?? '')->exists();

            $preview[] = [
                'nome' => $row['nome'] ?? '',
                'data_nascimento' => $row['data_nascimento'] ?? '',
                'sexo' => $row['sexo'] ?? '',
                'email' => $row['email'] ?? '',
                'nome_responsavel' => $row['nome_responsavel'] ?? '',
                'duplicado' => $existente,
            ];
        }

        session(['importacao_csv' => [
            'dados' => $preview,
            'unidade_id' => $request->unidade_id,
            'timestamp' => now()->timestamp,
        ]]);

        return view('desbravadores.importar-preview', compact('preview', 'erros'));
    }

    /**
     * Confirma e persiste os dados guardados na sessão pelo preview.
     */
    public function confirmar(Request $request)
    {
        Gate::authorize('secretaria');

        // Consentimento LGPD não é presumido: o secretário precisa atestar
        // explicitamente que possui o consentimento dos responsáveis. Sem isso,
        // nada é persistido.
        $request->validate([
            'confirmo_consentimento' => 'accepted',
        ], [
            'confirmo_consentimento.accepted' => 'É necessário confirmar que há consentimento LGPD dos responsáveis para importar.',
        ]);

        $sessao = session('importacao_csv');
        abort_if(! $sessao || (now()->timestamp - $sessao['timestamp']) > 1800, 422, 'Sessão de importação expirada.');

        $clubId = ClubContext::currentClubId();
        abort_unless($clubId, 403);

        // A unidade precisa pertencer ao clube ativo — evita injeção de unidade alheia.
        $unidadeValida = Unidade::where('id', $sessao['unidade_id'])->exists();
        abort_unless($unidadeValida, 422, 'Unidade inválida para o clube atual.');

        $importados = 0;
        $pulados = 0;

        foreach ($sessao['dados'] as $linha) {
            if ($linha['duplicado'] && ! $request->boolean('substituir_duplicados')) {
                $pulados++;

                continue;
            }

            try {
                $dataNasc = \Carbon\Carbon::createFromFormat('d/m/Y', $linha['data_nascimento'])->format('Y-m-d');

                Desbravador::create([
                    'nome' => $linha['nome'],
                    'data_nascimento' => $dataNasc,
                    'sexo' => in_array(strtoupper($linha['sexo'] ?? ''), ['M', 'F'], true) ? strtoupper($linha['sexo']) : null,
                    'email' => $linha['email'] ?: null,
                    'nome_responsavel' => $linha['nome_responsavel'] ?: null,
                    'unidade_id' => $sessao['unidade_id'],
                    'club_id' => $clubId,
                    'ativo' => true,
                    'consentimento_lgpd' => true,
                    'consentimento_lgpd_em' => now(),
                ]);
                $importados++;
            } catch (\Throwable) {
                $pulados++;
            }
        }

        session()->forget('importacao_csv');

        return redirect()->route('desbravadores.index')
            ->with('success', "{$importados} desbravadores importados com sucesso. {$pulados} pulados.");
    }

    private function lerCsv(\Illuminate\Http\UploadedFile $file): array
    {
        $linhas = [];
        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            while (($linha = fgetcsv($handle, 1000, ';')) !== false) {
                // Fallback para vírgula quando o arquivo não usa ponto-e-vírgula.
                if (count($linha) === 1) {
                    $linha = str_getcsv($linha[0], ',');
                }
                $linhas[] = array_map(fn ($v) => trim((string) $v), $linha);
            }
            fclose($handle);
        }

        return $linhas;
    }
}
