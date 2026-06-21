<?php

namespace App\Jobs;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\RelatorioGerado;
use App\Models\User;
use App\Services\TelegramNotifier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GerarRelatorioPDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    public function __construct(
        private readonly int $relatorioId,
        private readonly int $clubId,
        private readonly ?int $userId,
    ) {}

    public function handle(): void
    {
        $relatorio = RelatorioGerado::withoutGlobalScopes()->find($this->relatorioId);
        if (! $relatorio) {
            return;
        }

        $relatorio->update(['status' => 'processando']);

        // Autenticar o usuário solicitante para que os GlobalScopes funcionem corretamente.
        if ($this->userId) {
            auth()->loginUsingId($this->userId);
        }

        $clube = Club::find($this->clubId);
        if (! $clube) {
            $relatorio->update(['status' => 'erro', 'erro' => 'Clube não encontrado.']);

            return;
        }

        $filtros = $relatorio->filtros ?? [];

        $pdf = match ($relatorio->tipo) {
            'fichas_completas' => $this->gerarFichasCompletas($clube, $filtros),
            'fichas_medicas' => $this->gerarFichasMedicas($clube, $filtros),
            'financeiro' => $this->gerarFinanceiro($clube, $filtros),
            default => throw new \InvalidArgumentException("Tipo de relatório não suportado em batch: {$relatorio->tipo}"),
        };

        $diretorio = "relatorios/{$this->clubId}";
        $nomeArquivo = $relatorio->tipo.'_'.now()->format('Ymd_His').'_'.Str::random(6).'.pdf';
        $caminho = "{$diretorio}/{$nomeArquivo}";

        Storage::disk('local')->makeDirectory($diretorio);
        $pdf->save(Storage::disk('local')->path($caminho));

        $relatorio->update([
            'status' => 'pronto',
            'arquivo' => $caminho,
            'expires_at' => now()->addHours(24),
        ]);

        app(TelegramNotifier::class)->notifyAdministrativeAction(
            'Relatório PDF pronto para download',
            [
                'Clube' => $clube->nome,
                'Tipo' => $relatorio->tipoLabel(),
                'Expira em' => '24 horas',
            ],
            'success',
        );
    }

    public function failed(Throwable $e): void
    {
        $relatorio = RelatorioGerado::withoutGlobalScopes()->find($this->relatorioId);
        $relatorio?->update(['status' => 'erro', 'erro' => $e->getMessage()]);

        app(TelegramNotifier::class)->notifyAdministrativeAction(
            'Falha ao gerar relatório PDF',
            [
                'Clube ID' => $this->clubId,
                'Tipo' => $relatorio?->tipo ?? '?',
                'Erro' => $e->getMessage(),
            ],
            'error',
        );
    }

    private function gerarFichasCompletas(Club $clube, array $filtros): \Barryvdh\DomPDF\PDF
    {
        $desbravadores = $this->baseDesbravadorQuery($clube, $filtros)
            ->with([
                'unidade:id,nome,conselheiro',
                'classe:id,nome',
                'classe.requisitos:id,classe_id',
                'especialidades:id,nome,area',
                'frequencias' => fn ($q) => $q->select('frequencias.id', 'frequencias.desbravador_id', 'frequencias.data', 'frequencias.presente', 'frequencias.pontual', 'frequencias.biblia', 'frequencias.uniforme')->orderByDesc('frequencias.data'),
                'eventos' => fn ($q) => $q->select('eventos.id', 'eventos.nome', 'eventos.data_inicio'),
                'requisitosCumpridos' => fn ($q) => $q
                    ->select('requisitos.id', 'classe_id', 'categoria', 'codigo', 'descricao')
                    ->with('classe:id,nome')
                    ->orderBy('requisitos.categoria')
                    ->orderBy('requisitos.codigo'),
            ])
            ->orderBy('nome')
            ->get()
            ->map(fn (Desbravador $desbravador) => $this->mapFichaCompleta($desbravador))
            ->all();

        return Pdf::loadView('relatorios.fichas_completas_lote', array_merge([
            'desbravadores' => $desbravadores,
            'filtros' => $this->buildMemberFilters($clube, $filtros),
        ], $this->reportContext($clube)))
            ->setPaper('a4', 'portrait');
    }

    private function gerarFichasMedicas(Club $clube, array $filtros): \Barryvdh\DomPDF\PDF
    {
        $desbravadores = $this->baseDesbravadorQuery($clube, $filtros)
            ->with(['unidade:id,nome', 'classe:id,nome'])
            ->orderBy('nome')
            ->get()
            ->map(fn (Desbravador $desbravador) => $this->mapFichaMedica($desbravador))
            ->all();

        return Pdf::loadView('relatorios.fichas_medicas_lote', array_merge([
            'desbravadores' => $desbravadores,
            'filtros' => $this->buildMemberFilters($clube, $filtros),
        ], $this->reportContext($clube)))
            ->setPaper('a4', 'portrait');
    }

    private function gerarFinanceiro(Club $clube, array $filtros): \Barryvdh\DomPDF\PDF
    {
        // GlobalScope ClubScope filtra por club_id automaticamente (auth foi configurado acima).
        $query = Caixa::orderBy('data_movimentacao', 'desc');

        if (! empty($filtros['data_inicio'])) {
            $query->where('data_movimentacao', '>=', $filtros['data_inicio']);
        }
        if (! empty($filtros['data_fim'])) {
            $query->where('data_movimentacao', '<=', $filtros['data_fim']);
        }
        if (! empty($filtros['tipo_movimentacao']) && $filtros['tipo_movimentacao'] !== 'todos') {
            $query->where('tipo', $filtros['tipo_movimentacao']);
        }
        if (! empty($filtros['categoria'])) {
            $query->where('categoria', $filtros['categoria']);
        }

        $movimentacoes = $query->get();

        return Pdf::loadView('relatorios.table', array_merge([
            'titulo' => 'Relatório Financeiro',
            'subtitulo' => 'Fluxo consolidado de entradas e saídas',
            'colunas' => ['Data', 'Descrição', 'Categoria', 'Tipo', 'Valor'],
            'linhas' => $movimentacoes->map(fn (Caixa $item) => [
                $item->data_movimentacao?->format('d/m/Y') ?? '-',
                $item->descricao,
                $item->categoria ?: '-',
                ucfirst($item->tipo),
                'R$ '.number_format((float) $item->valor, 2, ',', '.'),
            ])->all(),
            'metricas' => [
                ['label' => 'Entradas', 'value' => 'R$ '.number_format((float) $movimentacoes->where('tipo', 'entrada')->sum('valor'), 2, ',', '.')],
                ['label' => 'Saídas', 'value' => 'R$ '.number_format((float) $movimentacoes->where('tipo', 'saida')->sum('valor'), 2, ',', '.')],
                ['label' => 'Saldo', 'value' => 'R$ '.number_format((float) ($movimentacoes->where('tipo', 'entrada')->sum('valor') - $movimentacoes->where('tipo', 'saida')->sum('valor')), 2, ',', '.')],
            ],
            'filtros' => array_filter([
                'Período' => $this->formatDateRange($filtros['data_inicio'] ?? null, $filtros['data_fim'] ?? null),
            ]),
        ], $this->reportContext($clube)))
            ->setPaper('a4', 'landscape');
    }

    private function baseDesbravadorQuery(Club $clube, array $filtros): Builder
    {
        $query = Desbravador::query()
            ->whereHas('unidade', fn (Builder $q) => $q->where('club_id', $clube->id));

        $status = $filtros['status'] ?? 'ativos';
        if ($status === 'ativos') {
            $query->where('ativo', true);
        } elseif ($status === 'inativos') {
            $query->where('ativo', false);
        }

        if (! empty($filtros['unidade_id'])) {
            $query->where('unidade_id', (int) $filtros['unidade_id']);
        }

        return $query;
    }

    private function buildMemberFilters(Club $clube, array $filtros): array
    {
        $status = $filtros['status'] ?? 'ativos';

        return array_filter([
            'Status' => match ($status) {
                'inativos' => 'Somente inativos',
                'todos' => 'Todos os cadastrados',
                default => 'Somente ativos',
            },
            'Unidade' => ! empty($filtros['unidade_id'])
                ? $clube->unidades()->find((int) $filtros['unidade_id'])?->nome ?? 'Todas as unidades'
                : 'Todas as unidades',
        ]);
    }

    private function reportContext(Club $clube): array
    {
        $user = $this->userId ? User::find($this->userId) : null;
        $logoBase64 = '';

        if ($clube->logo) {
            $path = storage_path('app/public/'.$clube->logo);
            if (file_exists($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };
                $logoBase64 = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
            }
        }

        if (! $logoBase64) {
            $fallback = public_path('icons/icon-192.png');
            if (file_exists($fallback)) {
                $logoBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($fallback));
            }
        }

        return [
            'clubeNome' => $clube->nome,
            'clubeCidade' => $clube->cidade ?? '',
            'clubeAssociacao' => $clube->associacao ?? '',
            'clubeLogoBase64' => $logoBase64,
            'responsavelNome' => $user?->name ?? 'Sistema',
            'emitidoEm' => now()->format('d/m/Y H:i'),
        ];
    }

    private function mapFichaCompleta(Desbravador $desbravador): array
    {
        $frequencias = $desbravador->frequencias;
        $totalRequisitos = $desbravador->classe?->requisitos?->count() ?? 0;
        $requisitosConcluidos = $desbravador->requisitosCumpridos
            ->where('classe_id', $desbravador->classe?->id)
            ->count();

        return [
            'nome' => $desbravador->nome,
            'status' => $desbravador->ativo ? 'Ativo' : 'Inativo',
            'data_nascimento' => $desbravador->data_nascimento?->format('d/m/Y') ?? '-',
            'idade' => $desbravador->data_nascimento ? $desbravador->data_nascimento->age.' anos' : '-',
            'sexo' => match ($desbravador->sexo) { 'M' => 'Masculino', 'F' => 'Feminino', default => '-' },
            'cpf' => $desbravador->cpf ?: '-',
            'rg' => $desbravador->rg ?: '-',
            'unidade' => $desbravador->unidade?->nome ?? 'Sem unidade',
            'conselheiro' => $desbravador->unidade?->conselheiro ?: '-',
            'classe' => $desbravador->classe?->nome ?? 'Não definida',
            'progresso_classe' => $totalRequisitos > 0
                ? (int) round(($requisitosConcluidos / $totalRequisitos) * 100)
                : 0,
            'email' => $desbravador->email ?: '-',
            'telefone' => $desbravador->telefone ?: '-',
            'endereco' => $desbravador->endereco ?: '-',
            'nome_responsavel' => $desbravador->nome_responsavel ?: '-',
            'telefone_responsavel' => $desbravador->telefone_responsavel ?: '-',
            'numero_sus' => $desbravador->numero_sus ?: '-',
            'tipo_sanguineo' => $desbravador->tipo_sanguineo ?: '-',
            'plano_saude' => $desbravador->plano_saude ?: 'Não informado',
            'alergias' => $desbravador->alergias ?: 'Nenhuma alergia registrada',
            'medicamentos_continuos' => $desbravador->medicamentos_continuos ?: 'Nenhum medicamento registrado',
            'especialidades' => $desbravador->especialidades
                ->map(fn ($e) => trim($e->nome.($e->area ? ' - '.$e->area : '')))
                ->values()->all(),
            'eventos' => $desbravador->eventos->take(8)
                ->map(fn ($e) => [
                    'nome' => $e->nome,
                    'data' => $e->data_inicio?->format('d/m/Y') ?? '-',
                    'pago' => $e->pivot->pago ? 'Sim' : 'Não',
                    'autorizacao' => $e->pivot->autorizacao_entregue ? 'Entregue' : 'Pendente',
                ])->values()->all(),
            'frequencias' => [
                'total' => $frequencias->count(),
                'presencas' => $frequencias->where('presente', true)->count(),
                'faltas' => $frequencias->where('presente', false)->count(),
                'pontos' => $frequencias->sum('pontos'),
                'ultimos' => $frequencias->take(8)->map(fn ($f) => [
                    'data' => $f->data?->format('d/m/Y') ?? '-',
                    'presenca' => $f->presente ? 'Presente' : 'Falta',
                    'pontos' => $f->pontos,
                ])->values()->all(),
            ],
            'requisitos' => $desbravador->requisitosCumpridos->take(12)
                ->map(fn ($r) => [
                    'classe' => $r->classe?->nome ?? '-',
                    'categoria' => $r->categoria ?: '-',
                    'codigo' => $r->codigo ?: '-',
                    'descricao' => $r->descricao,
                    'conclusao' => $r->pivot->data_conclusao
                        ? Carbon::parse($r->pivot->data_conclusao)->format('d/m/Y')
                        : '-',
                ])->values()->all(),
        ];
    }

    private function mapFichaMedica(Desbravador $desbravador): array
    {
        return [
            'nome' => $desbravador->nome,
            'unidade' => $desbravador->unidade?->nome ?? 'Sem unidade',
            'data_nascimento' => $desbravador->data_nascimento?->format('d/m/Y') ?? '-',
            'idade' => $desbravador->data_nascimento ? $desbravador->data_nascimento->age.' anos' : '-',
            'sexo' => match ($desbravador->sexo) { 'M' => 'Masculino', 'F' => 'Feminino', default => '-' },
            'classe' => $desbravador->classe?->nome ?? 'Não definida',
            'nome_responsavel' => $desbravador->nome_responsavel ?: '-',
            'telefone_responsavel' => $desbravador->telefone_responsavel ?: '-',
            'telefone' => $desbravador->telefone ?: '-',
            'tipo_sanguineo' => $desbravador->tipo_sanguineo ?: '-',
            'numero_sus' => $desbravador->numero_sus ?: '-',
            'plano_saude' => $desbravador->plano_saude ?: 'Não informado',
            'alergias' => $desbravador->alergias ?: 'Nenhuma alergia registrada.',
            'medicamentos_continuos' => $desbravador->medicamentos_continuos ?: 'Nenhum medicamento registrado.',
        ];
    }

    private function formatDateRange(?string $inicio, ?string $fim): string
    {
        if ($inicio && $fim) {
            return Carbon::parse($inicio)->format('d/m/Y').' a '.Carbon::parse($fim)->format('d/m/Y');
        }
        if ($inicio) {
            return 'A partir de '.Carbon::parse($inicio)->format('d/m/Y');
        }
        if ($fim) {
            return 'Até '.Carbon::parse($fim)->format('d/m/Y');
        }

        return 'Histórico completo';
    }
}
