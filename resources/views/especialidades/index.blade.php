<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Biblioteca de Especialidades') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

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
                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:border-dbv-blue focus:ring focus:ring-dbv-blue focus:ring-opacity-50 sm:text-sm h-10 transition-shadow">
                </form>

                {{-- Botão Novo --}}
                <a href="{{ route('especialidades.create') }}"
                    class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-dbv-blue dark:bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-800 dark:hover:bg-blue-500 shadow-md transform hover:-translate-y-0.5 transition-all">
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
                                str_contains($area, 'saúde') || str_contains($area, 'ciência')
                                    => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
                                str_contains($area, 'atividades') || str_contains($area, 'recreação')
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
                                    {{ $especialidade->desbravadores_count ?? 0 }}
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
                {{-- Empty State --}}
                <div
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                    <div class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600 mb-4">
                        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Nenhuma especialidade encontrada</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($search)
                            Tente buscar por outro termo.
                        @else
                            Comece cadastrando as especialidades do clube.
                        @endif
                    </p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
