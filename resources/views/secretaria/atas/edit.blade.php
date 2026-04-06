<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Editar Ata
        </h2>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-4xl mx-auto">
            <div class="ui-card p-6 md:p-8">
                <form method="POST" action="{{ route('atas.update', $ata) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <x-input-label for="titulo" :value="__('Titulo da Ata')" />
                            <x-text-input id="titulo" class="block mt-1 w-full" type="text" name="titulo"
                                :value="old('titulo', $ata->titulo)" required />
                        </div>

                        <div>
                            <x-input-label for="data_reuniao" :value="__('Data da Reuniao')" />
                            <x-text-input id="data_reuniao" class="block mt-1 w-full" type="date" name="data_reuniao"
                                :value="old('data_reuniao', $ata->data_reuniao?->format('Y-m-d'))" required />
                        </div>

                        <div>
                            <x-input-label for="local" :value="__('Local')" />
                            <x-text-input id="local" class="block mt-1 w-full" type="text" name="local"
                                :value="old('local', $ata->local)" required />
                        </div>

                        <div>
                            <x-input-label for="hora_inicio" :value="__('Hora de Inicio')" />
                            <x-text-input id="hora_inicio" class="block mt-1 w-full" type="time" name="hora_inicio"
                                :value="old('hora_inicio', optional($ata->hora_inicio)->format('H:i'))" required />
                        </div>

                        <div>
                            <x-input-label for="hora_fim" :value="__('Hora de Termino')" />
                            <x-text-input id="hora_fim" class="block mt-1 w-full" type="time" name="hora_fim"
                                :value="old('hora_fim', optional($ata->hora_fim)->format('H:i'))" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="participantes" :value="__('Participantes')" />
                        <textarea id="participantes" name="participantes" rows="2" class="ui-input mt-1">{{ old('participantes', $ata->participantes) }}</textarea>
                    </div>

                    <div>
                        <x-input-label for="conteudo" :value="__('Conteudo da Ata')" />
                        <textarea id="conteudo" name="conteudo" rows="12" class="ui-input mt-1" required>{{ old('conteudo', $ata->conteudo) }}</textarea>
                    </div>

                    <div class="flex flex-col gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button"
                            onclick="if(confirm('Excluir esta ata apagara o registro permanentemente. Deseja continuar?')) document.getElementById('delete-ata-form').submit();"
                            class="ui-btn-danger w-full sm:w-auto">
                            Excluir Ata
                        </button>

                        <div class="flex flex-col-reverse sm:flex-row gap-3 w-full sm:w-auto">
                            <a href="{{ route('atas.show', $ata) }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                            <button type="submit" class="ui-btn-primary w-full sm:w-auto">Salvar Alterações</button>
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
</x-app-layout>

