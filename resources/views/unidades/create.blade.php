<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nova Unidade
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Preencha os dados abaixo para criar uma nova unidade. Campos com <span class="text-red-500 font-bold">*</span> são obrigatórios.
                        </p>
                    </div>

                    <form action="{{ route('unidades.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="nome" :value="__('Nome da Unidade *')" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome" :value="old('nome')" required autofocus placeholder="Ex: Águias" />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="conselheiro" :value="__('Nome do Conselheiro *')" />
                            <x-text-input id="conselheiro" class="block mt-1 w-full" type="text" name="conselheiro" :value="old('conselheiro')" required placeholder="Nome do líder responsável" />
                            <x-input-error :messages="$errors->get('conselheiro')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="grito_guerra" :value="__('Grito de Guerra (Opcional)')" />
                            <textarea id="grito_guerra" name="grito_guerra" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Digite o grito de guerra da unidade...">{{ old('grito_guerra') }}</textarea>
                            <x-input-error :messages="$errors->get('grito_guerra')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('unidades.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                            <x-primary-button>
                                {{ __('Criar Unidade') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>