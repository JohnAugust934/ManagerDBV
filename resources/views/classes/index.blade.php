<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Classes Regulares e Avancadas
        </h2>
    </x-slot>

    <div class="ui-page">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($classes as $classe)
                <a href="{{ route('classes.show', $classe->id) }}"
                    class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 transform hover:-translate-y-1">

                    {{-- Faixa de Cor da Classe --}}
                    <div class="h-2 w-full" style="background-color: {{ $classe->cor }}"></div>

                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 group-hover:scale-110 transition-transform duration-300">
                                {{-- Ícone Dinâmico (Letra Inicial) --}}
                                <span class="text-2xl font-black" style="color: {{ $classe->cor }}">
                                    {{ substr($classe->nome, 0, 1) }}
                                </span>
                            </div>
                            <span
                                class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                                {{ $classe->requisitos->count() }} Requisitos
                            </span>
                        </div>

                        <h3
                            class="text-xl font-bold text-gray-800 dark:text-white mb-1 group-hover:text-dbv-blue dark:group-hover:text-blue-400 transition-colors">
                            {{ $classe->nome }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Gerenciar progresso e requisitos da turma.
                        </p>
                    </div>

                    {{-- Botão "Entrar" disfarçado --}}
                    <div
                        class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Acessar Sala</span>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-dbv-blue transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</x-app-layout>


