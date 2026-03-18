<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\Patrimonio;
use App\Models\Unidade;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    private const REPORT_TYPES = [
        'desbravadores',
        'fichas_completas',
        'fichas_medicas',
        'contatos_emergencia',
        'frequencia',
        'inadimplencia',
        'aniversariantes',
        'unidades',
        'ranking_unidades',
        'ranking_desbravadores',
        'financeiro',
        'patrimonio',
        'caixa',
    ];

    public function index()
    {
        $unidades = $this->baseUnidadeQuery()
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return view('relatorios.index', compact('unidades'));
    }

    public function gerarPersonalizado(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:'.implode(',', self::REPORT_TYPES),
            'status' => 'nullable|in:ativos,inativos,todos',
            'unidade_id' => 'nullable|exists:unidades,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'tipo_movimentacao' => 'nullable|in:todos,entrada,saida',
            'mes' => 'nullable|integer|min:1|max:12',
            'ano' => 'nullable|integer|min:2020|max:2100',
            'mes_aniversario' => 'nullable|integer|min:1|max:12',
        ]);

        return match ($validated['tipo']) {
            'desbravadores' => $this->relatorioDesbravadores($request),
            'fichas_completas' => $this->relatorioFichasCompletas($request),
            'fichas_medicas' => $this->relatorioFichasMedicas($request),
            'contatos_emergencia' => $this->relatorioContatosEmergencia($request),
            'frequencia' => $this->relatorioFrequencia($request),
            'inadimplencia' => $this->relatorioInadimplencia($request),
            'aniversariantes' => $this->relatorioAniversariantes($request),
            'unidades' => $this->relatorioUnidades(),
            'ranking_unidades' => $this->relatorioRankingUnidades(),
            'ranking_desbravadores' => $this->relatorioRankingDesbravadores(),
            'financeiro', 'caixa' => $this->relatorioFinanceiroPersonalizado($request),
            'patrimonio' => $this->patrimonio(),
            default => abort(422, 'Tipo de relatório inválido.'),
        };
    }

    public function financeiro()
    {
        $movimentacoes = Caixa::orderBy('data_movimentacao', 'desc')->get();

        return $this->renderTablePdf(
            titulo: 'Relatório Financeiro',
            subtitulo: 'Fluxo consolidado de entradas e saídas',
            colunas: ['Data', 'Descrição', 'Categoria', 'Tipo', 'Valor'],
            linhas: $movimentacoes->map(fn (Caixa $item) => [
                $item->data_movimentacao?->format('d/m/Y') ?? '-',
                $item->descricao,
                $item->categoria ?: '-',
                ucfirst($item->tipo),
                'R$ '.number_format((float) $item->valor, 2, ',', '.'),
            ])->all(),
            metricas: [
                ['label' => 'Entradas', 'value' => 'R$ '.number_format((float) $movimentacoes->where('tipo', 'entrada')->sum('valor'), 2, ',', '.')],
                ['label' => 'Saídas', 'value' => 'R$ '.number_format((float) $movimentacoes->where('tipo', 'saida')->sum('valor'), 2, ',', '.')],
                ['label' => 'Saldo', 'value' => 'R$ '.number_format((float) ($movimentacoes->where('tipo', 'entrada')->sum('valor') - $movimentacoes->where('tipo', 'saida')->sum('valor')), 2, ',', '.')],
            ],
            filtros: ['Período' => 'Histórico completo'],
            arquivo: 'relatorio_financeiro.pdf',
            orientation: 'landscape',
        );
    }

    public function patrimonio()
    {
        $itens = Patrimonio::orderBy('item')->get();

        return $this->renderTablePdf(
            titulo: 'Inventário Patrimonial',
            subtitulo: 'Mapa geral de bens e materiais do clube',
            colunas: ['Item', 'Quantidade', 'Estado', 'Local', 'Valor Unit.', 'Valor Total'],
            linhas: $itens->map(fn (Patrimonio $item) => [
                $item->item,
                (string) $item->quantidade,
                $item->estado_conservacao ?: '-',
                $item->local_armazenamento ?: '-',
                'R$ '.number_format((float) $item->valor_estimado, 2, ',', '.'),
                'R$ '.number_format((float) $item->valor_estimado * $item->quantidade, 2, ',', '.'),
            ])->all(),
            metricas: [
                ['label' => 'Itens cadastrados', 'value' => (string) $itens->count()],
                ['label' => 'Quantidade total', 'value' => (string) $itens->sum('quantidade')],
                ['label' => 'Valor estimado', 'value' => 'R$ '.number_format((float) $itens->sum(fn ($item) => $item->valor_estimado * $item->quantidade), 2, ',', '.')],
            ],
            filtros: ['Escopo' => 'Patrimônio completo'],
            arquivo: 'relatorio_patrimonio.pdf',
            orientation: 'landscape',
        );
    }

    public function autorizacao(Desbravador $desbravador)
    {
        return Pdf::loadView('relatorios.autorizacao', compact('desbravador'))
            ->stream("autorizacao_{$desbravador->nome}.pdf");
    }

    public function carteirinha(Desbravador $desbravador)
    {
        $desbravador->load('unidade', 'especialidades');

        return Pdf::loadView('relatorios.carteirinha', compact('desbravador'))
            ->setPaper('a4', 'portrait')
            ->stream("carteirinha_{$desbravador->nome}.pdf");
    }

    public function fichaMedica(Desbravador $desbravador)
    {
        $desbravador->loadMissing('unidade', 'classe');

        return Pdf::loadView('relatorios.ficha_medica', array_merge(
            ['desbravador' => $desbravador],
            $this->reportContext()
        ))->stream("ficha_medica_{$desbravador->nome}.pdf");
    }

    private function relatorioDesbravadores(Request $request)
    {
        $desbravadores = $this->baseDesbravadorQuery($request)
            ->with(['unidade:id,nome', 'classe:id,nome'])
            ->orderBy('nome')
            ->get();

        return $this->renderTablePdf(
            titulo: 'Lista de Desbravadores',
            subtitulo: 'Cadastro resumido para secretaria e operação',
            colunas: ['Nome', 'Unidade', 'Classe', 'Status', 'Idade', 'Responsável', 'Telefone'],
            linhas: $desbravadores->map(fn (Desbravador $desbravador) => [
                $desbravador->nome,
                $desbravador->unidade->nome ?? 'Sem unidade',
                $desbravador->classe->nome ?? 'Não definida',
                $desbravador->ativo ? 'Ativo' : 'Inativo',
                $this->formatAge($desbravador),
                $desbravador->nome_responsavel ?: '-',
                $desbravador->telefone_responsavel ?: ($desbravador->telefone ?: '-'),
            ])->all(),
            metricas: [
                ['label' => 'Registros', 'value' => (string) $desbravadores->count()],
                ['label' => 'Ativos', 'value' => (string) $desbravadores->where('ativo', true)->count()],
                ['label' => 'Inativos', 'value' => (string) $desbravadores->where('ativo', false)->count()],
            ],
            filtros: $this->buildMemberFilters($request),
            arquivo: 'relatorio_desbravadores.pdf',
            orientation: 'landscape',
        );
    }

    private function relatorioFichasCompletas(Request $request)
    {
        $desbravadores = $this->baseDesbravadorQuery($request)
            ->with([
                'unidade:id,nome,conselheiro',
                'classe:id,nome',
                'classe.requisitos:id,classe_id',
                'especialidades:id,nome,area',
                'frequencias' => fn ($query) => $query->select('frequencias.id', 'frequencias.desbravador_id', 'frequencias.data', 'frequencias.presente', 'frequencias.pontual', 'frequencias.biblia', 'frequencias.uniforme')->orderByDesc('frequencias.data'),
                'eventos' => fn ($query) => $query->select('eventos.id', 'eventos.nome', 'eventos.data_inicio'),
                'requisitosCumpridos' => fn ($query) => $query
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
            'filtros' => $this->buildMemberFilters($request),
        ], $this->reportContext()))
            ->setPaper('a4', 'portrait')
            ->stream('fichas_completas_desbravadores.pdf');
    }

    private function relatorioFichasMedicas(Request $request)
    {
        $desbravadores = $this->baseDesbravadorQuery($request)
            ->with(['unidade:id,nome', 'classe:id,nome'])
            ->orderBy('nome')
            ->get()
            ->map(fn (Desbravador $desbravador) => $this->mapFichaMedica($desbravador))
            ->all();

        return Pdf::loadView('relatorios.fichas_medicas_lote', array_merge([
            'desbravadores' => $desbravadores,
            'filtros' => $this->buildMemberFilters($request),
        ], $this->reportContext()))
            ->setPaper('a4', 'portrait')
            ->stream('fichas_medicas_lote.pdf');
    }

    private function relatorioContatosEmergencia(Request $request)
    {
        $desbravadores = $this->baseDesbravadorQuery($request)
            ->with(['unidade:id,nome', 'classe:id,nome'])
            ->orderBy('nome')
            ->get();

        return $this->renderTablePdf(
            titulo: 'Contatos de Emergência',
            subtitulo: 'Consulta rápida para saídas, acampamentos e eventos',
            colunas: ['Nome', 'Unidade', 'Responsável', 'Telefone', 'Alergias', 'Plano', 'Tipo Sanguíneo'],
            linhas: $desbravadores->map(fn (Desbravador $desbravador) => [
                $desbravador->nome,
                $desbravador->unidade->nome ?? 'Sem unidade',
                $desbravador->nome_responsavel ?: '-',
                $desbravador->telefone_responsavel ?: ($desbravador->telefone ?: '-'),
                $desbravador->alergias ?: 'Sem registro',
                $desbravador->plano_saude ?: 'Não informado',
                $desbravador->tipo_sanguineo ?: '-',
            ])->all(),
            metricas: [
                ['label' => 'Contatos', 'value' => (string) $desbravadores->count()],
                ['label' => 'Com alergia registrada', 'value' => (string) $desbravadores->filter(fn ($item) => filled($item->alergias))->count()],
                ['label' => 'Com plano de saúde', 'value' => (string) $desbravadores->filter(fn ($item) => filled($item->plano_saude))->count()],
            ],
            filtros: $this->buildMemberFilters($request),
            arquivo: 'relatorio_contatos_emergencia.pdf',
            orientation: 'landscape',
        );
    }

    private function relatorioFrequencia(Request $request)
    {
        $mes = (int) ($request->input('mes') ?: now()->month);
        $ano = (int) ($request->input('ano') ?: now()->year);

        $desbravadores = $this->baseDesbravadorQuery($request)
            ->with([
                'unidade:id,nome',
                'frequencias' => fn ($query) => $query
                    ->select('frequencias.id', 'frequencias.desbravador_id', 'frequencias.data', 'frequencias.presente', 'frequencias.pontual', 'frequencias.biblia', 'frequencias.uniforme')
                    ->whereMonth('frequencias.data', $mes)
                    ->whereYear('frequencias.data', $ano)
                    ->orderBy('frequencias.data'),
            ])
            ->orderBy('nome')
            ->get();

        return $this->renderTablePdf(
            titulo: 'Relatório de Frequência',
            subtitulo: 'Consolidado mensal de presença e pontuação',
            colunas: ['Nome', 'Unidade', 'Presenças', 'Faltas', 'Pontual', 'Bíblia', 'Uniforme', 'Pontos'],
            linhas: $desbravadores->map(function (Desbravador $desbravador) {
                $frequencias = $desbravador->frequencias;

                return [
                    $desbravador->nome,
                    $desbravador->unidade->nome ?? 'Sem unidade',
                    (string) $frequencias->where('presente', true)->count(),
                    (string) $frequencias->where('presente', false)->count(),
                    (string) $frequencias->where('pontual', true)->count(),
                    (string) $frequencias->where('biblia', true)->count(),
                    (string) $frequencias->where('uniforme', true)->count(),
                    (string) $frequencias->sum('pontos'),
                ];
            })->all(),
            metricas: [
                ['label' => 'Mês/Ano', 'value' => str_pad((string) $mes, 2, '0', STR_PAD_LEFT).'/'.$ano],
                ['label' => 'Participantes', 'value' => (string) $desbravadores->count()],
                ['label' => 'Pontos do período', 'value' => (string) $desbravadores->sum(fn ($desbravador) => $desbravador->frequencias->sum('pontos'))],
            ],
            filtros: array_merge($this->buildMemberFilters($request), [
                'Competência' => str_pad((string) $mes, 2, '0', STR_PAD_LEFT).'/'.$ano,
            ]),
            arquivo: 'relatorio_frequencia.pdf',
            orientation: 'landscape',
        );
    }

    private function relatorioInadimplencia(Request $request)
    {
        $mensalidades = Mensalidade::with(['desbravador.unidade:id,nome'])
            ->inadimplentes()
            ->whereHas('desbravador.unidade', fn (Builder $query) => $this->applyUnidadeScope($query))
            ->when($request->filled('status') && $request->input('status') !== 'todos', function (Builder $query) use ($request) {
                $query->whereHas('desbravador', function (Builder $subquery) use ($request) {
                    $subquery->where('ativo', $request->input('status') === 'ativos');
                });
            })
            ->when($request->filled('unidade_id'), function (Builder $query) use ($request) {
                $query->whereHas('desbravador', fn (Builder $subquery) => $subquery->where('unidade_id', $request->integer('unidade_id')));
            })
            ->orderBy('ano')
            ->orderBy('mes')
            ->get();

        return $this->renderTablePdf(
            titulo: 'Relatório de Inadimplência',
            subtitulo: 'Mensalidades pendentes em atraso',
            colunas: ['Desbravador', 'Unidade', 'Competência', 'Status', 'Valor'],
            linhas: $mensalidades->map(fn (Mensalidade $mensalidade) => [
                $mensalidade->desbravador?->nome ?? 'Desbravador removido',
                $mensalidade->desbravador?->unidade?->nome ?? 'Sem unidade',
                str_pad((string) $mensalidade->mes, 2, '0', STR_PAD_LEFT).'/'.$mensalidade->ano,
                ucfirst($mensalidade->status),
                'R$ '.number_format((float) $mensalidade->valor, 2, ',', '.'),
            ])->all(),
            metricas: [
                ['label' => 'Pendências', 'value' => (string) $mensalidades->count()],
                ['label' => 'Desbravadores', 'value' => (string) $mensalidades->pluck('desbravador_id')->filter()->unique()->count()],
                ['label' => 'Total em atraso', 'value' => 'R$ '.number_format((float) $mensalidades->sum('valor'), 2, ',', '.')],
            ],
            filtros: $this->buildMemberFilters($request),
            arquivo: 'relatorio_inadimplencia.pdf',
            orientation: 'landscape',
        );
    }

    private function relatorioAniversariantes(Request $request)
    {
        $mes = (int) ($request->input('mes_aniversario') ?: now()->month);

        $desbravadores = $this->baseDesbravadorQuery($request)
            ->with(['unidade:id,nome', 'classe:id,nome'])
            ->whereMonth('data_nascimento', $mes)
            ->orderBy('data_nascimento')
            ->orderBy('nome')
            ->get();

        return $this->renderTablePdf(
            titulo: 'Relatório de Aniversariantes',
            subtitulo: 'Planejamento mensal de celebrações e homenagens',
            colunas: ['Dia', 'Nome', 'Unidade', 'Classe', 'Status', 'Idade no ano'],
            linhas: $desbravadores->map(function (Desbravador $desbravador) {
                return [
                    $desbravador->data_nascimento?->format('d/m') ?? '-',
                    $desbravador->nome,
                    $desbravador->unidade->nome ?? 'Sem unidade',
                    $desbravador->classe->nome ?? 'Não definida',
                    $desbravador->ativo ? 'Ativo' : 'Inativo',
                    $desbravador->data_nascimento?->age ? $desbravador->data_nascimento->age.' anos' : '-',
                ];
            })->all(),
            metricas: [
                ['label' => 'Mês', 'value' => $this->monthName($mes)],
                ['label' => 'Aniversariantes', 'value' => (string) $desbravadores->count()],
            ],
            filtros: array_merge($this->buildMemberFilters($request), [
                'Mês de aniversário' => $this->monthName($mes),
            ]),
            arquivo: 'relatorio_aniversariantes.pdf',
            orientation: 'landscape',
        );
    }

    private function relatorioUnidades()
    {
        $unidades = $this->baseUnidadeQuery()
            ->with(['desbravadores.frequencias'])
            ->withCount('desbravadores')
            ->orderBy('nome')
            ->get();

        return $this->renderTablePdf(
            titulo: 'Relatório de Unidades',
            subtitulo: 'Estrutura, liderança e desempenho das unidades',
            colunas: ['Unidade', 'Conselheiro', 'Membros', 'Ativos', 'Inativos', 'Pontos'],
            linhas: $unidades->map(fn (Unidade $unidade) => [
                $unidade->nome,
                $unidade->conselheiro ?: 'Vago',
                (string) $unidade->desbravadores_count,
                (string) $unidade->desbravadores->where('ativo', true)->count(),
                (string) $unidade->desbravadores->where('ativo', false)->count(),
                (string) $unidade->desbravadores->sum(fn ($desbravador) => $desbravador->frequencias->sum('pontos')),
            ])->all(),
            metricas: [
                ['label' => 'Unidades', 'value' => (string) $unidades->count()],
                ['label' => 'Membros', 'value' => (string) $unidades->sum('desbravadores_count')],
            ],
            filtros: ['Escopo' => 'Todas as unidades do clube'],
            arquivo: 'relatorio_unidades.pdf',
            orientation: 'landscape',
        );
    }

    private function relatorioRankingUnidades()
    {
        $ano = $this->rankingYear();
        $ranking = $this->baseUnidadeQuery()
            ->with(['desbravadores.frequencias' => fn ($query) => $query->whereYear('data', $ano)])
            ->get()
            ->map(function (Unidade $unidade) {
                $membros = $unidade->desbravadores->count();
                $pontos = $unidade->desbravadores->sum(fn ($desbravador) => $desbravador->frequencias->sum('pontos'));

                return [
                    'nome' => $unidade->nome,
                    'subtexto' => $membros.' membros',
                    'pontos' => $pontos,
                    'media' => $membros > 0 ? round($pontos / $membros, 1) : 0,
                ];
            })
            ->sortByDesc('pontos')
            ->values();

        return $this->renderTablePdf(
            titulo: 'Ranking das Unidades',
            subtitulo: 'Pontuação anual acumulada por desempenho',
            colunas: ['Posição', 'Unidade', 'Resumo', 'Pontos', 'Média por membro'],
            linhas: $ranking->map(fn (array $item, int $index) => [
                '#'.($index + 1),
                $item['nome'],
                $item['subtexto'],
                (string) $item['pontos'],
                (string) $item['media'],
            ])->all(),
            metricas: [
                ['label' => 'Ano', 'value' => (string) $ano],
                ['label' => 'Unidades ranqueadas', 'value' => (string) $ranking->count()],
                ['label' => 'Maior pontuação', 'value' => (string) ($ranking->first()['pontos'] ?? 0)],
                ['label' => 'Melhor média', 'value' => (string) ($ranking->sortByDesc('media')->first()['media'] ?? 0)],
            ],
            filtros: ['Base' => 'Pontuação anual de frequência', 'Ano' => (string) $ano],
            arquivo: 'ranking_unidades.pdf',
            orientation: 'landscape',
        );
    }

    private function relatorioRankingDesbravadores()
    {
        $ano = $this->rankingYear();
        $ranking = $this->baseDesbravadorQuery(new Request(['status' => 'ativos']))
            ->with(['unidade:id,nome', 'frequencias' => fn ($query) => $query->whereYear('data', $ano)])
            ->orderBy('nome')
            ->get()
            ->map(function (Desbravador $desbravador) {
                return [
                    'nome' => $desbravador->nome,
                    'unidade' => $desbravador->unidade->nome ?? 'Sem unidade',
                    'pontos' => $desbravador->frequencias->sum('pontos'),
                    'presencas' => $desbravador->frequencias->where('presente', true)->count(),
                ];
            })
            ->sortByDesc('pontos')
            ->values();

        return $this->renderTablePdf(
            titulo: 'Ranking Individual',
            subtitulo: 'Desempenho anual dos desbravadores ativos',
            colunas: ['Posição', 'Desbravador', 'Unidade', 'Presenças', 'Pontos'],
            linhas: $ranking->map(fn (array $item, int $index) => [
                '#'.($index + 1),
                $item['nome'],
                $item['unidade'],
                (string) $item['presencas'],
                (string) $item['pontos'],
            ])->all(),
            metricas: [
                ['label' => 'Ano', 'value' => (string) $ano],
                ['label' => 'Participantes', 'value' => (string) $ranking->count()],
                ['label' => 'Maior pontuação', 'value' => (string) ($ranking->first()['pontos'] ?? 0)],
                ['label' => 'Maior presença', 'value' => (string) ($ranking->sortByDesc('presencas')->first()['presencas'] ?? 0)],
            ],
            filtros: ['Base' => 'Pontuação anual dos desbravadores ativos', 'Ano' => (string) $ano],
            arquivo: 'ranking_desbravadores.pdf',
            orientation: 'landscape',
        );
    }

    private function relatorioFinanceiroPersonalizado(Request $request)
    {
        $query = Caixa::query();

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_movimentacao', '>=', $request->input('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_movimentacao', '<=', $request->input('data_fim'));
        }

        if ($request->input('tipo_movimentacao', 'todos') !== 'todos') {
            $query->where('tipo', $request->input('tipo_movimentacao'));
        }

        $movimentacoes = $query->orderBy('data_movimentacao', 'desc')->get();

        return $this->renderTablePdf(
            titulo: 'Relatório Financeiro Personalizado',
            subtitulo: 'Movimentações filtradas por período e tipo',
            colunas: ['Data', 'Descrição', 'Categoria', 'Tipo', 'Valor'],
            linhas: $movimentacoes->map(fn (Caixa $item) => [
                $item->data_movimentacao?->format('d/m/Y') ?? '-',
                $item->descricao,
                $item->categoria ?: '-',
                ucfirst($item->tipo),
                'R$ '.number_format((float) $item->valor, 2, ',', '.'),
            ])->all(),
            metricas: [
                ['label' => 'Movimentações', 'value' => (string) $movimentacoes->count()],
                ['label' => 'Entradas', 'value' => 'R$ '.number_format((float) $movimentacoes->where('tipo', 'entrada')->sum('valor'), 2, ',', '.')],
                ['label' => 'Saídas', 'value' => 'R$ '.number_format((float) $movimentacoes->where('tipo', 'saida')->sum('valor'), 2, ',', '.')],
            ],
            filtros: [
                'Período' => $this->formatDateRange($request->input('data_inicio'), $request->input('data_fim')),
                'Tipo' => match ($request->input('tipo_movimentacao', 'todos')) {
                    'entrada' => 'Somente entradas',
                    'saida' => 'Somente saídas',
                    default => 'Entradas e saídas',
                },
            ],
            arquivo: 'relatorio_financeiro_filtrado.pdf',
            orientation: 'landscape',
        );
    }

    private function baseDesbravadorQuery(Request $request): Builder
    {
        $query = Desbravador::query()
            ->whereHas('unidade', fn (Builder $subquery) => $this->applyUnidadeScope($subquery));

        $status = $request->input('status', 'ativos');
        if ($status === 'ativos') {
            $query->where('ativo', true);
        } elseif ($status === 'inativos') {
            $query->where('ativo', false);
        }

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->integer('unidade_id'));
        }

        return $query;
    }

    private function baseUnidadeQuery(): Builder
    {
        return $this->applyUnidadeScope(Unidade::query());
    }

    private function buildMemberFilters(Request $request): array
    {
        $status = $request->input('status', 'ativos');
        $unidade = $request->filled('unidade_id')
            ? $this->baseUnidadeQuery()->find($request->integer('unidade_id'))?->nome
            : null;

        return array_filter([
                'Status' => match ($status) {
                    'inativos' => 'Somente inativos',
                    'todos' => 'Todos os cadastrados',
                    default => 'Somente ativos',
                },
            'Unidade' => $unidade ?: 'Todas as unidades',
        ]);
    }

    private function renderTablePdf(
        string $titulo,
        string $subtitulo,
        array $colunas,
        array $linhas,
        array $metricas,
        array $filtros,
        string $arquivo,
        string $orientation = 'portrait',
    ) {
        return Pdf::loadView('relatorios.table', array_merge([
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'colunas' => $colunas,
            'linhas' => $linhas,
            'metricas' => $metricas,
            'filtros' => array_filter($filtros),
        ], $this->reportContext()))
            ->setPaper('a4', $orientation)
            ->stream($arquivo);
    }

    private function reportContext(): array
    {
        return [
            'clubeNome' => auth()->user()?->club?->nome ?? 'Clube de Desbravadores',
            'responsavelNome' => auth()->user()?->name ?? 'Sistema',
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
        $progressoClasse = $totalRequisitos > 0
            ? (int) round(($requisitosConcluidos / $totalRequisitos) * 100)
            : 0;

        return [
            'nome' => $desbravador->nome,
            'status' => $desbravador->ativo ? 'Ativo' : 'Inativo',
            'data_nascimento' => $desbravador->data_nascimento?->format('d/m/Y') ?? '-',
            'idade' => $this->formatAge($desbravador),
            'sexo' => match ($desbravador->sexo) {
                'M' => 'Masculino',
                'F' => 'Feminino',
                default => '-',
            },
            'cpf' => $desbravador->cpf ?: '-',
            'rg' => $desbravador->rg ?: '-',
            'unidade' => $desbravador->unidade?->nome ?? 'Sem unidade',
            'conselheiro' => $desbravador->unidade?->conselheiro ?: '-',
            'classe' => $desbravador->classe?->nome ?? 'Não definida',
            'progresso_classe' => $progressoClasse,
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
                ->map(fn ($especialidade) => trim($especialidade->nome.($especialidade->area ? ' - '.$especialidade->area : '')))
                ->values()
                ->all(),
            'eventos' => $desbravador->eventos
                ->take(8)
                ->map(fn ($evento) => [
                    'nome' => $evento->nome,
                    'data' => $evento->data_inicio?->format('d/m/Y') ?? '-',
                    'pago' => $evento->pivot->pago ? 'Sim' : 'Não',
                    'autorizacao' => $evento->pivot->autorizacao_entregue ? 'Entregue' : 'Pendente',
                ])
                ->values()
                ->all(),
            'frequencias' => [
                'total' => $frequencias->count(),
                'presencas' => $frequencias->where('presente', true)->count(),
                'faltas' => $frequencias->where('presente', false)->count(),
                'pontos' => $frequencias->sum('pontos'),
                'ultimos' => $frequencias->take(8)
                    ->map(fn ($frequencia) => [
                        'data' => $frequencia->data?->format('d/m/Y') ?? '-',
                        'presenca' => $frequencia->presente ? 'Presente' : 'Falta',
                        'pontos' => $frequencia->pontos,
                    ])
                    ->values()
                    ->all(),
            ],
            'requisitos' => $desbravador->requisitosCumpridos
                ->take(12)
                ->map(fn ($requisito) => [
                    'classe' => $requisito->classe?->nome ?? '-',
                    'categoria' => $requisito->categoria ?: '-',
                    'codigo' => $requisito->codigo ?: '-',
                    'descricao' => $requisito->descricao,
                    'conclusao' => $requisito->pivot->data_conclusao
                        ? Carbon::parse($requisito->pivot->data_conclusao)->format('d/m/Y')
                        : '-',
                ])
                ->values()
                ->all(),
        ];
    }

    private function mapFichaMedica(Desbravador $desbravador): array
    {
        return [
            'nome' => $desbravador->nome,
            'unidade' => $desbravador->unidade?->nome ?? 'Sem unidade',
            'data_nascimento' => $desbravador->data_nascimento?->format('d/m/Y') ?? '-',
            'idade' => $this->formatAge($desbravador),
            'sexo' => match ($desbravador->sexo) {
                'M' => 'Masculino',
                'F' => 'Feminino',
                default => '-',
            },
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

    private function currentClubId(): ?int
    {
        return auth()->user()?->club_id;
    }

    private function applyUnidadeScope(Builder $query): Builder
    {
        $clubId = $this->currentClubId();

        if (! $clubId) {
            return $query;
        }

        $hasUnitsLinkedToClub = Unidade::where('club_id', $clubId)->exists();

        if ($hasUnitsLinkedToClub) {
            return $query->where('club_id', $clubId);
        }

        return $query->whereNull('club_id');
    }

    private function formatAge(Desbravador $desbravador): string
    {
        return $desbravador->data_nascimento
            ? $desbravador->data_nascimento->age.' anos'
            : '-';
    }

    private function formatDateRange(?string $start, ?string $end): string
    {
        if ($start && $end) {
            return Carbon::parse($start)->format('d/m/Y').' a '.Carbon::parse($end)->format('d/m/Y');
        }

        if ($start) {
            return 'A partir de '.Carbon::parse($start)->format('d/m/Y');
        }

        if ($end) {
            return 'Até '.Carbon::parse($end)->format('d/m/Y');
        }

        return 'Período completo';
    }

    private function rankingYear(): int
    {
        return now()->year;
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ][$month] ?? '-';
    }
}
