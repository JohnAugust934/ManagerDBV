<x-app-layout>
    <div class="ui-page space-y-6 max-w-[1200px] ui-animate-fade-up" x-data="{ tipo: '' }">

        <x-page-title title="Central de Relatórios" subtitle="Situação atual do clube e geração de PDFs." />

        {{-- Cards de Situação Atual --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 px-4 sm:px-0">

            <div class="ui-card p-5 relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-slate-500/5 dark:bg-white/5"></div>
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Membros Ativos</p>
                    <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center text-sky-600 dark:text-sky-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['membros_ativos'] }}</h3>
                <p class="text-[11px] font-semibold text-slate-400 mt-1">
                    {{ $stats['membros_inativos'] }} inativo{{ $stats['membros_inativos'] !== 1 ? 's' : '' }} no cadastro
                </p>
            </div>

            <div class="ui-card p-5 border-l-4 {{ $stats['saldo_caixa'] >= 0 ? 'border-emerald-500 dark:border-emerald-600' : 'border-red-500 dark:border-red-600' }}">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Saldo do Caixa</p>
                    <div class="w-8 h-8 rounded-xl {{ $stats['saldo_caixa'] >= 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' }} flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <h3 class="text-2xl font-black {{ $stats['saldo_caixa'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    R$&nbsp;{{ number_format($stats['saldo_caixa'], 2, ',', '.') }}
                </h3>
                <p class="text-[11px] font-semibold text-slate-400 mt-1">Balanço total acumulado</p>
            </div>

            <div class="ui-card p-5 border-l-4 {{ $stats['inadimplentes_count'] > 0 ? 'border-amber-500 dark:border-amber-600' : 'border-slate-200 dark:border-slate-700' }}">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Inadimplência</p>
                    <div class="w-8 h-8 rounded-xl {{ $stats['inadimplentes_count'] > 0 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-slate-50 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <h3 class="text-2xl font-black {{ $stats['inadimplentes_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-200' }}">
                    {{ $stats['inadimplentes_count'] }} pend{{ $stats['inadimplentes_count'] !== 1 ? 'ências' : 'ência' }}
                </h3>
                <p class="text-[11px] font-semibold text-slate-400 mt-1">
                    @if($stats['inadimplentes_count'] > 0)
                        R$&nbsp;{{ number_format($stats['inadimplentes_valor'], 2, ',', '.') }} em aberto
                    @else
                        Mensalidades em dia
                    @endif
                </p>
            </div>

            <div class="ui-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Aniversariantes</p>
                    <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-500 dark:text-rose-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-1.5-.454M9 6l3-3 3 3m-3-3v8"/></svg>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['aniversariantes_mes'] }}</h3>
                <p class="text-[11px] font-semibold text-slate-400 mt-1">Em {{ $stats['mes_atual'] }} (ativos)</p>
            </div>
        </div>

        {{-- Relatórios por Categoria --}}
        <div class="grid gap-6 lg:grid-cols-3 px-4 sm:px-0">

            {{-- Secretaria & Membros --}}
            <div class="ui-card p-0 overflow-hidden">
                <div class="border-b border-slate-100 dark:border-slate-800 px-5 py-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center text-sky-600 dark:text-sky-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white">Secretaria & Membros</h3>
                        <p class="text-[11px] text-slate-400">Cadastros, fichas e contatos</p>
                    </div>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="desbravadores">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Lista de Desbravadores</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-sky-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="fichas_completas">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Fichas Completas (ativos)</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-sky-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="fichas_medicas">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Fichas Médicas em Lote</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-sky-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="contatos_emergencia">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Contatos de Emergência</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-sky-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="aniversariantes">
                        <input type="hidden" name="mes_aniversario" value="{{ now()->month }}">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                                Aniversariantes de {{ $stats['mes_atual'] }}
                                @if($stats['aniversariantes_mes'] > 0)
                                    <span class="ml-1.5 inline-flex items-center rounded-full bg-rose-100 dark:bg-rose-900/30 px-1.5 py-0.5 text-[10px] font-bold text-rose-600 dark:text-rose-400">{{ $stats['aniversariantes_mes'] }}</span>
                                @endif
                            </span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-sky-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="eventos">
                        <input type="hidden" name="ano" value="{{ now()->year }}">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Eventos de {{ now()->year }}</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-sky-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Pedagógico & Operação --}}
            <div class="ui-card p-0 overflow-hidden">
                <div class="border-b border-slate-100 dark:border-slate-800 px-5 py-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-violet-50 dark:bg-violet-500/10 flex items-center justify-center text-violet-600 dark:text-violet-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white">Pedagógico & Operação</h3>
                        <p class="text-[11px] text-slate-400">Frequência, ranking e unidades</p>
                    </div>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="frequencia">
                        <input type="hidden" name="mes" value="{{ now()->month }}">
                        <input type="hidden" name="ano" value="{{ now()->year }}">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Frequência de {{ $stats['mes_atual'] }}</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="ranking_desbravadores">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Ranking Individual {{ now()->year }}</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="ranking_unidades">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Ranking das Unidades {{ now()->year }}</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="unidades">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Estrutura das Unidades</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="especialidades">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Especialidades Conquistadas</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="progresso_classe">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Progresso de Classe</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Financeiro & Gestão --}}
            <div class="ui-card p-0 overflow-hidden">
                <div class="border-b border-slate-100 dark:border-slate-800 px-5 py-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white">Financeiro & Gestão</h3>
                        <p class="text-[11px] text-slate-400">Caixa, inadimplência e patrimônio</p>
                    </div>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    <a href="{{ route('relatorios.financeiro') }}" target="_blank" class="flex items-center justify-between px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Financeiro Completo</span>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="inadimplencia">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                Inadimplência
                                @if($stats['inadimplentes_count'] > 0)
                                    <span class="ml-1.5 inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-1.5 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400">{{ $stats['inadimplentes_count'] }}</span>
                                @endif
                            </span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                    <a href="{{ route('relatorios.patrimonio') }}" target="_blank" class="flex items-center justify-between px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                            Inventário Patrimonial
                            <span class="ml-1 text-[11px] text-slate-400 font-normal">R$&nbsp;{{ number_format($stats['patrimonio_total'], 0, ',', '.') }}</span>
                        </span>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="financeiro">
                        <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Financeiro por Filtro</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Gerador Personalizado --}}
        <div class="ui-card p-0 overflow-hidden px-4 sm:px-0">
            <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-5">
                <h3 class="text-base font-black text-slate-800 dark:text-white">Gerador com Filtros Avançados</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Escolha o modelo, aplique filtros e abra o PDF em nova aba.</p>
            </div>

            <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank" class="p-6">
                <div class="grid gap-6 lg:grid-cols-2">

                    {{-- Tipo de Relatório --}}
                    <div class="space-y-5">
                        <div>
                            <x-input-label for="tipo" value="Tipo de Relatório" />
                            <select name="tipo" id="tipo" x-model="tipo" class="ui-input mt-2">
                                <option value="">— Selecione um relatório —</option>
                                <optgroup label="Secretaria & Membros">
                                    <option value="desbravadores">Lista de Desbravadores</option>
                                    <option value="fichas_completas">Fichas Completas</option>
                                    <option value="fichas_medicas">Fichas Médicas em Lote</option>
                                    <option value="contatos_emergencia">Contatos de Emergência</option>
                                    <option value="aniversariantes">Aniversariantes</option>
                                    <option value="eventos">Eventos & Inscrições</option>
                                </optgroup>
                                <optgroup label="Pedagógico & Operação">
                                    <option value="frequencia">Frequência Consolidada</option>
                                    <option value="especialidades">Especialidades Conquistadas</option>
                                    <option value="progresso_classe">Progresso de Classe</option>
                                    <option value="ranking_desbravadores">Ranking Individual</option>
                                    <option value="ranking_unidades">Ranking das Unidades</option>
                                    <option value="unidades">Estrutura das Unidades</option>
                                </optgroup>
                                <optgroup label="Financeiro & Gestão">
                                    <option value="financeiro">Financeiro por Filtro</option>
                                    <option value="inadimplencia">Inadimplência</option>
                                    <option value="patrimonio">Inventário Patrimonial</option>
                                </optgroup>
                            </select>
                        </div>

                        {{-- Filtros de membro (status + unidade) --}}
                        <div x-show="['desbravadores','fichas_completas','fichas_medicas','contatos_emergencia','frequencia','inadimplencia','aniversariantes','especialidades','progresso_classe'].includes(tipo)" x-cloak class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label value="Status" />
                                <select name="status" class="ui-input mt-2">
                                    <option value="ativos">Somente ativos</option>
                                    <option value="todos">Todos os cadastrados</option>
                                    <option value="inativos">Somente inativos</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Unidade" />
                                <select name="unidade_id" class="ui-input mt-2">
                                    <option value="">Todas as unidades</option>
                                    @foreach ($unidades as $unidade)
                                        <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros específicos por tipo --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-5 min-h-[140px] flex flex-col justify-center">

                        <div x-show="!tipo" class="text-center">
                            <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            <p class="text-sm text-slate-400 dark:text-slate-500">Selecione um tipo para ver os filtros disponíveis.</p>
                        </div>

                        {{-- Filtros de frequência --}}
                        <div x-show="tipo === 'frequencia'" x-cloak class="space-y-4">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Competência</p>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label value="Mês" />
                                    <input type="number" min="1" max="12" name="mes" value="{{ now()->month }}" class="ui-input mt-2">
                                </div>
                                <div>
                                    <x-input-label value="Ano" />
                                    <input type="number" min="2020" max="2100" name="ano" value="{{ now()->year }}" class="ui-input mt-2">
                                </div>
                            </div>
                        </div>

                        {{-- Filtro de aniversário --}}
                        <div x-show="tipo === 'aniversariantes'" x-cloak class="space-y-4">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Mês de Aniversário</p>
                            <input type="number" min="1" max="12" name="mes_aniversario" value="{{ now()->month }}" class="ui-input">
                        </div>

                        {{-- Filtros financeiros --}}
                        <div x-show="tipo === 'financeiro'" x-cloak class="space-y-4">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Filtros Financeiros</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <x-input-label value="De" />
                                    <input type="date" name="data_inicio" class="ui-input mt-2">
                                </div>
                                <div>
                                    <x-input-label value="Até" />
                                    <input type="date" name="data_fim" class="ui-input mt-2">
                                </div>
                            </div>
                            <div>
                                <x-input-label value="Tipo de Movimentação" />
                                <select name="tipo_movimentacao" class="ui-input mt-2">
                                    <option value="todos">Entradas e saídas</option>
                                    <option value="entrada">Somente entradas</option>
                                    <option value="saida">Somente saídas</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Categoria" />
                                <select name="categoria" class="ui-input mt-2">
                                    <option value="">Todas as categorias</option>
                                    @foreach ($categoriasCaixa as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Filtro de ano para eventos --}}
                        <div x-show="tipo === 'eventos'" x-cloak class="space-y-4">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Ano</p>
                            <input type="number" min="2020" max="2100" name="ano" value="{{ now()->year }}" class="ui-input">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Lista todos os eventos do ano com inscrições, pagamentos e autorizações.</p>
                        </div>

                        {{-- Relatórios sem filtros adicionais --}}
                        <div x-show="['ranking_unidades','ranking_desbravadores','unidades','patrimonio'].includes(tipo)" x-cloak>
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-3">Sem filtros adicionais</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Este relatório usa a base consolidada do sistema para uma visão geral de gestão.</p>
                        </div>

                        {{-- Relatórios de membro sem filtros extras --}}
                        <div x-show="['desbravadores','fichas_completas','fichas_medicas','contatos_emergencia','inadimplencia','especialidades','progresso_classe'].includes(tipo)" x-cloak>
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-3">Filtros aplicados</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Use status e unidade ao lado para refinar o resultado.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 dark:border-slate-800 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        O PDF será aberto em nova aba pronto para impressão ou download.
                    </p>
                    <button
                        type="submit"
                        :disabled="!tipo"
                        class="ui-btn-primary w-full sm:w-auto disabled:cursor-not-allowed disabled:opacity-40">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
