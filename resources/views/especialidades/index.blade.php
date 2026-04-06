<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Biblioteca de Especialidades') }}
        </h2>
    </x-slot>

    <div class="ui-page space-y-8">
        <div class="space-y-8">

            {{-- Barra de Ferramentas --}}
            <div
                class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">

                {{-- Busca --}}
                <form method="GET" action="{{ route('especialidades.index') }}" class="w-full md:w-96 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Buscar especialidade..."
                        class="ui-input pl-10 h-10">
                </form>

                {{-- Botão Novo --}}
                <a href="{{ route('especialidades.create') }}"
                    class="ui-btn-primary w-full sm:w-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova Especialidade
                </a>
            </div>

            {{-- Grid de Especialidades --}}
            @if ($especialidades->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                    @foreach ($especialidades as $especialidade)
                        @php
                            $area = strtolower($especialidade->area);
                            $bgClass = match (true) {
                                str_contains($area, 'natureza')
                                    => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
                                str_contains($area, 'adra') || str_contains($area, 'comunidade')
                                    => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
                                str_contains($area, 'artes') || str_contains($area, 'habilidades')
                                    => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800',
                                str_contains($area, 'saude') || str_contains($area, 'ciencia')
                                    => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
                                str_contains($area, 'atividades') || str_contains($area, 'recreacao')
                                    => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-800',
                                default
                                    => 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                            };
                        @endphp

                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between h-full">

                            {{-- Área / Categoria (Badge) --}}
                            <div class="mb-3">
                                <span
                                    class="px-2.5 py-1 rounded-md text-[10px] font-bold border uppercase tracking-wider {{ $bgClass }}">
                                    {{ $especialidade->area }}
                                </span>
                            </div>

                            {{-- Nome da Especialidade --}}
                            <h3 class="text-base font-bold text-gray-900 dark:text-white leading-snug">
                                {{ $especialidade->nome }}
                            </h3>

                            {{-- Contagem de Desbravadores --}}
                            <div
                                class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <svg class="w-4 h-4 mr-1.5 text-gray-400 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="font-medium">
                                    {{ $especialidade->desbravadores_count?? 0 }}
                                </span>
                                <span class="ml-1">conclusões</span>
                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- Paginação --}}
                <div class="mt-6 p-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $especialidades->links() }}
                </div>
            @else
                <x-empty-state
                    title="Nenhuma especialidade encontrada"
                    description="{{ $search? 'Tente buscar por outro termo.' : 'Comece cadastrando as especialidades do clube.' }}">
                    <x-slot:action>
                        <a href="{{ route('especialidades.create') }}" class="ui-btn-primary">Nova Especialidade</a>
                    </x-slot:action>
                </x-empty-state>
            @endif

        </div>
    </div>
</x-app-layout>



