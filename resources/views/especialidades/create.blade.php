<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Cadastrar Especialidade') }}
        </h2>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-2xl mx-auto">
            <div class="ui-card overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40">
                    <h3 class="ui-title text-lg">Nova Especialidade</h3>
                    <p class="ui-subtitle mt-1">Adicione uma nova especialidade a biblioteca do clube.</p>
                </div>

                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('especialidades.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="nome" value="Nome da Especialidade *" />
                            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome')"
                                placeholder="Ex: Fogueiras e Cozinha ao Ar Livre" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('nome')" />
                        </div>

                        <div>
                            <x-input-label for="area" value="Area / Categoria *" />
                            <select id="area" name="area" class="ui-input mt-1" required>
                                <option value="" disabled selected>Selecione a area...</option>
                                @foreach (['ADRA', 'Artes e Habilidades Manuais', 'Atividades Agropecuarias', 'Atividades Missionarias e Comunitarias', 'Atividades Profissionais', 'Atividades Recreativas', 'Ciencia e Saude', 'Estudo da Natureza', 'Habilidades Domesticas', 'Mestrados'] as $area)
                                    <option value="{{ $area }}" {{ old('area') == $area? 'selected' : '' }}>
                                        {{ $area }}
                                    </option>
                                @endforeach
                                <option value="Outra">Outra</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('area')" />
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-5 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('especialidades.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                            <button type="submit" class="ui-btn-primary w-full sm:w-auto">Cadastrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
