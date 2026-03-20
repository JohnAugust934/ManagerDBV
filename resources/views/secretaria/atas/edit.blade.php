<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Editar Ata
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('atas.update', $ata) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="titulo" :value="__('Título da Ata')" />
                                <x-text-input id="titulo" class="block mt-1 w-full" type="text" name="titulo"
                                    :value="old('titulo', $ata->titulo)" required />
                            </div>

                            <div>
                                <x-input-label for="data_reuniao" :value="__('Data da Reunião')" />
                                <x-text-input id="data_reuniao" class="block mt-1 w-full" type="date" name="data_reuniao"
                                    :value="old('data_reuniao', $ata->data_reuniao?->format('Y-m-d'))" required />
                            </div>

                            <div>
                                <x-input-label for="local" :value="__('Local')" />
                                <x-text-input id="local" class="block mt-1 w-full" type="text" name="local"
                                    :value="old('local', $ata->local)" required />
                            </div>

                            <div>
                                <x-input-label for="hora_inicio" :value="__('Hora de Início')" />
                                <x-text-input id="hora_inicio" class="block mt-1 w-full" type="time" name="hora_inicio"
                                    :value="old('hora_inicio', optional($ata->hora_inicio)->format('H:i'))" required />
                            </div>

                            <div>
                                <x-input-label for="hora_fim" :value="__('Hora de Término')" />
                                <x-text-input id="hora_fim" class="block mt-1 w-full" type="time" name="hora_fim"
                                    :value="old('hora_fim', optional($ata->hora_fim)->format('H:i'))" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="participantes" :value="__('Participantes')" />
                            <textarea id="participantes" name="participantes" rows="2"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg shadow-sm focus:border-dbv-blue focus:ring-dbv-blue">{{ old('participantes', $ata->participantes) }}</textarea>
                        </div>

                        <div>
                            <x-input-label for="conteudo" :value="__('Conteúdo da Ata')" />
                            <textarea id="conteudo" name="conteudo" rows="12"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg shadow-sm focus:border-dbv-blue focus:ring-dbv-blue"
                                required>{{ old('conteudo', $ata->conteudo) }}</textarea>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                            <button type="button"
                                onclick="if(confirm('Excluir esta ata apagará o registro permanentemente. Deseja continuar?')) document.getElementById('delete-ata-form').submit();"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40">
                                Excluir Ata
                            </button>

                            <div class="flex flex-col-reverse sm:flex-row gap-3 w-full sm:w-auto">
                                <a href="{{ route('atas.show', $ata) }}"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800">
                                    Cancelar
                                </a>
                                <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest bg-dbv-blue text-white hover:bg-blue-800">
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-ata-form" action="{{ route('atas.destroy', $ata) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
