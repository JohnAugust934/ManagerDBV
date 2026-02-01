<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Publicar Ato Oficial') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="p-6">
                    <form method="POST" action="{{ route('atos.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="numero" :value="__('Número do Ato')" />
                                <x-text-input id="numero" class="block mt-1 w-full" type="text" name="numero"
                                    :value="old('numero')" required placeholder="Ex: 001/2026" />
                            </div>
                            <div>
                                <x-input-label for="data" :value="__('Data')" />
                                <x-text-input id="data" class="block mt-1 w-full" type="date" name="data"
                                    :value="old('data', date('Y-m-d'))" required />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="tipo" :value="__('Tipo de Ato')" />
                            <select id="tipo" name="tipo"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="Nomeação">Nomeação</option>
                                <option value="Exoneração">Exoneração</option>
                                <option value="Voto da Comissão">Voto da Comissão</option>
                                <option value="Aviso">Aviso</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="descricao" :value="__('Descrição / Teor do Ato')" />
                            <textarea id="descricao" name="descricao" rows="4"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm"
                                required></textarea>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                            <x-secondary-button onclick="window.history.back()">Cancelar</x-secondary-button>
                            <x-primary-button class="bg-red-600 hover:bg-red-700">Publicar Ato</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
