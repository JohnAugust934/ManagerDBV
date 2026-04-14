<x-app-layout>
    <x-slot name="header">Fluxo de Caixa</x-slot>

    <div class="ui-page space-y-6 max-w-[1200px] ui-animate-fade-up">

        {{-- 3 Cards de Resumo --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-4 sm:px-0">

            {{-- Saldo Atual --}}
            <div class="ui-card p-6 relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-slate-500/5 dark:bg-white/5"></div>
                <div class="absolute -right-8 -bottom-6 w-32 h-32 rounded-full bg-slate-500/5 dark:bg-white/5"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 relative">Saldo Atual</p>
                <h3 class="text-3xl font-black relative {{ $saldoAtual >= 0 ? 'text-slate-800 dark:text-white' : 'text-red-500 dark:text-red-400' }}">
                    R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                </h3>
                <p class="text-[11px] font-bold text-slate-400 mt-2 relative">Balanço total acumulado</p>
            </div>

            {{-- Entradas --}}
            <div class="ui-card p-6 border-l-4 border-emerald-500 dark:border-emerald-600">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Entradas</p>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400">+ R$ {{ number_format($entradas, 2, ',', '.') }}</h3>
            </div>

            {{-- Saídas --}}
            <div class="ui-card p-6 border-l-4 border-red-500 dark:border-red-600">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Saídas</p>
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-red-600 dark:text-red-400">- R$ {{ number_format($saidas, 2, ',', '.') }}</h3>
            </div>
        </div>

        {{-- Ação Principal --}}
        <div class="flex justify-end px-4 sm:px-0">
            <a href="{{ route('caixa.create') }}" class="ui-btn-primary flex items-center gap-2 h-12 px-6 rounded-2xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                Nova Movimentação
            </a>
        </div>

        {{-- Histórico de Lançamentos --}}
        <div class="ui-card overflow-hidden px-4 sm:px-0">
            {{-- Cabeçalho --}}
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="font-black text-sm text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Histórico
                </h3>
                <span class="text-[11px] font-black text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-700">
                    {{ $lancamentos->total() }} registros
                </span>
            </div>

            @if ($lancamentos->count() > 0)
                {{-- Tabela Desktop --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800">
                                <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-widest text-slate-500">Data</th>
                                <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-widest text-slate-500">Descrição</th>
                                <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-widest text-slate-500">Categoria</th>
                                <th class="px-5 py-3.5 text-right text-[11px] font-black uppercase tracking-widest text-slate-500">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($lancamentos as $lancamento)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3.5 text-[13px] font-black text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($lancamento->data_movimentacao)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-[13px] font-bold text-slate-800 dark:text-white">{{ $lancamento->descricao }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($lancamento->categoria)
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-lg bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 uppercase tracking-widest">{{ $lancamento->categoria }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right text-[13px] font-black whitespace-nowrap {{ $lancamento->tipo === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $lancamento->tipo === 'entrada' ? '+' : '-' }} R$ {{ number_format($lancamento->valor, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Cards Mobile --}}
                <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($lancamentos as $lancamento)
                    <div class="px-4 py-4 flex items-center gap-4">
                        <div class="shrink-0">
                            @if ($lancamento->tipo === 'entrada')
                                <div class="w-11 h-11 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                </div>
                            @else
                                <div class="w-11 h-11 rounded-full bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-400 border border-red-100 dark:border-red-500/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-slate-800 dark:text-white truncate">{{ $lancamento->descricao }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[11px] font-bold text-slate-400">{{ \Carbon\Carbon::parse($lancamento->data_movimentacao)->format('d/m') }}</span>
                                @if ($lancamento->categoria)
                                    <span class="text-slate-300 dark:text-slate-700">•</span>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">{{ $lancamento->categoria }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0 pl-2">
                            <span class="text-sm font-black {{ $lancamento->tipo === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $lancamento->tipo === 'entrada' ? '+' : '-' }} R$ {{ number_format($lancamento->valor, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    {{ $lancamentos->links() }}
                </div>
            @else
                <div class="ui-empty border-none shadow-none py-12">
                    <div class="ui-empty-icon"><svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
                    <h3 class="ui-empty-title">O caixa está vazio</h3>
                    <p class="ui-empty-description">Nenhum lançamento registrado ainda. Comece pelo primeiro agora.</p>
                    <div class="mt-6"><a href="{{ route('caixa.create') }}" class="ui-btn-primary">Registrar Primeiro Lançamento</a></div>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
