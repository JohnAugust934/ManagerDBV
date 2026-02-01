<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Editar Patrimônio') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="p-6">
                    <form method="POST" action="{{ route('patrimonio.update', $patrimonio) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="item" :value="__('Descrição do Item')" />
                            <x-text-input id="item" class="block mt-1 w-full" type="text" name="item"
                                :value="old('item', $patrimonio->item)" required />
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="quantidade" :value="__('Quantidade')" />
                                <x-text-input id="quantidade" class="block mt-1 w-full" type="number" name="quantidade"
                                    :value="old('quantidade', $patrimonio->quantidade)" required min="1" />
                            </div>

                            <div>
                                <x-input-label for="valor_estimado" :value="__('Valor Unit. (R$)')" />
                                <x-text-input id="valor_estimado" class="block mt-1 w-full" type="number"
                                    step="0.01" name="valor_estimado" :value="old('valor_estimado', $patrimonio->valor_estimado)" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="estado_conservacao" :value="__('Estado de Conservação')" />
                                <select id="estado_conservacao" name="estado_conservacao"
                                    class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                    @foreach (['Novo', 'Bom', 'Regular', 'Ruim', 'Inservível'] as $estado)
                                        <option value="{{ $estado }}"
                                            {{ old('estado_conservacao', $patrimonio->estado_conservacao) == $estado ? 'selected' : '' }}>
                                            {{ $estado }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="data_aquisicao" :value="__('Data Aquisição')" />
                                <x-text-input id="data_aquisicao" class="block mt-1 w-full" type="date"
                                    name="data_aquisicao" :value="old(
                                        'data_aquisicao',
                                        optional($patrimonio->data_aquisicao)->format('Y-m-d'),
                                    )" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="local_armazenamento" :value="__('Local de Armazenamento')" />
                            <x-text-input id="local_armazenamento" class="block mt-1 w-full" type="text"
                                name="local_armazenamento" :value="old('local_armazenamento', $patrimonio->local_armazenamento)" />
                        </div>

                        <div>
                            <x-input-label for="observacoes" :value="__('Observações')" />
                            <textarea id="observacoes" name="observacoes" rows="3"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">{{ old('observacoes', $patrimonio->observacoes) }}</textarea>
                        </div>

                        <div
                            class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-slate-700">
                            <button type="button"
                                onclick="if(confirm('Tem certeza que deseja dar baixa neste item?')) document.getElementById('delete-form').submit()"
                                class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 hover:underline">
                                Dar Baixa / Excluir
                            </button>

                            <div class="flex gap-3">
                                <x-secondary-button onclick="window.history.back()">Cancelar</x-secondary-button>
                                <x-primary-button>Salvar Alterações</x-primary-button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('patrimonio.destroy', $patrimonio) }}" method="POST"
                        style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
