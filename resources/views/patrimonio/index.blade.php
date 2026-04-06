<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Inventário de Patrimônio') }}
        </h2>
    </x-slot>

    <div class="ui-page space-y-8">
        <div class="space-y-8">

            {{-- 1. Dashboard de Resumo (KPIs) --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Total Itens --}}
                <div
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border-l-4 border-blue-500 dark:border-blue-600">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Total Itens (Qtd)</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $totalItens }}</h3>
                </div>

                {{-- Valor Total --}}
                <div
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border-l-4 border-green-500 dark:border-green-600">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Valor Patrimonial</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1 text-nowrap truncate">R$
                        {{ number_format($valorTotal, 2, ',', '.') }}</h3>
                </div>

                {{-- Bom Estado --}}
                <div
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border-l-4 border-teal-400 dark:border-teal-500">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Em Bom Estado</p>
                    <h3 class="text-2xl font-bold text-teal-600 dark:text-teal-400 mt-1">{{ $itensBons }}</h3>
                </div>

                {{-- Precisa Atenção --}}
                <div
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border-l-4 border-red-400 dark:border-red-500">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Inserviveis/Ruins</p>
                    <h3 class="text-2xl font-bold text-red-500 dark:text-red-400 mt-1">{{ $itensRuins }}</h3>
                </div>
            </div>

            {{-- 2. Barra de Ferramentas (Busca e Botão Novo) --}}
            <div
                class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">

                {{-- Campo de Busca --}}
                <form method="GET" action="{{ route('patrimonio.index') }}" class="w-full md:w-96 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Buscar item, local ou observacao..."
                        class="ui-input pl-10 h-10">
                </form>

                {{-- Botão Novo --}}
                <a href="{{ route('patrimonio.create') }}"
                    class="ui-btn-primary w-full sm:w-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Adicionar Bem
                </a>
            </div>

            {{-- 3. Listagem --}}
            <div
                class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">

                @if ($patrimonios->count() > 0)
                    {{-- 
                        === VISÃO DESKTOP (Tabela Clássica) === 
                        Mantida conforme você gostou
                    --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Item</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Qtd</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Estado</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Local</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Valor Unit.</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($patrimonios as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="h-10 w-10 flex-shrink-0 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                        {{ $item->item }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Aq:
                                                        {{ $item->data_aquisicao? \Carbon\Carbon::parse($item->data_aquisicao)->format('d/m/Y') : '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">
                                            {{ $item->quantidade }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $estado = strtolower($item->estado_conservacao);
                                                $colors = [
                                                    'novo' => 'bg-green-100 text-green-800 border-green-200',
                                                    'bom' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    '?timo' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'regular' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                    'ruim' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                    'péssimo' => 'bg-red-100 text-red-800 border-red-200',
                                                    'péssimo' => 'bg-red-100 text-red-800 border-red-200',
                                                    'inservível' => 'bg-red-100 text-red-800 border-red-200',
                                                    'inservível' => 'bg-red-100 text-red-800 border-red-200',
                                                ];
                                                $statusClass =
                                                    $colors[$estado]?? 'bg-gray-100 text-gray-800 border-gray-200';
                                            @endphp
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $statusClass }}">
                                                {{ ucfirst($item->estado_conservacao) }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item->local_armazenamento?? '-' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-white">
                                            R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('patrimonio.edit', $item->id) }}"
                                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 bg-blue-50 dark:bg-blue-900/20 p-2 rounded-lg transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('patrimonio.destroy', $item->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 dark:text-red-400 hover:text-red-900 bg-red-50 dark:bg-red-900/20 p-2 rounded-lg transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 
                        === VISÃO MOBILE (Cards Expandidos e Limpos) === 
                        Redesenhado para não ficar espremido 
                    --}}
                    <div class="md:hidden space-y-4 p-4 bg-gray-50 dark:bg-gray-900/50">
                        @foreach ($patrimonios as $item)
                            <div
                                class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col gap-4">

                                {{-- Cabeçalho do Card: Ícone + Nome Principal + Valor --}}
                                <div class="flex items-start gap-4">
                                    <div
                                        class="h-12 w-12 flex-shrink-0 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400 shadow-inner">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-lg font-bold text-gray-900 dark:text-white leading-tight break-words">
                                            {{ $item->item }}
                                        </h4>
                                        <div class="flex items-center mt-1">
                                            <span
                                                class="text-xs font-bold px-2 py-0.5 rounded text-green-700 bg-green-50 dark:text-green-400 dark:bg-green-900/30 border border-green-100 dark:border-green-800">
                                                R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Corpo do Card: Grid de Informações --}}
                                <div
                                    class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm border-t border-b border-gray-100 dark:border-gray-700 py-4">
                                    {{-- Estado --}}
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Estado</span>
                                        <div>
                                            @php
                                                $estado = strtolower($item->estado_conservacao);
                                                $colorsMobile = [
                                                    'novo' => 'bg-green-100 text-green-800',
                                                    'bom' => 'bg-blue-100 text-blue-800',
                                                    '?timo' => 'bg-blue-100 text-blue-800',
                                                    'regular' => 'bg-yellow-100 text-yellow-800',
                                                    'ruim' => 'bg-orange-100 text-orange-800',
                                                    'péssimo' => 'bg-red-100 text-red-800',
                                                    'péssimo' => 'bg-red-100 text-red-800',
                                                    'inservível' => 'bg-red-100 text-red-800',
                                                    'inservível' => 'bg-red-100 text-red-800',
                                                ];
                                                $statusClassMobile =
                                                    $colorsMobile[$estado]?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold {{ $statusClassMobile }}">
                                                {{ ucfirst($item->estado_conservacao) }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Quantidade --}}
                                    <div class="flex flex-col pl-2 border-l border-gray-100 dark:border-gray-700">
                                        <span
                                            class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Qtd.</span>
                                        <span
                                            class="font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                            </svg>
                                            {{ $item->quantidade }} item(s)
                                        </span>
                                    </div>

                                    {{-- Local --}}
                                    <div class="flex flex-col col-span-2">
                                        <span
                                            class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Localização</span>
                                        <span
                                            class="font-medium text-gray-800 dark:text-gray-200 truncate flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $item->local_armazenamento?? 'Não informado' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Ações: Botões Grandes e Fáceis de Tocar --}}
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('patrimonio.edit', $item->id) }}"
                                        class="flex-1 inline-flex justify-center items-center px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-xl transition active:scale-95">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar
                                    </a>

                                    <form action="{{ route('patrimonio.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Tem certeza que deseja excluir este item?');"
                                        class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 text-sm font-bold rounded-xl transition active:scale-95">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Excluir
                                        </button>
                                    </form>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- Paginação --}}
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        {{ $patrimonios->links() }}
                    </div>
                @else
                    <div class="p-6">
                        <x-empty-state
                            title="Nenhum item encontrado"
                            description="{{ $search? 'Não encontramos resultados para o termo informado. Ajuste a busca e tente novamente.' : 'O inventário do clube ainda est? vazio. Comece adicionando os bens e equipamentos.' }}">
                            <x-slot:action>
                                <a href="{{ route('patrimonio.create') }}" class="ui-btn-primary">
                                    Adicionar Primeiro Item
                                </a>
                            </x-slot:action>
                        </x-empty-state>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>



