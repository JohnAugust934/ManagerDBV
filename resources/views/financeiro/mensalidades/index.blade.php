<x-app-layout>
    <x-slot name="header">Financeiro & Mensalidades</x-slot>

    <div class="ui-page space-y-6 max-w-7xl mx-auto ui-animate-fade-up" x-data="{
        modalPagamentoOpen: false,
        modalGerarOpen: false,
        pagamentoUrl: '',
        nomeDesbravador: '',
        valorMensalidade: '',
        openPagamento(url, nome, valor) {
            this.pagamentoUrl = url;
            this.nomeDesbravador = nome;
            this.valorMensalidade = valor;
            this.modalPagamentoOpen = true;
        }
    }">

        <!-- Control Bar -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-[#002F6C] dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Painel do Mês
            </h2>

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
                    Lançar Novo Lote
                </button>
            </form>
        </div>

        <!-- Estatísticas Top Level -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Entradas do Mês -->
            <div class="ui-card relative overflow-hidden p-6 border-b-4 border-emerald-500 dark:border-emerald-500/50 bg-gradient-to-b from-white to-emerald-50/50 dark:from-slate-900 dark:to-emerald-900/10">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500 rounded-full blur-2xl opacity-10 dark:opacity-20"></div>
                <div class="flex justify-between items-start mb-4">
                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Caixa do Mês (Pago)</p>
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">R$ <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($valorRecebido, 2, ',', '.') }}</span></h3>
                <div class="mt-4 flex items-center gap-2">
                    <span class="px-2 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 text-[10px] font-black rounded-lg">{{ $totalPago }}</span>
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
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">R$ <span class="text-amber-500 dark:text-amber-400">{{ number_format($valorPendente, 2, ',', '.') }}</span></h3>
                <div class="mt-4 flex items-center gap-2">
                    <span class="px-2 py-1 bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400 text-[10px] font-black rounded-lg">{{ $totalPendente }}</span>
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

        <!-- Área de Listagem (Grid) -->
        <h3 class="text-[13px] font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-black/5 dark:border-white/5 pb-2">Status Individual</h3>
        
        @if ($mensalidades->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($mensalidades as $m)
                <div class="ui-card p-0 overflow-hidden border border-slate-100 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col pt-4">
                    
                    <!-- dbv header info -->
                    <div class="px-5 flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full border-2 border-slate-100 dark:border-slate-800 flex items-center justify-center bg-slate-50 dark:bg-slate-900 font-black text-lg text-slate-400 shrink-0">
                            {{ mb_strtoupper(substr($m->desbravador->nome, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-sm text-slate-800 dark:text-white truncate" title="{{ $m->desbravador->nome }}">{{ $m->desbravador->nome }}</p>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $m->desbravador->unidade->nome ?? 'Sem Unidade' }}</span>
                        </div>
                    </div>

                    <!-- payment value and status core -->
                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/50 mx-4 rounded-xl flex items-center justify-between border {{ $m->status === 'pago' ? 'border-emerald-100 dark:border-emerald-900/30' : 'border-amber-100 dark:border-amber-900/30' }}">
                        <p class="font-black text-lg {{ $m->status === 'pago' ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300' }}">
                            R$ {{ number_format($m->valor, 2, ',', '.') }}
                        </p>
                        
                        @if ($m->status === 'pago')
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 rounded uppercase font-black text-[9px] tracking-widest flex items-center gap-1">
                                Pago <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 rounded uppercase font-black text-[9px] tracking-widest flex items-center gap-1">
                                Pendente
                            </span>
                        @endif
                    </div>
                    
                    <!-- action area -->
                    <div class="mt-auto pt-4 pb-4 px-4">
                        @if ($m->status === 'pendente')
                            <button @click="openPagamento('{{ route('mensalidades.pagar', $m->id) }}', '{{ $m->desbravador->nome }}', '{{ number_format($m->valor, 2, ',', '.') }}')" 
                                    class="w-full h-11 bg-slate-800 hover:bg-slate-900 dark:bg-white dark:hover:bg-slate-200 dark:text-slate-900 text-white font-black text-[11px] uppercase tracking-widest rounded-xl transition-all shadow shadow-slate-900/10 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Confirmar Recebimento
                            </button>
                        @else
                            <div class="w-full h-11 border border-emerald-100 dark:border-emerald-900/30 bg-emerald-50 dark:bg-emerald-900/10 flex items-center justify-center rounded-xl text-emerald-600 dark:text-emerald-500 font-bold text-[11px] tracking-wide">
                                Quitada em {{ \Carbon\Carbon::parse($m->data_pagamento)->format('d/m/Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class="ui-card p-12 flex flex-col items-center justify-center text-center border-dashed border-2 border-slate-200 dark:border-slate-800 bg-transparent shadow-none">
            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">Nenhuma cobrança registrada neste mês</h3>
            <p class="text-sm font-bold text-slate-400 mb-6 max-w-md">Utilize o controle de "Lançar Novo Lote" para rodar a mensalidade padrão do mês para todos da base num clique mágico.</p>
            <button @click="modalGerarOpen = true" class="ui-btn-primary flex items-center gap-2">
                Começar a Gerar Carnês
            </button>
        </div>
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
                        <form :action="pagamentoUrl" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full px-6 py-3 rounded-xl font-black text-sm bg-emerald-600 hover:bg-emerald-500 text-white transition-all shadow-lg shadow-emerald-900/20 active:scale-95 flex justify-center items-center gap-2">
                                Confirmar Recebimento
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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

                    <form action="{{ route('mensalidades.gerar') }}" method="POST">
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

                            <div class="space-y-5">
                                <div>
                                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Competência (Mês/Ano)</label>
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <select name="mes" class="ui-input w-full appearance-none font-bold pr-8 cursor-pointer">
                                                @foreach (range(1, 12) as $m)
                                                    <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>{{ $m }} - {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                                        </div>
                                        <div class="relative w-1/3">
                                            <select name="ano" class="ui-input w-full appearance-none font-bold pr-8 cursor-pointer text-center">
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
                                        <input type="number" name="valor" step="0.01" value="15.00" required class="ui-input w-full pl-12 h-14 font-black text-2xl text-slate-800 dark:text-white transition-all group-hover:border-[#002F6C] focus:border-[#002F6C]">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 sm:px-8 py-5 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row gap-3 justify-end items-center">
                            <button type="button" @click="modalGerarOpen = false" class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm bg-[#002F6C] hover:bg-[#001D42] dark:bg-blue-600 dark:hover:bg-blue-500 text-white transition-all shadow-lg shadow-blue-900/20 active:scale-95 flex justify-center items-center gap-2">
                                Disparar Cobranças
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>
