<x-app-layout>
    <x-slot name="header">Gestão de Patrimônio</x-slot>

    <div class="ui-page space-y-6 max-w-[1400px] ui-animate-fade-up">

        {{-- Cabeçalho --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white tracking-tight">Inventário de Patrimônio</h1>
                <p class="text-slate-500 font-medium mt-1 text-sm">Controle de bens, barracas, equipamentos e almoxarifado.</p>
            </div>
            <a href="{{ route('patrimonio.create') }}" class="ui-btn-primary w-full sm:w-auto h-12 px-6 flex items-center justify-center gap-2 rounded-2xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                Novo Item
            </a>
        </div>

        {{-- ============================================= --}}
        {{-- CARDS DE RESUMO — layout equilibrado mobile  --}}
        {{-- ============================================= --}}
        {{-- Mobile: linha 1 = valor (full width), linha 2 = 3 cards em linha --}}
        {{-- Desktop: 4 colunas lado a lado --}}
        <div class="space-y-3 lg:space-y-0 lg:grid lg:grid-cols-4 lg:gap-4">

            {{-- Valor Estimado Total — destaque, full-width no mobile --}}
            <div class="lg:order-2 ui-card p-5 relative overflow-hidden border-l-4 border-emerald-500 flex items-center gap-4">
                <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-emerald-500/10 pointer-events-none"></div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 border border-emerald-200 dark:border-emerald-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor Estimado Total</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 leading-tight">
                        R$ {{ number_format($valorTotal, 2, ',', '.') }}
                    </h3>
                </div>
            </div>

            {{-- Mini-cards: 3 em linha horizontal no mobile --}}
            <div class="grid grid-cols-3 gap-3 lg:contents">
                {{-- Total Itens --}}
                <div class="lg:order-1 ui-card p-4 relative overflow-hidden text-center lg:text-left">
                    <p class="text-[9px] lg:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 leading-tight">Total Itens</p>
                    <h3 class="text-2xl lg:text-3xl font-black text-slate-800 dark:text-white">{{ $totalItens }}</h3>
                </div>

                {{-- Bom Estado --}}
                <div class="lg:order-3 ui-card p-4 relative overflow-hidden text-center lg:text-left">
                    <p class="text-[9px] lg:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 leading-tight">Bom Estado</p>
                    <h3 class="text-2xl lg:text-3xl font-black text-blue-500 dark:text-blue-400">{{ $itensBons }}</h3>
                </div>

                {{-- Ruins --}}
                <div class="lg:order-4 ui-card p-4 relative overflow-hidden text-center lg:text-left">
                    <p class="text-[9px] lg:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 leading-tight">Ruins</p>
                    <h3 class="text-2xl lg:text-3xl font-black text-red-500 dark:text-red-400">{{ $itensRuins }}</h3>
                </div>
            </div>
        </div>

        {{-- ============================================= --}}
        {{-- LISTA DE ITENS                                --}}
        {{-- ============================================= --}}
        <div class="ui-card overflow-hidden">
            {{-- Toolbar de Busca --}}
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <form method="GET" action="{{ route('patrimonio.index') }}" class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar item, local ou observação..." class="ui-input pl-10 w-full font-bold">
                    @if($search)
                        <a href="{{ route('patrimonio.index') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
            </div>

            @if ($patrimonios->count() > 0)
                {{-- MOBILE: Cards individuais --}}
                <div class="block lg:hidden divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($patrimonios as $item)
                        @php
                            $estado = mb_strtolower($item->estado_conservacao, 'UTF-8');
                            $badge = match(true) {
                                in_array($estado, ['novo', 'ótimo', 'bom']) => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400',
                                in_array($estado, ['regular']) => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/20 dark:text-amber-400',
                                default => 'bg-red-50 text-red-600 border-red-100 dark:bg-red-500/20 dark:text-red-400'
                            };
                        @endphp
                        <div class="p-4 flex items-center gap-3">
                            {{-- Ícone --}}
                            <div class="w-11 h-11 rounded-xl bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>

                            {{-- Info principale --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-slate-800 dark:text-white text-sm uppercase tracking-tight truncate">{{ $item->item }}</p>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <span class="inline-block px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded-md border {{ $badge }}">
                                        {{ mb_strtoupper($item->estado_conservacao, 'UTF-8') }}
                                    </span>
                                    @if($item->local_armazenamento)
                                        <span class="text-[10px] font-bold text-slate-400 truncate max-w-[120px]">📍 {{ $item->local_armazenamento }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Valor + Quantidade + Ações --}}
                            <div class="shrink-0 text-right flex flex-col items-end gap-2">
                                <div>
                                    <p class="font-black text-sm text-slate-800 dark:text-white">R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}</p>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Qtd: {{ $item->quantidade }}</p>
                                </div>
                                <div class="flex gap-1">
                                    <a href="{{ route('patrimonio.edit', $item->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-[#002F6C] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('patrimonio.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Excluir este item permanentemente?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- DESKTOP: Tabela Completa --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                                <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 whitespace-nowrap">Item</th>
                                <th class="px-4 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 text-center">Qtd</th>
                                <th class="px-4 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500">Estado</th>
                                <th class="px-4 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500">Localização</th>
                                <th class="px-4 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 text-right">Valor Unit.</th>
                                <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($patrimonios as $item)
                                @php
                                    $estado = mb_strtolower($item->estado_conservacao, 'UTF-8');
                                    $badge = match(true) {
                                        in_array($estado, ['novo', 'ótimo', 'bom']) => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/30',
                                        in_array($estado, ['regular']) => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/20 dark:text-amber-400 dark:border-amber-500/30',
                                        default => 'bg-red-50 text-red-600 border-red-100 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/30'
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors group">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 flex items-center justify-center shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-800 dark:text-white text-sm uppercase tracking-tight">{{ $item->item }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Aq: {{ $item->data_aquisicao ? \Carbon\Carbon::parse($item->data_aquisicao)->format('d/m/Y') : '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-black text-sm">{{ $item->quantidade }}</span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-block px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg border {{ $badge }}">
                                            {{ mb_strtoupper($item->estado_conservacao, 'UTF-8') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-600 dark:text-slate-400 max-w-[150px] truncate">
                                        {{ $item->local_armazenamento ?: '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-right whitespace-nowrap">
                                        <span class="text-sm font-black text-slate-700 dark:text-slate-300">R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('patrimonio.edit', $item->id) }}" class="p-2 rounded-xl text-slate-400 hover:text-[#002F6C] hover:bg-[#002F6C]/10 dark:hover:text-blue-400 dark:hover:bg-blue-500/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <form action="{{ route('patrimonio.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Excluir este item permanentemente?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    {{ $patrimonios->links() }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-2">Inventário Vazio</h3>
                    <p class="text-sm font-bold text-slate-400 mb-6 max-w-md">Nenhum bem patrimonial cadastrado. Comece a gerenciar os equipamentos do clube.</p>
                    <a href="{{ route('patrimonio.create') }}" class="ui-btn-primary">Adicionar Primeiro Item</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
