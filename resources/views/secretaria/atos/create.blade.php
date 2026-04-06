<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Publicar Ato Oficial') }}
        </h2>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-2xl mx-auto">
            <div class="ui-card p-6 md:p-8">
                <form method="POST" action="{{ route('atos.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="numero" :value="__('Numero do Ato')" />
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
                        <select id="tipo" name="tipo" class="ui-input mt-1">
                            <option value="Nomeacao">Nomeacao</option>
                            <option value="Exoneracao">Exoneracao</option>
                            <option value="Voto da Comissao">Voto da Comissao</option>
                            <option value="Aviso">Aviso</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="descricao" :value="__('Descricao / Teor do Ato')" />
                        <textarea id="descricao" name="descricao" rows="4" class="ui-input mt-1" required>{{ old('descricao') }}</textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('atos.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto">Publicar Ato</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
