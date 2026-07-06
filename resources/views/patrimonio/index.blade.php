<x-app-layout>

    <div class="ui-page space-y-6 ui-animate-fade-up">

        {{-- Cabeçalho --}}
        <x-page-title title="Inventário de Patrimônio" subtitle="Controle de bens, barracas, equipamentos e almoxarifado.">
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </x-slot:icon>
            <x-slot:actions>
                <a href="{{ route('patrimonio.create') }}" class="ui-btn-primary w-full sm:w-auto group">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    Novo Item
                </a>
            </x-slot:actions>
        </x-page-title>

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
                                in_array($estado, ['novo', 'ótimo', 'bom']) => 'ui-badge-success',
                                in_array($estado, ['regular']) => 'ui-badge-warning',
                                default => 'ui-badge-danger'
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
                                    <span class="{{ $badge }} !text-[9px] !px-2 !tracking-widest">
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
                                    <form id="del-patrimonio-card-{{ $item->id }}" action="{{ route('patrimonio.destroy', $item->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmAction({ title: 'Excluir Item', message: 'Excluir este item permanentemente?', formId: 'del-patrimonio-card-{{ $item->id }}', confirmText: 'Excluir', variant: 'danger' })" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
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
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qtd</th>
                                <th>Estado</th>
                                <th>Localização</th>
                                <th class="text-right">Valor Unit.</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($patrimonios as $item)
                                @php
                                    $estado = mb_strtolower($item->estado_conservacao, 'UTF-8');
                                    $badge = match(true) {
                                        in_array($estado, ['novo', 'ótimo', 'bom']) => 'ui-badge-success',
                                        in_array($estado, ['regular']) => 'ui-badge-warning',
                                        default => 'ui-badge-danger'
                                    };
                                @endphp
                                <tr class="group">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 flex items-center justify-center shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-800 dark:text-white text-sm uppercase tracking-tight">{{ $item->item }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                                    Aq: {{ $item->data_aquisicao ? \Carbon\Carbon::parse($item->data_aquisicao)->format('d/m/Y') : '-' }}
                                                    @if($item->criadoPor) · por {{ $item->criadoPor->name }} @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-black text-sm">{{ $item->quantidade }}</span>
                                    </td>
                                    <td>
                                        <span class="{{ $badge }} !text-[10px] !tracking-widest">
                                            {{ mb_strtoupper($item->estado_conservacao, 'UTF-8') }}
                                        </span>
                                    </td>
                                    <td class="text-sm font-semibold text-slate-600 dark:text-slate-400 max-w-[150px] truncate">
                                        {{ $item->local_armazenamento ?: '-' }}
                                    </td>
                                    <td class="text-right">
                                        <span class="text-sm font-black text-slate-700 dark:text-slate-300">R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="text-right">
                                        <div class="ui-row-actions justify-end">
                                            <a href="{{ route('patrimonio.edit', $item->id) }}" class="p-2 rounded-xl text-slate-400 hover:text-[#002F6C] hover:bg-[#002F6C]/10 dark:hover:text-blue-400 dark:hover:bg-blue-500/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <form id="del-patrimonio-row-{{ $item->id }}" action="{{ route('patrimonio.destroy', $item->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="button" onclick="confirmAction({ title: 'Excluir Item', message: 'Excluir este item permanentemente?', formId: 'del-patrimonio-row-{{ $item->id }}', confirmText: 'Excluir', variant: 'danger' })" class="p-2 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors">
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
                <x-empty-state
                    class="!border-0 !bg-transparent"
                    title="Inventário Vazio"
                    description="Nenhum bem patrimonial cadastrado. Comece a gerenciar os equipamentos do clube.">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </x-slot:icon>
                    <x-slot:action>
                        <a href="{{ route('patrimonio.create') }}" class="ui-btn-primary">Adicionar Primeiro Item</a>
                    </x-slot:action>
                </x-empty-state>
            @endif
        </div>
    </div>
</x-app-layout>
