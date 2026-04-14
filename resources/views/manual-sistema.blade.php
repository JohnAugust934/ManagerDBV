<x-app-layout>
    <x-slot name="header">Manual do Sistema</x-slot>

    <div class="ui-page max-w-4xl mx-auto space-y-8 ui-animate-fade-up pb-20">

        {{-- Capa --}}
        <div class="ui-card overflow-hidden">
            <div class="p-8 sm:p-12 bg-gradient-to-br from-[#002F6C] to-blue-600 relative overflow-hidden">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white to-transparent"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 rounded-3xl bg-white/20 border border-white/20 flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-white leading-tight mb-3">Manual do Sistema</h1>
                    <p class="text-blue-200 font-medium text-lg">Guia completo do Desbravadores Manager</p>
                </div>
            </div>

            {{-- Índice Rápido --}}
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Índice</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach(['Módulo de Secretaria','Módulo Pedagógico','Frequência e Chamadas','Gestão de Eventos','Financeiro e Caixa','Relatórios e Documentos'] as $secao)
                    <a href="#{{ Str::slug($secao) }}" class="flex items-center gap-2.5 px-4 py-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 hover:border-[#002F6C]/40 dark:hover:border-blue-500/40 hover:bg-[#002F6C]/5 dark:hover:bg-blue-500/10 transition-all group">
                        <svg class="w-4 h-4 text-[#002F6C] dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transition-colors">{{ $secao }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Seções do Manual --}}
        @php
        $secoes = [
            ['id' => 'modulo-de-secretaria', 'titulo' => 'Módulo de Secretaria', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'items' => [
                ['titulo' => 'Cadastro de Desbravadores', 'desc' => 'Acesse Secretaria → Desbravadores → Novo. Preencha os dados pessoais obrigatórios (nome, data de nascimento) e opcionais (foto, contato dos pais). O sistema suporta upload de foto de perfil.'],
                ['titulo' => 'Desbravador s/ Foto', 'desc' => 'Quando não há foto cadastrada, o avatar mostra automaticamente a inicial do nome do membro. Nenhuma ação necessária.'],
                ['titulo' => 'Unidades e Contagem', 'desc' => 'A contagem de membros de cada unidade é calculada em tempo real via relação do banco de dados. Vincule o desbravador à unidade na tela de edição do membro.'],
            ]],
            ['id' => 'modulo-pedagogico', 'titulo' => 'Módulo Pedagógico', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'items' => [
                ['titulo' => 'Classes e Requisitos', 'desc' => 'No módulo Pedagógico → Classes você pode acompanhar o progresso de cada aluno por classe. Clique no cartão do aluno para abrir o Drawer lateral com o checklist completo de requisitos.'],
                ['titulo' => 'Aula em Lote', 'desc' => 'Na aba "Aula em Lote", selecione o requisito ensinado na aula e marque/desmarque todos os alunos simultaneamente. As alterações são salvas automaticamente via AJAX sem recarregar a página.'],
                ['titulo' => 'Especialidades', 'desc' => 'Cadastre especialidades com área/categoria. O sistema aplica automaticamente cores temáticas por área (Natureza=verde, Saúde=vermelho, etc.) para fácil identificação visual.'],
            ]],
            ['id' => 'frequencia-e-chamadas', 'titulo' => 'Frequência e Chamadas', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'items' => [
                ['titulo' => 'Realizando uma Chamada', 'desc' => 'Vá em Frequência → Nova Chamada. Selecione a data e, opcionalmente, filtre por unidade. Marque as checkboxes de cada critério (Presente, Pontual, Bíblia, Uniforme) e clique em "Salvar Chamada".'],
                ['titulo' => 'Colunas Personalizadas', 'desc' => 'Usuários com permissão podem acessar "Gerenciar Colunas" para criar critérios customizados com pontuação própria, substituindo os campos padrão.'],
                ['titulo' => 'Histórico e Percentual', 'desc' => 'No histórico mensal, o percentual de frequência é colorido automaticamente: verde (≥75%), amarelo (≥50%) e vermelho (<50%) para rápida identificação de membros em risco.'],
            ]],
            ['id' => 'gestao-de-eventos', 'titulo' => 'Gestão de Eventos', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'items' => [
                ['titulo' => 'Criando um Evento', 'desc' => 'Acesse Eventos → Novo Evento. Preencha nome, local, datas e valor. Eventos gratuitos exibem badge "Gratuito" em destaque verde.'],
                ['titulo' => 'Inscrições e Pagamentos', 'desc' => 'Na tela de gerenciamento do evento, inscreva membros individualmente ou em lote. O botão "PENDENTE/PAGO" é interativo e atualiza o status via AJAX sem recarregar a página.'],
                ['titulo' => 'Autorização Parental', 'desc' => 'Para cada inscrito, clique no ícone de documento para gerar automaticamente a autorização parental em PDF pronta para impressão.'],
            ]],
            ['id' => 'financeiro-e-caixa', 'titulo' => 'Financeiro e Caixa', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'items' => [
                ['titulo' => 'Fluxo de Caixa', 'desc' => 'Registre entradas e saídas manualmente em Financeiro → Caixa. O saldo é calculado automaticamente. Vermelho indica saldo negativo.'],
                ['titulo' => 'Mensalidades', 'desc' => 'Gere o carnê mensal para todos os membros de uma vez via "Gerar Carnê do Mês". Defina valor e período. Use os filtros de mês/ano para navegar entre períodos.'],
                ['titulo' => 'Recebimento', 'desc' => 'Clique em "Receber" em qualquer mensalidade pendente. O sistema confirma o valor e registra automaticamente como entrada no caixa.'],
            ]],
            ['id' => 'relatorios-e-documentos', 'titulo' => 'Relatórios e Documentos', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'items' => [
                ['titulo' => 'Documentos Disponíveis', 'desc' => 'Em Relatórios, gere: Autorização de Evento, Carteirinha do Clube, Ficha Médica e Relatório Financeiro. Todos em formato PDF otimizado para impressão.'],
                ['titulo' => 'Relatório Personalizado', 'desc' => 'Use "Gerar Personalizado" para filtrar membros por critérios específicos (unidade, classe, status) e exportar como lista.'],
            ]],
        ];
        @endphp

        @foreach ($secoes as $secao)
        <div id="{{ $secao['id'] }}" class="ui-card overflow-hidden scroll-mt-24">
            <div class="flex items-center gap-4 px-6 sm:px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <div class="w-10 h-10 rounded-xl bg-[#002F6C]/10 dark:bg-blue-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#002F6C] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $secao['icon'] }}"/></svg>
                </div>
                <h2 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">{{ $secao['titulo'] }}</h2>
            </div>
            <div class="p-6 sm:p-8 space-y-5">
                @foreach ($secao['items'] as $item)
                <div class="flex gap-4">
                    <div class="w-1.5 shrink-0 mt-1">
                        <div class="w-1.5 h-1.5 rounded-full bg-[#D9222A]"></div>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white mb-1 uppercase tracking-tight">{{ $item['titulo'] }}</h3>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400 leading-relaxed">{{ $item['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

    </div>


    {{-- BOTÃO VOLTAR AO TOPO --}}
    {{-- x-teleport move o botão para o <body>, fora do <main overflow-y-auto> --}}
    {{-- Sem isso o position:fixed fica preso no container de scroll --}}
    <template
        x-data="{ visible: false }"
        x-init="
            const el = document.getElementById('app-content');
            el.addEventListener('scroll', () => { visible = el.scrollTop > 250; }, { passive: true });
        "
        x-teleport="body">
        <button
            x-show="visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-3"
            @click="document.getElementById('app-content').scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-24 sm:bottom-8 right-5 sm:right-8 z-[9999] w-12 h-12 bg-[#002F6C] hover:bg-[#001D42] dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-full shadow-xl shadow-blue-900/30 flex items-center justify-center active:scale-90 transition-colors"
            aria-label="Voltar ao topo"
            title="Voltar ao topo"
            style="display:none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
    </template>

</x-app-layout>

