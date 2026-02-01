<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Registrar Ata') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="p-6">
                    <form method="POST" action="{{ route('atas.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="titulo" :value="__('Título / Pauta Principal')" />
                                <x-text-input id="titulo" class="block mt-1 w-full" type="text" name="titulo"
                                    :value="old('titulo')" required autofocus
                                    placeholder="Ex: Reunião de Diretoria Ordinária" />
                            </div>

                            <div>
                                <x-input-label for="data_reuniao" :value="__('Data')" />
                                <x-text-input id="data_reuniao" class="block mt-1 w-full" type="date"
                                    name="data_reuniao" :value="old('data_reuniao', date('Y-m-d'))" required />
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <x-input-label for="hora_inicio" :value="__('Início')" />
                                    <x-text-input id="hora_inicio" class="block mt-1 w-full" type="time"
                                        name="hora_inicio" :value="old('hora_inicio')" required />
                                </div>
                                <div>
                                    <x-input-label for="hora_fim" :value="__('Fim')" />
                                    <x-text-input id="hora_fim" class="block mt-1 w-full" type="time"
                                        name="hora_fim" :value="old('hora_fim')" />
                                </div>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="local" :value="__('Local da Reunião')" />
                                <x-text-input id="local" class="block mt-1 w-full" type="text" name="local"
                                    :value="old('local')" placeholder="Ex: Sala dos Desbravadores" required />
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="conteudo" :value="__('Conteúdo da Ata (Deliberações)')" />
                                <textarea id="conteudo" name="conteudo" rows="10"
                                    class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm"
                                    required>{{ old('conteudo') }}</textarea>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="participantes" :value="__('Participantes (Opcional)')" />
                                <x-text-input id="participantes" class="block mt-1 w-full" type="text"
                                    name="participantes" :value="old('participantes')" placeholder="Separe os nomes por vírgula" />
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                            <x-secondary-button onclick="window.history.back()">Cancelar</x-secondary-button>
                            <x-primary-button>Salvar Ata</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
