<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            {{ __('Novo Evento') }}
        </h2>
    </x-slot>

    <div class="ui-page min-h-full">
        <div class="max-w-4xl mx-auto">

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">

                {{-- Cabeçalho do Card --}}
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Detalhes do Evento
                    </h3>
                </div>

                <div class="p-6 md:p-8">
                    <form action="{{ route('eventos.store') }}" method="POST" class="space-y-6">
                        @csrf

                        {{-- SEÇÃO 1: INFORMAÇÕES BÁSICAS --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Nome --}}
                            <div class="md:col-span-2">
                                <x-input-label for="nome" :value="__('Nome do Evento *')" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </div>
                                    <x-text-input id="nome" class="block w-full pl-10" type="text"
                                        name="nome" :value="old('nome')" required autofocus
                                        placeholder="Ex: Acampamento de Verão" />
                                </div>
                                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            </div>

                            {{-- Local --}}
                            <div class="md:col-span-2">
                                <x-input-label for="local" :value="__('Local *')" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <x-text-input id="local" class="block w-full pl-10" type="text"
                                        name="local" :value="old('local')" required placeholder="Ex: Chácara Oliveira" />
                                </div>
                                <x-input-error :messages="$errors->get('local')" class="mt-2" />
                            </div>
                        </div>

                        {{-- SEÇÃO 2: DATAS E HORÁRIOS (Melhoria de UX) --}}
                        <div
                            class="bg-blue-50 dark:bg-blue-900/10 p-4 rounded-xl border border-blue-100 dark:border-blue-800/30">
                            <h4
                                class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Cronograma
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Data Início --}}
                                <div>
                                    <x-input-label for="data_inicio" :value="__('Início (Data e Hora) *')"
                                        class="text-blue-900 dark:text-blue-200" />
                                    <x-text-input id="data_inicio"
                                        class="block mt-1 w-full border-blue-200 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800"
                                        type="datetime-local" name="data_inicio" :value="old('data_inicio')" required />
                                    <p class="text-xs text-blue-600/70 mt-1">Horário de saída ou início da reunião.</p>
                                    <x-input-error :messages="$errors->get('data_inicio')" class="mt-2" />
                                </div>

                                {{-- Data Fim --}}
                                <div>
                                    <x-input-label for="data_fim" :value="__('Fim (Data e Hora) *')"
                                        class="text-blue-900 dark:text-blue-200" />
                                    <x-text-input id="data_fim"
                                        class="block mt-1 w-full border-blue-200 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800"
                                        type="datetime-local" name="data_fim" :value="old('data_fim')" required />
                                    <p class="text-xs text-blue-600/70 mt-1">Previsão de retorno ou término.</p>
                                    <x-input-error :messages="$errors->get('data_fim')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- SEÇÃO 3: FINANCEIRO E DETALHES --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            {{-- Valor --}}
                            <div class="md:col-span-1">
                                <x-input-label for="valor" :value="__('Valor (R$) *')" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <x-text-input id="valor" class="block w-full pl-10" type="number"
                                        step="0.01" name="valor" :value="old('valor', '0.00')" required />
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Digite 0,00 se for gratuito.</p>
                                <x-input-error :messages="$errors->get('valor')" class="mt-2" />
                            </div>

                            {{-- Descrição --}}
                            <div class="md:col-span-2">
                                <x-input-label for="descricao" :value="__('Descrição / O que levar (Opcional)')" />
                                <textarea id="descricao" name="descricao" rows="3"
                                    class="ui-input mt-1 resize-none"
                                    placeholder="Lista de materiais, uniforme exigido, etc.">{{ old('descricao') }}</textarea>
                                <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
                            </div>
                        </div>

                        {{-- BOTÕES DE AÇÃO --}}
                        <div
                            class="pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-4">
                            <a href="{{ route('eventos.index') }}"
                                class="ui-btn-secondary">
                                Cancelar
                            </a>

                            <button type="submit"
                                class="ui-btn-primary">
                                Salvar Evento
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
