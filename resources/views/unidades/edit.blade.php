<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Unidade: {{ $unidade->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('unidades.update', $unidade->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="nome" :value="__('Nome da Unidade *')" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome" :value="old('nome', $unidade->nome)" required />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="conselheiro" :value="__('Nome do Conselheiro *')" />
                            <x-text-input id="conselheiro" class="block mt-1 w-full" type="text" name="conselheiro" :value="old('conselheiro', $unidade->conselheiro)" required />
                            <x-input-error :messages="$errors->get('conselheiro')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="grito_guerra" :value="__('Grito de Guerra')" />
                            <textarea id="grito_guerra" name="grito_guerra" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('grito_guerra', $unidade->grito_guerra) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('unidades.show', $unidade->id) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                            <x-primary-button>
                                {{ __('Salvar Alterações') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>