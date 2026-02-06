<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Cadastrar Especialidade') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div
                class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">

                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Nova Especialidade
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Adicione uma nova especialidade à biblioteca do clube.
                    </p>
                </div>

                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('especialidades.store') }}" class="space-y-6">
                        @csrf

                        {{-- 1. Nome --}}
                        <div>
                            <x-input-label for="nome" value="Nome da Especialidade *" />
                            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full"
                                :value="old('nome')" placeholder="Ex: Fogueiras e Cozinha ao Ar Livre" required
                                autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('nome')" />
                        </div>

                        {{-- 2. Área --}}
                        <div>
                            <x-input-label for="area" value="Área / Categoria *" />
                            <div class="relative mt-1">
                                <select id="area" name="area"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm appearance-none"
                                    required>
                                    <option value="" disabled selected>Selecione a área...</option>
                                    @foreach (['ADRA', 'Artes e Habilidades Manuais', 'Atividades Agropecuárias', 'Atividades Missionárias e Comunitárias', 'Atividades Profissionais', 'Atividades Recreativas', 'Ciência e Saúde', 'Estudo da Natureza', 'Habilidades Domésticas', 'Mestrados'] as $area)
                                        <option value="{{ $area }}"
                                            {{ old('area') == $area ? 'selected' : '' }}>
                                            {{ $area }}
                                        </option>
                                    @endforeach
                                    <option value="Outra">Outra</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('area')" />
                        </div>

                        {{-- Botões --}}
                        <div
                            class="flex items-center justify-end pt-6 border-t border-gray-100 dark:border-gray-700 gap-4">
                            <a href="{{ route('especialidades.index') }}"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 font-medium">
                                Cancelar
                            </a>

                            <x-primary-button class="justify-center">
                                {{ __('Cadastrar') }}
                            </x-primary-button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
