<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Registrar Ata') }}
        </h2>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-4xl mx-auto">
            <div class="ui-card p-6 md:p-8">
                <form method="POST" action="{{ route('atas.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <x-input-label for="titulo" :value="__('Titulo / Pauta Principal')" />
                            <x-text-input id="titulo" class="block mt-1 w-full" type="text" name="titulo"
                                :value="old('titulo')" required autofocus placeholder="Ex: Reuniao de Diretoria Ordinaria" />
                        </div>

                        <div>
                            <x-input-label for="data_reuniao" :value="__('Data')" />
                            <x-text-input id="data_reuniao" class="block mt-1 w-full" type="date" name="data_reuniao"
                                :value="old('data_reuniao', date('Y-m-d'))" required />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <x-input-label for="hora_inicio" :value="__('Inicio')" />
                                <x-text-input id="hora_inicio" class="block mt-1 w-full" type="time" name="hora_inicio"
                                    :value="old('hora_inicio')" required />
                            </div>
                            <div>
                                <x-input-label for="hora_fim" :value="__('Fim')" />
                                <x-text-input id="hora_fim" class="block mt-1 w-full" type="time" name="hora_fim"
                                    :value="old('hora_fim')" />
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="local" :value="__('Local da Reuniao')" />
                            <x-text-input id="local" class="block mt-1 w-full" type="text" name="local"
                                :value="old('local')" placeholder="Ex: Sala dos Desbravadores" required />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="conteudo" :value="__('Conteudo da Ata (Deliberacoes)')" />
                            <textarea id="conteudo" name="conteudo" rows="10" class="ui-input mt-1" required>{{ old('conteudo') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="participantes" :value="__('Participantes (Opcional)')" />
                            <x-text-input id="participantes" class="block mt-1 w-full" type="text"
                                name="participantes" :value="old('participantes')" placeholder="Separe os nomes por virgula" />
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('atas.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto">Salvar Ata</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
