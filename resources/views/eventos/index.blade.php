<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Eventos do Clube') }}
        </h2>
    </x-slot>

    <div class="py-6 bg-gray-50 dark:bg-dbv-dark-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- BARRA DE AÇÕES --}}
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-5">
                <div class="text-sm text-gray-500 dark:text-gray-400 hidden md:block">
                    Gerencie o calendário, inscrições e pagamentos.
                </div>

                @can('secretaria')
                    <a href="{{ route('eventos.create') }}"
                        class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform active:scale-95">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Novo Evento
                    </a>
                @endcan
            </div>

            {{-- 
               REMOVIDO: @if (session('success')) ... @endif 
               MOTIVO: O layout 'x-app-layout' já exibe mensagens de sessão globalmente.
            --}}

            {{-- Grid de Eventos --}}
            @if ($eventos->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($eventos as $evento)
                        @php
                            $dataInicio = \Carbon\Carbon::parse($evento->data_inicio);
                            $isPassado = \Carbon\Carbon::parse($evento->data_fim)->isPast();
                        @endphp

                        <div
                            class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 flex flex-col overflow-hidden relative">

                            {{-- Barra Superior Colorida --}}
                            <div
                                class="h-2 bg-gradient-to-r {{ $isPassado ? 'from-gray-400 to-gray-600' : 'from-dbv-blue to-blue-500' }}">
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex justify-between items-start mb-4">
                                    {{-- Badge de Data --}}
                                    <div
                                        class="flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2 w-14 h-14 border border-gray-200 dark:border-gray-600 group-hover:border-blue-200 dark:group-hover:border-blue-900 transition-colors">
                                        <span
                                            class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $dataInicio->translatedFormat('M') }}</span>
                                        <span
                                            class="text-xl font-extrabold text-gray-800 dark:text-white leading-none">{{ $dataInicio->format('d') }}</span>
                                    </div>

                                    {{-- Badge de Status --}}
                                    @if ($isPassado)
                                        <span
                                            class="px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-bold rounded-full border border-gray-200 dark:border-gray-600 uppercase tracking-wide">Encerrado</span>
                                    @else
                                        <span
                                            class="px-2.5 py-0.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-[10px] font-bold rounded-full border border-green-200 dark:border-green-800 uppercase tracking-wide flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                            Aberto
                                        </span>
                                    @endif
                                </div>

                                <h3
                                    class="text-lg font-bold text-gray-800 dark:text-white mb-3 line-clamp-2 group-hover:text-dbv-blue dark:group-hover:text-blue-400 transition-colors">
                                    {{ $evento->nome }}
                                </h3>

                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400 mb-6">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="truncate font-medium">{{ $evento->local }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                        <span
                                            class="font-bold {{ $evento->valor == 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $evento->valor == 0 ? 'Gratuito' : 'R$ ' . number_format($evento->valor, 2, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                                            </path>
                                        </svg>
                                        <span>{{ $evento->desbravadores_count }} inscritos</span>
                                    </div>
                                </div>

                                <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <a href="{{ route('eventos.show', $evento->id) }}"
                                        class="flex items-center justify-center w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-dbv-blue hover:text-white hover:border-dbv-blue dark:hover:bg-blue-600 dark:hover:border-blue-600 transition-all duration-200 text-sm font-bold shadow-sm">
                                        Gerenciar
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Paginação --}}
                <div class="mt-8">
                    {{ $eventos->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div
                    class="flex flex-col items-center justify-center p-10 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-dashed border-gray-300 dark:border-gray-700 text-center">
                    <div
                        class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Nenhum evento encontrado</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-6 max-w-xs mx-auto">
                        Comece a planejar o ano do clube criando o primeiro evento do calendário.
                    </p>
                    @can('secretaria')
                        <a href="{{ route('eventos.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 transition shadow-md">
                            Criar Primeiro Evento
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
