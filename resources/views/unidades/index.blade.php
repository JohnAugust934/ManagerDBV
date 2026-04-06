<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
            {{ __('Unidades do Clube') }}
        </h2>
    </x-slot>

    <div class="ui-page space-y-6">

        <div class="px-4 sm:px-0 flex justify-end">
            <a href="{{ route('unidades.create') }}"
                class="ui-btn-primary w-full sm:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Unidade
            </a>
        </div>

        <div class="px-4 md:px-0">
            @if ($unidades->isEmpty())
                <x-empty-state
                    title="Nenhuma unidade cadastrada"
                    description="Crie as unidades para organizar os desbravadores do clube." />
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
                                            {{ $unidade->conselheiro?? 'Sem conselheiro' }}
                                        </p>
                                    </div>
                                    <span
                                        class="px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-bold border border-blue-100 dark:border-blue-800">
                                        {{ $unidade->desbravadores_count?? 0 }} membros
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
                                        class="ui-btn-secondary">
                                        Ver Painel
                                    </a>
                                    <a href="{{ route('unidades.edit', $unidade) }}"
                                        class="ui-btn-secondary">
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

