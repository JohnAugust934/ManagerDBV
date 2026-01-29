<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Novo Evento
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('eventos.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="nome" :value="__('Nome do Evento')" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome" required autofocus />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="local" :value="__('Local')" />
                            <x-text-input id="local" class="block mt-1 w-full" type="text" name="local" required placeholder="Ex: Sítio São José" />
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="data_inicio" :value="__('Início')" />
                                <x-text-input id="data_inicio" class="block mt-1 w-full" type="datetime-local" name="data_inicio" required />
                            </div>
                            <div>
                                <x-input-label for="data_fim" :value="__('Fim')" />
                                <x-text-input id="data_fim" class="block mt-1 w-full" type="datetime-local" name="data_fim" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="valor" :value="__('Valor da Inscrição (R$)')" />
                            <x-text-input id="valor" class="block mt-1 w-full" type="number" step="0.01" name="valor" required />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="descricao" :value="__('Descrição / Itens para levar')" />
                            <textarea id="descricao" name="descricao" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>{{ __('Salvar Evento') }}</x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>