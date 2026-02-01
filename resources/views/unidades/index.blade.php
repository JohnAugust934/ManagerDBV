<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                {{ __('Unidades do Clube') }}
            </h2>

            <a href="{{ route('unidades.create') }}"
                class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-dbv-red border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150 shadow-md shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Unidade
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        <div class="md:hidden px-4">
            <a href="{{ route('unidades.create') }}"
                class="w-full flex items-center justify-center px-4 py-3 bg-dbv-red border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-red-700 shadow-md transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Unidade
            </a>
        </div>

        <div class="px-4 md:px-0">
            @if ($unidades->isEmpty())
                <div
                    class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-50 dark:bg-yellow-900/20 mb-4 text-dbv-yellow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Nenhuma unidade cadastrada</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie as unidades para organizar seus
                        desbravadores.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($unidades as $unidade)
                        <div
                            class="group relative bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col h-full">

                            <div class="h-2 w-full bg-gradient-to-r from-dbv-blue to-blue-600"></div>

                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3
                                            class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-dbv-blue dark:group-hover:text-blue-400 transition-colors">
                                            {{ $unidade->nome }}
                                        </h3>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            {{ $unidade->conselheiro ?? 'Sem conselheiro' }}
                                        </p>
                                    </div>
                                    <span
                                        class="px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-bold border border-blue-100 dark:border-blue-800">
                                        {{ $unidade->desbravadores_count ?? 0 }} membros
                                    </span>
                                </div>

                                @if ($unidade->grito_guerra)
                                    <div
                                        class="relative bg-gray-50 dark:bg-slate-700/50 p-3 rounded-lg border-l-4 border-dbv-yellow mb-4 mt-auto">
                                        <p class="text-xs italic text-gray-600 dark:text-gray-300 line-clamp-2">
                                            "{{ $unidade->grito_guerra }}"</p>
                                    </div>
                                @else
                                    <div class="mt-auto pt-4"></div>
                                @endif

                                <div
                                    class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-slate-700">
                                    <a href="{{ route('unidades.show', $unidade) }}"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-bold uppercase rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                                        Ver Painel
                                    </a>
                                    <a href="{{ route('unidades.edit', $unidade) }}"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-50 dark:bg-slate-700 text-gray-600 dark:text-gray-300 text-xs font-bold uppercase rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition">
                                        Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
