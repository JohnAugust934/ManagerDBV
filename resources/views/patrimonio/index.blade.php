<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                {{ __('Patrimônio') }}
            </h2>

            <a href="{{ route('patrimonio.create') }}"
                class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 active:bg-blue-900 focus:outline-none transition shadow-md shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Adicionar Bem
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        <div class="md:hidden px-4">
            <a href="{{ route('patrimonio.create') }}"
                class="w-full flex items-center justify-center px-4 py-3 bg-dbv-blue border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-800 shadow-md transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Adicionar Bem
            </a>
        </div>

        <div class="px-4 md:px-0">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total de
                        Itens</p>
                    <h3 class="text-2xl font-extrabold text-gray-800 dark:text-white">{{ $totalItens }} <span
                            class="text-sm font-normal text-gray-400">unidades</span></h3>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 relative overflow-hidden">
                    <div class="absolute right-0 top-0 h-full w-1 bg-green-500"></div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Valor
                        Estimado</p>
                    <h3 class="text-2xl font-extrabold text-green-600 dark:text-green-400">
                        R$ {{ number_format($valorTotal, 2, ',', '.') }}
                    </h3>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 relative overflow-hidden">
                    <div class="absolute right-0 top-0 h-full w-1 {{ $itensRuins > 0 ? 'bg-red-500' : 'bg-gray-200' }}">
                    </div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Estado
                        Crítico</p>
                    <h3
                        class="text-2xl font-extrabold {{ $itensRuins > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-100' }}">
                        {{ $itensRuins }} <span class="text-sm font-normal text-gray-400">itens ruins</span>
                    </h3>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-4 mb-6">
                <form method="GET" action="{{ route('patrimonio.index') }}" class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Buscar por item ou local..."
                        class="pl-10 block w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg focus:ring-dbv-blue focus:border-dbv-blue sm:text-sm h-10 transition-colors">
                </form>
            </div>

            @if ($patrimonios->count() > 0)

                <div
                    class="hidden md:block bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Item</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Local</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Estado</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Valor Unit.</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                            @foreach ($patrimonios as $bem)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="ml-0">
                                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                    {{ $bem->item }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $bem->quantidade }} unidades</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $bem->local_armazenamento ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            // CORREÇÃO: Usando o nome correto da coluna
                                            $cor = match ($bem->estado_conservacao) {
                                                'Novo'
                                                    => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                'Bom'
                                                    => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                'Regular'
                                                    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                default
                                                    => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                            };
                                        @endphp
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cor }}">
                                            {{ $bem->estado_conservacao }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                        R$ {{ number_format($bem->valor_estimado, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('patrimonio.edit', $bem) }}"
                                            class="text-dbv-blue dark:text-blue-400 hover:underline">Editar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                        {{ $patrimonios->links() }}
                    </div>
                </div>

                <div class="md:hidden space-y-3">
                    @foreach ($patrimonios as $bem)
                        <div
                            class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-slate-700 relative overflow-hidden">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $bem->item }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $bem->local_armazenamento ?? 'Local não definido' }}</p>
                                </div>
                                <span
                                    class="text-xs font-bold bg-gray-100 dark:bg-slate-700 px-2 py-1 rounded-md text-gray-600 dark:text-gray-300">
                                    Qtd: {{ $bem->quantidade }}
                                </span>
                            </div>

                            <div
                                class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                                @php
                                    $cor = match ($bem->estado_conservacao) {
                                        'Novo' => 'text-green-600 dark:text-green-400',
                                        'Bom' => 'text-blue-600 dark:text-blue-400',
                                        'Regular' => 'text-yellow-600 dark:text-yellow-400',
                                        default => 'text-red-600 dark:text-red-400',
                                    };
                                @endphp
                                <span
                                    class="text-xs font-bold uppercase {{ $cor }}">{{ $bem->estado_conservacao }}</span>

                                <a href="{{ route('patrimonio.edit', $bem) }}"
                                    class="text-xs font-bold text-dbv-blue dark:text-blue-400 border border-blue-200 dark:border-blue-800 px-3 py-1.5 rounded-lg">
                                    Editar
                                </a>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-4">
                        {{ $patrimonios->links() }}
                    </div>
                </div>
            @else
                <div
                    class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-slate-700 mb-4 text-gray-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">O inventário está vazio.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
