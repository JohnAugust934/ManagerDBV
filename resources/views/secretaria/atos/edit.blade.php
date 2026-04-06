<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">Editar Ato Oficial</h2>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-2xl mx-auto">
            <div class="ui-card p-6 md:p-8">
                <form method="POST" action="{{ route('atos.update', $ato) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="numero" :value="__('Numero do Ato')" />
                            <x-text-input id="numero" class="block mt-1 w-full" type="text" name="numero"
                                :value="old('numero', $ato->numero)" required />
                        </div>
                        <div>
                            <x-input-label for="data" :value="__('Data')" />
                            <x-text-input id="data" class="block mt-1 w-full" type="date" name="data"
                                :value="old('data', $ato->data?->format('Y-m-d'))" required />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="tipo" :value="__('Tipo de Ato')" />
                        <select id="tipo" name="tipo" class="ui-input mt-1">
                            @foreach (['Nomeacao', 'Exoneracao', 'Voto da Comissao', 'Aviso'] as $tipo)
                                <option value="{{ $tipo }}" @selected(old('tipo', $ato->tipo) === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="descricao" :value="__('Descricao / Teor do Ato')" />
                        <textarea id="descricao" name="descricao" rows="5" class="ui-input mt-1" required>{{ old('descricao', $ato->descricao) }}</textarea>
                    </div>

                    <div class="flex flex-col gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button"
                            onclick="if(confirm('Excluir este ato apagara o registro permanentemente. Deseja continuar?')) document.getElementById('delete-ato-form').submit();"
                            class="ui-btn-danger w-full sm:w-auto">
                            Excluir Ato
                        </button>

                        <div class="flex flex-col-reverse sm:flex-row gap-3 w-full sm:w-auto">
                            <a href="{{ route('atos.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                            <button type="submit" class="ui-btn-primary w-full sm:w-auto">Salvar Alterações</button>
                        </div>
                    </div>
                </form>

                <form id="delete-ato-form" action="{{ route('atos.destroy', $ato) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

