<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Editar Ato Oficial
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="p-6">
                    <form method="POST" action="{{ route('atos.update', $ato) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="numero" :value="__('Número do Ato')" />
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
                            <select id="tipo" name="tipo"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                @foreach (['Nomeação', 'Exoneração', 'Voto da Comissão', 'Aviso'] as $tipo)
                                    <option value="{{ $tipo }}" @selected(old('tipo', $ato->tipo) === $tipo)>{{ $tipo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="descricao" :value="__('Descrição / Teor do Ato')" />
                            <textarea id="descricao" name="descricao" rows="5"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm"
                                required>{{ old('descricao', $ato->descricao) }}</textarea>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                            <button type="button"
                                onclick="if(confirm('Excluir este ato apagará o registro permanentemente. Deseja continuar?')) document.getElementById('delete-ato-form').submit();"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40">
                                Excluir Ato
                            </button>

                            <div class="flex flex-col-reverse sm:flex-row gap-3 w-full sm:w-auto">
                                <a href="{{ route('atos.index') }}"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800">
                                    Cancelar
                                </a>
                                <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest bg-dbv-red text-white hover:bg-red-700">
                                    Salvar Alterações
                                </button>
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
    </div>
</x-app-layout>
