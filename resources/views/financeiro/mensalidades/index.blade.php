<x-app-layout>

    <div class="ui-page space-y-6 ui-animate-fade-up" x-data="{
        visualizacao: localStorage.getItem('mensalidades_viz') ?? 'cards',
        setViz(v) { this.visualizacao = v; localStorage.setItem('mensalidades_viz', v); },
        modalPagamentoOpen: false,
        modalGerarOpen: false,
        pagamentoUrl: '',
        nomeDesbravador: '',
        valorMensalidade: '',
        processando: false,
        openPagamento(url, nome, valor) {
            this.pagamentoUrl = url;
            this.nomeDesbravador = nome;
            this.valorMensalidade = valor;
            this.modalPagamentoOpen = true;
        },
        async confirmarPagamento() {
            if (this.processando) return;
            this.processando = true;
            try {
                const res = await fetch(this.pagamentoUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    window.notify(data.message || 'Não foi possível registrar o pagamento.', 'error');
                    return;
                }
                // Troca o elemento pelo HTML atualizado — usa row ou card conforme a visualização ativa.
                const card = document.getElementById('mensalidade-card-' + data.id);
                const html = (this.visualizacao === 'linhas' && data.row) ? data.row : data.card;
                if (card && html) card.outerHTML = html;
                // Reflete os totais recalculados no servidor.
                if (data.resumo) {
                    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
                    set('resumo-recebido', data.resumo.valorRecebido);
                    set('resumo-pendente', data.resumo.valorPendente);
                    set('resumo-total-pago', data.resumo.totalPago);
                    set('resumo-total-pendente', data.resumo.totalPendente);
                }
                this.modalPagamentoOpen = false;
                window.notify(data.message || 'Pagamento registrado!', 'success');
            } catch (e) {
                window.notify('Falha de conexão ao registrar o pagamento.', 'error');
            } finally {
                this.processando = false;
            }
        }
    }">

        <x-page-title title="Controle de Mensalidades" subtitle="Acompanhe pagamentos, inadimplência e o painel do mês.">
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-slot:icon>
        </x-page-title>

        <!-- Control Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-end gap-4">
            <form method="GET" action="{{ route('mensalidades.index') }}" class="flex flex-wrap md:flex-nowrap gap-2 w-full md:w-auto">
                <div class="relative flex-1 md:w-48">
                    <select name="mes" onchange="this.form.submit()" class="ui-input appearance-none w-full font-black text-slate-700 dark:text-white bg-slate-50 dark:bg-slate-900 border-none shadow-sm pr-10">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>
                                {{ mb_strtoupper(\Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div class="relative flex-1 md:w-32">
                    <select name="ano" onchange="this.form.submit()" class="ui-input appearance-none w-full font-black text-slate-700 dark:text-white bg-slate-50 dark:bg-slate-900 border-none shadow-sm pr-10">
                        @foreach (range(date('Y') - 1, date('Y') + 1) as $y)
                            <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                
                <button type="button" @click="modalGerarOpen = true" class="w-full md:w-auto mt-2 md:mt-0 flex items-center justify-center gap-2 px-6 py-3 bg-[#002F6C] hover:bg-[#001D42] dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-black text-xs uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-blue-900/20 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Gerar Carnê do Mês
                </button>
            </form>
        </div>

        <!-- Estatísticas Top Level -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Entradas do Mês -->
            <div class="ui-card relative overflow-hidden p-6 border-b-4 border-emerald-500 dark:border-emerald-500/50 bg-gradient-to-b from-white to-emerald-50/50 dark:from-slate-900 dark:to-emerald-900/10">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500 rounded-full blur-2xl opacity-10 dark:opacity-20"></div>
                <div class="flex justify-between items-start mb-4">
                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Recebido (Mês)</p>
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">R$ <span id="resumo-recebido" class="text-emerald-600 dark:text-emerald-400">{{ number_format($valorRecebido, 2, ',', '.') }}</span></h3>
                <div class="mt-4 flex items-center gap-2">
                    <span id="resumo-total-pago" class="px-2 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 text-[10px] font-black rounded-lg">{{ $totalPago }}</span>
                    <span class="text-[11px] font-bold text-slate-400">membros em dia</span>
                </div>
            </div>

            <!-- Previsão / Pendentes -->
            <div class="ui-card relative overflow-hidden p-6 border-b-4 border-amber-400 dark:border-amber-500/50 bg-gradient-to-b from-white to-amber-50/50 dark:from-slate-900 dark:to-amber-900/10">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-400 rounded-full blur-2xl opacity-10 dark:opacity-20"></div>
                <div class="flex justify-between items-start mb-4">
                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">A Receber no Mês</p>
                    <div class="p-2 bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">R$ <span id="resumo-pendente" class="text-amber-500 dark:text-amber-400">{{ number_format($valorPendente, 2, ',', '.') }}</span></h3>
                <div class="mt-4 flex items-center gap-2">
                    <span id="resumo-total-pendente" class="px-2 py-1 bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400 text-[10px] font-black rounded-lg">{{ $totalPendente }}</span>
                    <span class="text-[11px] font-bold text-slate-400">mensalidades abertas</span>
                </div>
            </div>

            <!-- Dívida Histórica (Alerta Crítico) -->
            <div class="ui-card relative overflow-hidden p-6 border-b-4 border-red-500 dark:border-red-500/50 bg-gradient-to-b from-white to-red-50/50 dark:from-slate-900 dark:to-red-900/10 shadow-lg shadow-red-900/5">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-500 rounded-full blur-2xl opacity-10 dark:opacity-20"></div>
                <div class="flex justify-between items-start mb-4">
                    <p class="text-[11px] font-black text-red-500 dark:text-red-400 uppercase tracking-widest">Inadimplência Histórica</p>
                    <div class="p-2 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">R$ <span class="text-red-600 dark:text-red-500">{{ number_format($totalInadimplenteGeral, 2, ',', '.') }}</span></h3>
                <div class="mt-4 flex items-center gap-2">
                    <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 text-[10px] font-black rounded-lg">{{ $qtdInadimplentes }}</span>
                    <span class="text-[11px] font-bold text-red-500/70 dark:text-red-400/80">cobranças antigas em atraso</span>
                </div>
            </div>
        </div>

        <!-- Painel de Inadimplência por Unidade -->
        @if($inadimplenciaPorUnidade->isNotEmpty())
        <div class="ui-card p-6">
            <h3 class="text-[13px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Inadimplência por Unidade
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($inadimplenciaPorUnidade as $unidadeInfo)
                    <div class="flex items-center justify-between p-4 rounded-2xl border border-red-100 dark:border-red-800/30 bg-red-50 dark:bg-red-900/10">
                        <div>
                            <p class="font-black text-sm text-slate-700 dark:text-slate-200">{{ $unidadeInfo->nome }}</p>
                            <p class="text-xs text-red-500 font-bold mt-0.5">{{ $unidadeInfo->qtd }} pendência{{ $unidadeInfo->qtd !== 1 ? 's' : '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-black text-red-600 dark:text-red-400">R$ {{ number_format($unidadeInfo->total, 2, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Área de Listagem -->
        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-2">
            <h3 class="text-[13px] font-black text-slate-400 uppercase tracking-widest">Status Individual</h3>

            {{-- Toggle cards / linhas (só desktop) --}}
            <div class="hidden sm:flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                <button @click="setViz('cards')"
                    :class="visualizacao === 'cards' ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-widest transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Cards
                </button>
                <button @click="setViz('linhas')"
                    :class="visualizacao === 'linhas' ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-widest transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    Linhas
                </button>
            </div>
        </div>

        @if ($mensalidades->count() > 0)

        {{-- Cards (padrão e mobile) --}}
        <div x-show="visualizacao === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($mensalidades as $m)
                @include('financeiro.mensalidades._card', ['m' => $m])
            @endforeach
        </div>

        {{-- Linhas --}}
        <div x-show="visualizacao === 'linhas'" style="display:none" class="ui-table-wrapper">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Membro</th>
                        <th class="hidden md:table-cell">Unidade</th>
                        <th class="text-right">Valor</th>
                        <th class="text-center hidden sm:table-cell">Status</th>
                        <th class="text-right">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mensalidades as $m)
                        @include('financeiro.mensalidades._row', ['m' => $m])
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <x-empty-state
            title="Nenhuma cobrança registrada neste mês"
            description="Gere o lote mensal para rodar a mensalidade padrão do mês para toda a base num clique.">
            <x-slot:icon>
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
            <x-slot:action>
                <button @click="modalGerarOpen = true" class="ui-btn-primary">Começar a Gerar Carnês</button>
            </x-slot:action>
        </x-empty-state>
        @endif


        {{-- ============================================================= --}}
        {{-- MODAL: CONFIRMAR PAGAMENTO                                     --}}
        {{-- fixed inset-0 garante overlay sobre o viewport inteiro        --}}
        {{-- ============================================================= --}}
        <template x-teleport="body">
            <div x-show="modalPagamentoOpen"
                 style="display:none"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                 role="dialog" aria-modal="true">

                {{-- Overlay --}}
                <div x-show="modalPagamentoOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm"
                     @click="modalPagamentoOpen = false"
                     aria-hidden="true"></div>

                {{-- Painel --}}
                <div x-show="modalPagamentoOpen"
                     x-transition:enter="ease-out duration-250"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                     class="relative ui-card w-full max-w-md p-0 shadow-2xl shadow-black/30 text-left z-10 overflow-hidden">

                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 border border-emerald-200 dark:border-emerald-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Confirmar Recebimento</h3>
                                <p class="text-[11px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Fluxo de Caixa Positivo</p>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-inner">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Membro Pagador</p>
                            <p class="text-xl font-black text-slate-800 dark:text-white truncate mb-4" x-text="nomeDesbravador"></p>
                            <div class="flex justify-between items-end border-t border-slate-200 dark:border-slate-700 pt-3">
                                <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Valor Original</span>
                                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400" x-text="'R$ ' + valorMensalidade"></span>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 sm:px-8 py-5 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row gap-3 justify-end items-center">
                        <button type="button" @click="modalPagamentoOpen = false" class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">
                            Cancelar
                        </button>
                        <form :action="pagamentoUrl" method="POST" class="w-full sm:w-auto" @submit.prevent="confirmarPagamento">
                            @csrf
                            <button type="submit" :disabled="processando" class="w-full px-6 py-3 rounded-xl font-black text-sm bg-emerald-600 hover:bg-emerald-500 text-white transition-all shadow-lg shadow-emerald-900/20 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                                <span x-text="processando ? 'Processando...' : 'Confirmar Recebimento'"></span>
                                <svg x-show="!processando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- ============================================================= --}}
        {{-- MODAL: GERAR CARNÊ LOTE                                        --}}
        {{-- ============================================================= --}}
        <template x-teleport="body">
            <div x-show="modalGerarOpen"
                 style="display:none"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                 role="dialog" aria-modal="true">

                {{-- Overlay --}}
                <div x-show="modalGerarOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm"
                     @click="modalGerarOpen = false"
                     aria-hidden="true"></div>

                {{-- Painel --}}
                <div x-show="modalGerarOpen"
                     x-transition:enter="ease-out duration-250"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                     class="relative ui-card w-full max-w-[480px] p-0 shadow-2xl shadow-black/30 text-left z-10 overflow-hidden">

                    <form action="{{ route('mensalidades.gerar') }}" method="POST" x-data="gerarLoteMensal()">
                        @csrf
                        <div class="p-6 sm:p-8">
                            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                                <div class="w-12 h-12 rounded-2xl bg-[#002F6C]/10 dark:bg-blue-500/20 flex items-center justify-center text-[#002F6C] dark:text-blue-400 shrink-0 border border-[#002F6C]/20 dark:border-blue-500/30">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-black text-xl text-slate-800 dark:text-white tracking-tight">Gerar Lote Mensal</h3>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Massa de cobranças</p>
                                </div>
                            </div>

                            <div class="space-y-5" x-show="step === 'form'">
                                <div>
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Competência (Mês/Ano)</label>
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <select name="mes" x-model.number="mes" class="ui-input w-full appearance-none font-bold pr-8 cursor-pointer">
                                                @foreach (range(1, 12) as $m)
                                                    <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>{{ $m }} - {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                                        </div>
                                        <div class="relative w-1/3">
                                            <select name="ano" x-model.number="ano" class="ui-input w-full appearance-none font-bold pr-8 cursor-pointer text-center">
                                                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                                <option value="{{ date('Y') + 1 }}">{{ date('Y') + 1 }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Valor Base para todos (R$)</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-slate-400 dark:text-slate-500 font-black text-lg">R$</span>
                                        </div>
                                        <input type="number" name="valor" x-model="valor" step="0.01" value="15.00" required class="ui-input w-full pl-12 h-14 font-black text-2xl text-slate-800 dark:text-white transition-all group-hover:border-[#002F6C] focus:border-[#002F6C]">
                                    </div>
                                </div>
                            </div>

                            {{-- Etapa de carregamento --}}
                            <div x-show="step === 'loading'" x-cloak class="py-10 text-center">
                                <svg class="w-8 h-8 mx-auto animate-spin text-[#002F6C] dark:text-blue-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-3">Verificando...</p>
                            </div>

                            {{-- Etapa de confirmação (preview) --}}
                            <div x-show="step === 'confirm'" x-cloak>
                                <template x-if="preview">
                                    <div class="space-y-4">
                                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 space-y-2">
                                            <div class="flex justify-between text-sm"><span class="text-slate-500">Competência</span><span class="font-black text-slate-800 dark:text-white" x-text="preview.competencia"></span></div>
                                            <div class="flex justify-between text-sm"><span class="text-slate-500">Valor unitário</span><span class="font-black text-slate-800 dark:text-white" x-text="preview.valor_formatado"></span></div>
                                            <div class="flex justify-between text-sm"><span class="text-slate-500">Total de membros ativos</span><span class="font-black text-slate-800 dark:text-white" x-text="preview.total_ativos"></span></div>
                                            <div class="flex justify-between text-sm border-t border-slate-200 dark:border-slate-700 pt-2 mt-2"><span class="text-slate-500">Já existem (serão puladas)</span><span class="font-black text-amber-600 dark:text-amber-400" x-text="preview.ja_existem"></span></div>
                                            <div class="flex justify-between text-base font-black"><span class="text-emerald-600 dark:text-emerald-400">Serão criadas agora</span><span class="text-emerald-600 dark:text-emerald-400" x-text="preview.serao_criadas"></span></div>
                                        </div>
                                        <p x-show="preview.serao_criadas === 0" class="text-xs text-amber-600 dark:text-amber-400 font-bold text-center">
                                            Todas as mensalidades desta competência já foram geradas.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="px-6 sm:px-8 py-5 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row gap-3 justify-end items-center">
                            <button type="button" @click="modalGerarOpen = false" class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">
                                Cancelar
                            </button>

                            {{-- Etapa 1: verificar (não submete o form ainda) --}}
                            <button type="button" x-show="step === 'form'" @click="buscarPreview()" class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm bg-[#002F6C] hover:bg-[#001D42] dark:bg-blue-600 dark:hover:bg-blue-500 text-white transition-all shadow-lg shadow-blue-900/20 active:scale-95 flex justify-center items-center gap-2">
                                Verificar e prosseguir
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>

                            {{-- Etapa 2: voltar / confirmar geração (submete) --}}
                            <button type="button" x-show="step === 'confirm'" x-cloak @click="step = 'form'" class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">
                                Voltar
                            </button>
                            <button type="submit" x-show="step === 'confirm'" x-cloak :disabled="!preview || preview.serao_criadas === 0" class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm bg-[#002F6C] hover:bg-[#001D42] dark:bg-blue-600 dark:hover:bg-blue-500 text-white transition-all shadow-lg shadow-blue-900/20 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                                Confirmar geração
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </button>
                        </div>
                    </form>

                    <script>
                        function gerarLoteMensal() {
                            return {
                                step: 'form',
                                preview: null,
                                mes: {{ (int) date('m') }},
                                ano: {{ (int) date('Y') }},
                                valor: '15.00',
                                async buscarPreview() {
                                    this.step = 'loading';
                                    try {
                                        const res = await fetch('{{ route('mensalidades.preview') }}', {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                            body: JSON.stringify({ mes: this.mes, ano: this.ano, valor: this.valor })
                                        });
                                        if (!res.ok) throw new Error('falha');
                                        this.preview = await res.json();
                                        this.step = 'confirm';
                                    } catch (e) {
                                        this.step = 'form';
                                        if (window.notify) window.notify('Erro ao verificar dados. Tente novamente.', 'error');
                                    }
                                }
                            };
                        }
                    </script>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>
