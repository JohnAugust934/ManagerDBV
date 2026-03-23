<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-dbv-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            {{ __('Manual do Sistema') }}
        </h2>
    </x-slot>

    @php
        $modules = [
            [
                'key' => 'secretaria',
                'title' => 'Secretaria',
                'badge' => 'Operacional',
                'summary' => 'Cadastro, atualização de dados do clube, membros e documentos oficiais.',
                'sections' => [
                    [
                        'title' => 'Meu Clube (Dados Institucionais)',
                        'description' => 'Mantém os dados oficiais que aparecem em telas e relatórios.',
                        'can_do' => ['Editar nome e cidade do clube', 'Atualizar logo', 'Garantir identidade visual correta'],
                        'steps' => [
                            ['title' => 'Abra o menu Secretaria > Meu Clube', 'detail' => 'No menu lateral, clique em "Meu Clube" para abrir as configurações principais do clube.'],
                            ['title' => 'Preencha ou revise os dados', 'detail' => 'Confira nome, cidade e demais informações. Evite abreviações para manter padrão nos documentos.'],
                            ['title' => 'Atualize a logo quando necessário', 'detail' => 'Carregue uma imagem nítida para que relatórios e cabeçalhos fiquem padronizados.'],
                            ['title' => 'Salve e valide o resultado', 'detail' => 'Após salvar, retorne ao painel e verifique se os dados foram refletidos corretamente.'],
                        ],
                    ],
                    [
                        'title' => 'Desbravadores (Cadastro e Edição)',
                        'description' => 'Centraliza o cadastro completo de membros do clube.',
                        'can_do' => ['Criar novo cadastro', 'Editar ficha de membro', 'Consultar detalhes por perfil'],
                        'steps' => [
                            ['title' => 'Acesse Secretaria > Desbravadores', 'detail' => 'Use a lista para localizar cadastros existentes ou iniciar um novo registro.'],
                            ['title' => 'Clique em Novo Cadastro', 'detail' => 'Preencha os campos obrigatórios com atenção para evitar duplicidade ou dados incompletos.'],
                            ['title' => 'Finalize e revise a ficha', 'detail' => 'Depois de salvar, abra o cadastro para validar contatos, responsáveis e informações complementares.'],
                            ['title' => 'Use edição para manutenção contínua', 'detail' => 'Sempre que houver mudança de unidade, telefone ou status, atualize imediatamente.'],
                        ],
                    ],
                    [
                        'title' => 'Documentos (Atas e Atos Oficiais)',
                        'description' => 'Registra o histórico institucional do clube.',
                        'can_do' => ['Criar atas', 'Criar atos oficiais', 'Editar e revisar histórico documental'],
                        'steps' => [
                            ['title' => 'Abra Secretaria > Documentos', 'detail' => 'Entre no submenu e escolha entre Atas ou Atos Oficiais.'],
                            ['title' => 'Crie um novo documento', 'detail' => 'Informe data, título e conteúdo com linguagem objetiva e padronizada.'],
                            ['title' => 'Revise antes de concluir', 'detail' => 'Confirme nomes, funções, datas e decisões registradas para evitar retrabalho.'],
                            ['title' => 'Use a listagem para consultas futuras', 'detail' => 'Com o histórico organizado, o clube ganha rastreabilidade administrativa.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'pedagogico',
                'title' => 'Pedagógico',
                'badge' => 'Formação',
                'summary' => 'Gestão da evolução em classes, especialidades e frequência.',
                'sections' => [
                    [
                        'title' => 'Classes (Requisitos)',
                        'description' => 'Acompanha o progresso individual por requisito.',
                        'can_do' => ['Visualizar classes', 'Marcar requisitos cumpridos', 'Consultar progresso'],
                        'steps' => [
                            ['title' => 'Acesse Pedagógico > Classes', 'detail' => 'Selecione a classe desejada para visualizar os requisitos aplicáveis.'],
                            ['title' => 'Abra o desbravador ou grupo', 'detail' => 'Filtre pelo participante para focar no progresso individual.'],
                            ['title' => 'Marque requisitos concluídos', 'detail' => 'Atualize no momento da validação para manter o status sempre confiável.'],
                            ['title' => 'Revise pendências', 'detail' => 'Use os itens não concluídos para planejar as próximas atividades do módulo.'],
                        ],
                    ],
                    [
                        'title' => 'Especialidades',
                        'description' => 'Controla especialidades disponíveis e vínculos por membro.',
                        'can_do' => ['Cadastrar especialidades', 'Vincular especialidade a desbravador', 'Remover vínculos incorretos'],
                        'steps' => [
                            ['title' => 'Abra Pedagógico > Especialidades', 'detail' => 'Confira a lista existente e cadastre novas quando necessário.'],
                            ['title' => 'Entre no perfil do desbravador', 'detail' => 'Use a gestão de especialidades dentro do cadastro para vincular corretamente.'],
                            ['title' => 'Salve e valide no histórico', 'detail' => 'Confirme se o vínculo foi registrado para não perder rastreabilidade formativa.'],
                            ['title' => 'Mantenha nomenclatura padronizada', 'detail' => 'Evite variações de nome para não duplicar especialidades equivalentes.'],
                        ],
                    ],
                    [
                        'title' => 'Frequência',
                        'description' => 'Registra presença e constrói histórico mensal.',
                        'can_do' => ['Abrir nova chamada', 'Marcar presença', 'Acompanhar histórico mensal'],
                        'steps' => [
                            ['title' => 'Acesse Pedagógico > Frequência > Nova Chamada', 'detail' => 'Selecione a data correta e confirme o grupo de participantes.'],
                            ['title' => 'Marque presença/ausência', 'detail' => 'Registre cada membro com cuidado para manter indicadores reais de participação.'],
                            ['title' => 'Salve a chamada', 'detail' => 'Após salvar, valide se os totais da chamada estão coerentes.'],
                            ['title' => 'Consulte histórico mensal', 'detail' => 'Use o histórico para identificar constância e apoiar ações de acompanhamento.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'financeiro',
                'title' => 'Financeiro',
                'badge' => 'Controle',
                'summary' => 'Caixa, mensalidades e patrimônio com visão de gestão.',
                'sections' => [
                    [
                        'title' => 'Caixa (Entradas e Saídas)',
                        'description' => 'Consolida movimentações financeiras do clube.',
                        'can_do' => ['Registrar receita', 'Registrar despesa', 'Acompanhar saldo'],
                        'steps' => [
                            ['title' => 'Abra Financeiro > Caixa', 'detail' => 'Visualize as movimentações já registradas e o saldo consolidado.'],
                            ['title' => 'Cadastre nova movimentação', 'detail' => 'Defina tipo (entrada/saída), valor, data e descrição objetiva.'],
                            ['title' => 'Revise informações antes de salvar', 'detail' => 'Pequenos erros de valor ou tipo impactam relatórios e fechamento mensal.'],
                            ['title' => 'Use filtros para conferência', 'detail' => 'Filtre por período para facilitar prestação de contas e reuniões.'],
                        ],
                    ],
                    [
                        'title' => 'Mensalidades',
                        'description' => 'Gera cobranças e registra pagamentos.',
                        'can_do' => ['Gerar mensalidades em lote', 'Marcar pagamento', 'Acompanhar inadimplência'],
                        'steps' => [
                            ['title' => 'Acesse Financeiro > Mensalidades', 'detail' => 'Confira competências e status antes de novas ações em lote.'],
                            ['title' => 'Execute geração massiva quando necessário', 'detail' => 'Use a ação de gerar para criar lançamentos para múltiplos membros.'],
                            ['title' => 'Registre pagamentos confirmados', 'detail' => 'Marque apenas após confirmação, mantendo histórico financeiro confiável.'],
                            ['title' => 'Acompanhe pendências', 'detail' => 'Use a listagem para orientar cobrança com comunicação organizada.'],
                        ],
                    ],
                    [
                        'title' => 'Patrimônio',
                        'description' => 'Inventário dos bens e recursos materiais do clube.',
                        'can_do' => ['Cadastrar item patrimonial', 'Editar status/descrição', 'Controlar inventário'],
                        'steps' => [
                            ['title' => 'Abra Financeiro > Patrimônio', 'detail' => 'Veja a base atual de itens inventariados.'],
                            ['title' => 'Cadastre novo item', 'detail' => 'Preencha categoria, descrição e dados de controle patrimonial.'],
                            ['title' => 'Atualize quando houver mudança', 'detail' => 'Em caso de baixa, manutenção ou troca, ajuste imediatamente no sistema.'],
                            ['title' => 'Use para auditoria interna', 'detail' => 'Relatórios patrimoniais dependem da consistência desses registros.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'eventos',
                'title' => 'Eventos e Agenda',
                'badge' => 'Planejamento',
                'summary' => 'Criação de eventos, inscrições e acompanhamento de participantes.',
                'sections' => [
                    [
                        'title' => 'Criação de Eventos',
                        'description' => 'Configura atividades e calendário do clube.',
                        'can_do' => ['Criar evento', 'Editar evento', 'Excluir evento'],
                        'steps' => [
                            ['title' => 'Acesse Agenda > Eventos', 'detail' => 'Use a listagem para acompanhar próximos compromissos.'],
                            ['title' => 'Clique em criar novo evento', 'detail' => 'Preencha nome, período, local e informações de participação.'],
                            ['title' => 'Salve e valide dados principais', 'detail' => 'Confirme se data e regras de inscrição estão corretas antes de divulgar.'],
                            ['title' => 'Edite quando houver alteração', 'detail' => 'Atualize imediatamente para evitar divergência na comunicação da equipe.'],
                        ],
                    ],
                    [
                        'title' => 'Inscrições',
                        'description' => 'Gerencia participantes individualmente ou em lote.',
                        'can_do' => ['Inscrever desbravador', 'Inscrever em lote', 'Remover inscrição'],
                        'steps' => [
                            ['title' => 'Abra o evento desejado', 'detail' => 'Dentro do evento, use o bloco de participantes para controlar inscrições.'],
                            ['title' => 'Inscreva individualmente ou em lote', 'detail' => 'Escolha o formato conforme volume de participantes.'],
                            ['title' => 'Revise lista final', 'detail' => 'Garanta que não haja duplicidade e que todos os nomes esperados estejam presentes.'],
                            ['title' => 'Remova inscrições indevidas', 'detail' => 'Use a remoção para corrigir trocas ou desistências.'],
                        ],
                    ],
                    [
                        'title' => 'Documentação e Status',
                        'description' => 'Acompanha situação de inscrição e geração de autorização.',
                        'can_do' => ['Atualizar status da inscrição', 'Emitir autorização', 'Conferir situação final'],
                        'steps' => [
                            ['title' => 'Atualize status dos inscritos', 'detail' => 'Quando aplicável, ajuste os status para refletir situação financeira/participação.'],
                            ['title' => 'Gere autorização individual', 'detail' => 'Emita o documento diretamente da tela do evento para agilizar operação.'],
                            ['title' => 'Confirme dados antes da impressão', 'detail' => 'Valide nome do desbravador, evento e responsável.'],
                            ['title' => 'Use status para controle pré-evento', 'detail' => 'Essa revisão evita problemas de última hora na saída.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'relatorios',
                'title' => 'Relatórios',
                'badge' => 'Análise',
                'summary' => 'Geração de documentos para acompanhamento técnico e administrativo.',
                'sections' => [
                    [
                        'title' => 'Acesso ao Hub de Relatórios',
                        'description' => 'Ponto central para emissão de documentos do sistema.',
                        'can_do' => ['Abrir catálogo de relatórios', 'Escolher tipo de saída', 'Gerar documentos operacionais'],
                        'steps' => [
                            ['title' => 'Acesse Relatórios no menu', 'detail' => 'Entre no hub e escolha o relatório adequado para sua necessidade.'],
                            ['title' => 'Selecione o tipo de relatório', 'detail' => 'Escolha entre opções administrativas, médicas, financeiras e de identificação.'],
                            ['title' => 'Defina filtros quando necessário', 'detail' => 'Período e critérios corretos melhoram precisão da informação.'],
                            ['title' => 'Gere e valide o conteúdo', 'detail' => 'Antes de compartilhar, faça conferência rápida de nomes e totais.'],
                        ],
                    ],
                    [
                        'title' => 'Relatórios por Desbravador',
                        'description' => 'Documentos individuais para uso em reuniões, eventos e arquivo.',
                        'can_do' => ['Gerar carteirinha', 'Gerar autorização', 'Emitir ficha médica'],
                        'steps' => [
                            ['title' => 'Localize o desbravador', 'detail' => 'Acesse o perfil ou use os atalhos do módulo de relatórios.'],
                            ['title' => 'Escolha o documento individual', 'detail' => 'Selecione a saída desejada conforme contexto da atividade.'],
                            ['title' => 'Gere o arquivo', 'detail' => 'Aguarde processamento e abra o documento para validação.'],
                            ['title' => 'Compartilhe com a equipe responsável', 'detail' => 'Garanta uso da versão mais recente do documento.'],
                        ],
                    ],
                    [
                        'title' => 'Relatórios Financeiros e Patrimoniais',
                        'description' => 'Visão consolidada para gestão e prestação de contas.',
                        'can_do' => ['Gerar financeiro', 'Gerar patrimônio', 'Conferir indicadores'],
                        'steps' => [
                            ['title' => 'Abra a seção financeira do hub', 'detail' => 'Disponível para perfis com permissão de financeiro.'],
                            ['title' => 'Defina período e critérios', 'detail' => 'Filtros corretos garantem coerência com a movimentação real.'],
                            ['title' => 'Gere relatório consolidado', 'detail' => 'Use o resultado para reuniões de gestão e acompanhamento de metas.'],
                            ['title' => 'Armazene histórico de prestações', 'detail' => 'Mantenha rotina de geração para facilitar comparação mensal.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'master',
                'title' => 'Admin Master',
                'badge' => 'Segurança',
                'summary' => 'Controle de acesso, convites e políticas de backup/restauração.',
                'sections' => [
                    [
                        'title' => 'Gestão de Acessos',
                        'description' => 'Administra usuários e níveis de permissão.',
                        'can_do' => ['Criar usuário', 'Editar permissões', 'Remover acessos quando necessário'],
                        'steps' => [
                            ['title' => 'Acesse Admin Master > Gestão de Acessos', 'detail' => 'Visualize usuários ativos e seus respectivos perfis de autorização.'],
                            ['title' => 'Crie ou ajuste usuários', 'detail' => 'Defina papel correto para evitar acessos indevidos a módulos sensíveis.'],
                            ['title' => 'Revise permissões periodicamente', 'detail' => 'Mudanças de função devem refletir imediatamente no sistema.'],
                            ['title' => 'Remova acessos inativos', 'detail' => 'Mantenha política de segurança limpa e auditável.'],
                        ],
                    ],
                    [
                        'title' => 'Convites',
                        'description' => 'Entrada controlada de novos usuários por convite.',
                        'can_do' => ['Criar convite', 'Acompanhar pendências', 'Cancelar convite'],
                        'steps' => [
                            ['title' => 'Abra o módulo de convites', 'detail' => 'Gerencie entrada de novos perfis sem liberar cadastro aberto.'],
                            ['title' => 'Informe e-mail e função', 'detail' => 'Defina permissões corretas já no convite inicial.'],
                            ['title' => 'Monitore status do convite', 'detail' => 'Acompanhe aceite pendente, expirado ou concluído.'],
                            ['title' => 'Cancele convites incorretos', 'detail' => 'Evita tentativas de cadastro com permissões indevidas.'],
                        ],
                    ],
                    [
                        'title' => 'Backup e Restauração',
                        'description' => 'Protege dados e permite recuperação em incidentes.',
                        'can_do' => ['Gerar backup', 'Importar backup', 'Restaurar ambiente'],
                        'steps' => [
                            ['title' => 'Acesse Admin Master > Backups', 'detail' => 'Use a tela para ações de segurança e continuidade operacional.'],
                            ['title' => 'Gere backup recorrente', 'detail' => 'Crie rotina periódica para reduzir risco de perda de dados.'],
                            ['title' => 'Valide arquivo antes de restaurar', 'detail' => 'Confirme origem e data para não sobrescrever dados válidos.'],
                            ['title' => 'Restaure somente em cenário controlado', 'detail' => 'Preferencialmente com alinhamento da equipe responsável.'],
                        ],
                    ],
                ],
            ],
        ];

        $sectionImageMap = [
            'secretaria' => [
                ['dashboard', 'club-edit', 'club-edit', 'dashboard'],
                ['desbravadores-index', 'desbravadores-create', 'desbravadores-show', 'desbravadores-index'],
                ['atas-index', 'atas-create', 'atos-index', 'atos-create'],
            ],
            'pedagogico' => [
                ['classes-index', 'classes-show', 'classes-show', 'classes-index'],
                ['especialidades-index', 'especialidades-create', 'desbravadores-show', 'especialidades-index'],
                ['frequencia-create', 'frequencia-create', 'frequencia-index', 'frequencia-index'],
            ],
            'financeiro' => [
                ['caixa-index', 'caixa-create', 'caixa-create', 'caixa-index'],
                ['mensalidades-index', 'mensalidades-index', 'mensalidades-index', 'mensalidades-index'],
                ['patrimonio-index', 'patrimonio-create', 'patrimonio-index', 'relatorios-index'],
            ],
            'eventos' => [
                ['eventos-index', 'eventos-create', 'eventos-create', 'eventos-index'],
                ['eventos-show', 'eventos-show', 'eventos-index', 'eventos-show'],
                ['eventos-show', 'eventos-show', 'eventos-show', 'eventos-index'],
            ],
            'relatorios' => [
                ['relatorios-index', 'relatorios-index', 'relatorios-index', 'relatorios-index'],
                ['desbravadores-show', 'relatorios-index', 'relatorios-index', 'relatorios-index'],
                ['relatorios-index', 'relatorios-index', 'relatorios-index', 'relatorios-index'],
            ],
            'master' => [
                ['usuarios-index', 'usuarios-index', 'usuarios-index', 'usuarios-index'],
                ['invites-index', 'invites-create', 'invites-index', 'invites-index'],
                ['backups-index', 'backups-index', 'backups-index', 'backups-index'],
            ],
        ];
    @endphp

    <div class="py-6 sm:py-8 lg:py-10" x-data="{
        selected: 'secretaria',
        showTopButton: false,
        scrollContainer: null,
        init() {
            this.scrollContainer = document.querySelector('main.overflow-y-auto');

            if (this.scrollContainer) {
                this.showTopButton = this.scrollContainer.scrollTop > 420;
                this.scrollContainer.addEventListener('scroll', () => {
                    this.showTopButton = this.scrollContainer.scrollTop > 420;
                });
                return;
            }

            this.showTopButton = window.scrollY > 420;
            window.addEventListener('scroll', () => {
                this.showTopButton = window.scrollY > 420;
            });
        },
        scrollToTop() {
            if (this.scrollContainer) {
                this.scrollContainer.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <section class="rounded-3xl border border-blue-100 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
                <div class="p-5 sm:p-8 lg:p-10 bg-gradient-to-br from-dbv-blue/[0.05] via-transparent to-dbv-yellow/[0.10]">
                    <p class="inline-flex items-center rounded-full border border-blue-200 dark:border-blue-700 px-3 py-1 text-xs font-bold uppercase tracking-widest text-dbv-blue dark:text-blue-300">
                        Manual detalhado por módulo
                    </p>
                    <h1 class="mt-3 text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white">
                        Selecione seu módulo e siga o passo a passo completo
                    </h1>
                    <p class="mt-3 text-sm sm:text-base text-gray-600 dark:text-gray-300 max-w-4xl leading-relaxed">
                        Este manual foi estruturado para treinamento e consulta rápida. Cada módulo possui ações que o usuário pode executar, com fluxo operacional detalhado.
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 sm:p-6 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">1. Escolha seu módulo</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach ($modules as $module)
                        <button type="button"
                            @click="selected = '{{ $module['key'] }}'"
                            :class="selected === '{{ $module['key'] }}' ? 'border-dbv-blue bg-blue-50 dark:bg-blue-900/25 shadow-sm' : 'border-gray-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-700'"
                            class="text-left rounded-2xl border p-4 transition-all duration-200">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm sm:text-base font-extrabold text-gray-900 dark:text-white">{{ $module['title'] }}</h3>
                                <span class="text-[10px] uppercase tracking-wider font-black text-dbv-blue dark:text-blue-300">{{ $module['badge'] }}</span>
                            </div>
                            <p class="mt-2 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $module['summary'] }}</p>
                        </button>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/30">
                    <p class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">2. Manual detalhado do módulo selecionado</p>
                </div>

                @foreach ($modules as $module)
                    <div x-show="selected === '{{ $module['key'] }}'" x-transition.opacity.duration.200ms class="p-4 sm:p-6 lg:p-8 space-y-5">
                        <div class="rounded-2xl border border-blue-100 dark:border-blue-800 bg-blue-50/60 dark:bg-blue-900/10 p-4 sm:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <h2 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">{{ $module['title'] }}</h2>
                                <span class="text-xs font-bold uppercase tracking-widest text-dbv-blue dark:text-blue-300">{{ $module['badge'] }}</span>
                            </div>
                            <p class="mt-2 text-sm sm:text-base text-gray-700 dark:text-gray-300">{{ $module['summary'] }}</p>
                        </div>

                        <div class="space-y-5">
                            @foreach ($module['sections'] as $sectionIndex => $section)
                                <article class="rounded-2xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900/30 overflow-hidden">
                                    <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-gray-50 to-blue-50/60 dark:from-slate-800 dark:to-slate-700/20">
                                        <p class="text-[11px] font-black uppercase tracking-widest text-dbv-blue dark:text-blue-300">Fluxo {{ $sectionIndex + 1 }}</p>
                                        <h3 class="mt-1 text-lg sm:text-xl font-extrabold text-gray-900 dark:text-white">{{ $section['title'] }}</h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $section['description'] }}</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($section['can_do'] as $action)
                                                <span class="inline-flex items-center rounded-full bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-600 px-3 py-1 text-xs font-semibold text-gray-700 dark:text-gray-200">
                                                    {{ $action }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="p-4 sm:p-5 space-y-3">
                                        @foreach ($section['steps'] as $stepIndex => $step)
                                            @php
                                                $imageKey = $sectionImageMap[$module['key']][$sectionIndex][$stepIndex] ?? 'dashboard';
                                                $imagePath = asset('images/manual/' . $imageKey . '.png');
                                            @endphp
                                            <div class="rounded-xl border border-gray-200 dark:border-slate-700 p-3 sm:p-4">
                                                <div class="flex items-start gap-3">
                                                    <span class="h-8 w-8 shrink-0 rounded-lg bg-dbv-blue text-white text-sm font-black grid place-items-center">
                                                        {{ $stepIndex + 1 }}
                                                    </span>
                                                    <div class="min-w-0">
                                                        <h4 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100">{{ $step['title'] }}</h4>
                                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $step['detail'] }}</p>
                                                    </div>
                                                </div>

                                                <div class="mt-3 rounded-lg border border-blue-200 dark:border-blue-800 bg-gradient-to-br from-blue-50 to-yellow-50 dark:from-slate-800 dark:to-slate-700 p-3">
                                                    <p class="text-[11px] font-black uppercase tracking-wider text-dbv-blue dark:text-blue-300 mb-2">Print real da tela</p>
                                                    <img src="{{ $imagePath }}"
                                                        alt="Print do sistema para {{ $module['title'] }} - {{ $section['title'] }} - passo {{ $stepIndex + 1 }}"
                                                        class="w-full rounded-lg border border-blue-100 dark:border-slate-600 shadow-sm">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </section>
        </div>

        <button type="button" x-show="showTopButton" x-transition.opacity.duration.200ms @click="scrollToTop()"
            class="fixed bottom-5 right-5 sm:bottom-7 sm:right-7 z-40 inline-flex items-center gap-2 rounded-full bg-white/90 dark:bg-slate-800/90 border border-gray-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-dbv-blue dark:text-blue-200 shadow-lg backdrop-blur hover:bg-white dark:hover:bg-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            </svg>
            Topo
        </button>
    </div>
</x-app-layout>
