<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Editar Unidade') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">

                <div class="p-6">
                    <form method="POST" action="{{ route('unidades.update', $unidade) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="nome" :value="__('Nome da Unidade')" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                :value="old('nome', $unidade->nome)" required />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="conselheiro" :value="__('Nome do Conselheiro(a)')" />
                            <x-text-input id="conselheiro" class="block mt-1 w-full" type="text" name="conselheiro"
                                :value="old('conselheiro', $unidade->conselheiro)" required />
                            <x-input-error :messages="$errors->get('conselheiro')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grito_guerra" :value="__('Grito de Guerra')" />
                            <textarea id="grito_guerra" name="grito_guerra" rows="4"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">{{ old('grito_guerra', $unidade->grito_guerra) }}</textarea>
                            <x-input-error :messages="$errors->get('grito_guerra')" class="mt-2" />
                        </div>

                        <div
                            class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-slate-700">
                            <button type="button"
                                onclick="confirm('Tem certeza que deseja excluir esta unidade?') ? document.getElementById('delete-form').submit() : false"
                                class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 hover:underline">
                                Excluir Unidade
                            </button>

                            <div class="flex gap-3">
                                <x-secondary-button onclick="window.history.back()">
                                    {{ __('Cancelar') }}
                                </x-secondary-button>

                                <x-primary-button class="bg-blue-600 hover:bg-blue-700">
                                    {{ __('Salvar Alterações') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('unidades.destroy', $unidade) }}" method="POST"
                        style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
