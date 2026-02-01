<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Nova Unidade') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">

                <div class="p-6 border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/30">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Cadastre uma nova unidade para agrupar seus desbravadores.
                    </p>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('unidades.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="nome" :value="__('Nome da Unidade')" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                :value="old('nome')" required autofocus placeholder="Ex: Águias, Órion..." />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="conselheiro" :value="__('Nome do Conselheiro(a)')" />
                            <x-text-input id="conselheiro" class="block mt-1 w-full" type="text" name="conselheiro"
                                :value="old('conselheiro')" required placeholder="Quem lidera esta unidade?" />
                            <x-input-error :messages="$errors->get('conselheiro')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grito_guerra" :value="__('Grito de Guerra (Opcional)')" />
                            <textarea id="grito_guerra" name="grito_guerra" rows="4"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm"
                                placeholder="Digite o grito de guerra aqui...">{{ old('grito_guerra') }}</textarea>
                            <x-input-error :messages="$errors->get('grito_guerra')" class="mt-2" />
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                            <x-secondary-button onclick="window.history.back()">
                                {{ __('Cancelar') }}
                            </x-secondary-button>

                            <x-primary-button class="bg-dbv-blue hover:bg-blue-800">
                                {{ __('Criar Unidade') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
