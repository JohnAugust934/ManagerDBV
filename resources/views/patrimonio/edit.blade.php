<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Editar Item de Patrimônio') }}
        </h2>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-4xl mx-auto">
            <div class="ui-card overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40">
                    <h3 class="ui-title text-lg">Atualizar Dados</h3>
                    <p class="ui-subtitle mt-1">Edite as informações do item selecionado.</p>
                </div>

                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('patrimonio.update', $patrimonio->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="item" value="Nome do Item / Equipamento *" />
                            <x-text-input id="item" name="item" type="text" class="mt-1 block w-full"
                                :value="old('item', $patrimonio->item)" placeholder="Ex: Barraca Iglu 4 Pessoas, Caixa de Som" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('item')" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="quantidade" value="Quantidade *" />
                                <x-text-input id="quantidade" name="quantidade" type="number" min="1" step="1"
                                    class="mt-1 block w-full" :value="old('quantidade', $patrimonio->quantidade)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('quantidade')" />
                            </div>

                            <div>
                                <x-input-label for="estado_conservacao" value="Estado de Conservacao *" />
                                <select id="estado_conservacao" name="estado_conservacao" class="ui-input mt-1" required>
                                    <option value="" disabled>Selecione...</option>
                                    @foreach (['Novo', 'Otimo', 'Bom', 'Regular', 'Ruim', 'Pessimo', 'Inservivel'] as $estado)
                                        <option value="{{ $estado }}"
                                            {{ old('estado_conservacao', $patrimonio->estado_conservacao) == $estado? 'selected' : '' }}>
                                            {{ $estado }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('estado_conservacao')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="valor_estimado" value="Valor Unitario Estimado (R$)" />
                                <x-text-input id="valor_estimado" name="valor_estimado" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full" :value="old('valor_estimado', $patrimonio->valor_estimado)" placeholder="0.00" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Deixe em branco se desconhecido.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('valor_estimado')" />
                            </div>

                            <div>
                                <x-input-label for="data_aquisicao" value="Data de Aquisicao" />
                                <x-text-input id="data_aquisicao" name="data_aquisicao" type="date" class="mt-1 block w-full" :value="old(
                                    'data_aquisicao',
                                    $patrimonio->data_aquisicao? \Carbon\Carbon::parse($patrimonio->data_aquisicao)->format('Y-m-d')
                                        : '',
                                )" />
                                <x-input-error class="mt-2" :messages="$errors->get('data_aquisicao')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="local_armazenamento" value="Local de Armazenamento" />
                            <x-text-input id="local_armazenamento" name="local_armazenamento" type="text" class="mt-1 block w-full"
                                :value="old('local_armazenamento', $patrimonio->local_armazenamento)" placeholder="Ex: Armario A, Sala da Diretoria" />
                            <x-input-error class="mt-2" :messages="$errors->get('local_armazenamento')" />
                        </div>

                        <div>
                            <x-input-label for="observacoes" value="Observações / Detalhes" />
                            <textarea id="observacoes" name="observacoes" rows="3" class="ui-input mt-1 resize-none"
                                placeholder="Descreva detalhes, numero de serie, cor ou avarias.">{{ old('observacoes', $patrimonio->observacoes) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('observacoes')" />
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-5 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('patrimonio.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                            <button type="submit" class="ui-btn-primary w-full sm:w-auto">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

