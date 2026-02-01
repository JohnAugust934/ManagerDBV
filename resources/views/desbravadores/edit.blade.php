<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Editar: ') . $desbravador->nome }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('desbravadores.update', $desbravador) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div
                        class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-slate-700 pb-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            Dados Cadastrais
                        </h3>
                        <div class="flex items-center">
                            <label for="ativo" class="inline-flex items-center cursor-pointer">
                                <span class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300">Membro
                                    Ativo?</span>
                                <input type="checkbox" id="ativo" name="ativo" value="1" class="sr-only peer"
                                    {{ $desbravador->ativo ? 'checked' : '' }}>
                                <div
                                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="col-span-1 md:col-span-2">
                            <x-input-label for="nome" :value="__('Nome Completo')" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                :value="old('nome', $desbravador->nome)" required />
                        </div>

                        <div>
                            <x-input-label for="sexo" :value="__('Sexo')" />
                            <select id="sexo" name="sexo"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="M" {{ old('sexo', $desbravador->sexo) == 'M' ? 'selected' : '' }}>
                                    Masculino</option>
                                <option value="F" {{ old('sexo', $desbravador->sexo) == 'F' ? 'selected' : '' }}>
                                    Feminino</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="data_nascimento" :value="__('Data de Nascimento')" />
                            <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date"
                                name="data_nascimento" :value="old('data_nascimento', $desbravador->data_nascimento->format('Y-m-d'))" required />
                        </div>

                        <div>
                            <x-input-label for="unidade_id" :value="__('Unidade')" />
                            <select id="unidade_id" name="unidade_id"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="">Selecione...</option>
                                @foreach ($unidades as $unidade)
                                    <option value="{{ $unidade->id }}"
                                        {{ old('unidade_id', $desbravador->unidade_id) == $unidade->id ? 'selected' : '' }}>
                                        {{ $unidade->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="classe_atual" :value="__('Classe Atual')" />
                            <select id="classe_atual" name="classe_atual"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                @foreach (['Amigo', 'Companheiro', 'Pesquisador', 'Pioneiro', 'Excursionista', 'Guia', 'Líder'] as $classe)
                                    <option value="{{ $classe }}"
                                        {{ old('classe_atual', $desbravador->classe_atual) == $classe ? 'selected' : '' }}>
                                        {{ $classe }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <h3
                        class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-100 dark:border-slate-700 pb-2">
                        Contato e Responsáveis
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                :value="old('email', $desbravador->email)" required />
                        </div>

                        <div>
                            <x-input-label for="telefone" :value="__('Telefone / WhatsApp')" />
                            <x-text-input id="telefone" class="block mt-1 w-full" type="text" name="telefone"
                                :value="old('telefone', $desbravador->telefone)" />
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <x-input-label for="endereco" :value="__('Endereço')" />
                            <x-text-input id="endereco" class="block mt-1 w-full" type="text" name="endereco"
                                :value="old('endereco', $desbravador->endereco)" required />
                        </div>

                        <div>
                            <x-input-label for="nome_responsavel" :value="__('Nome do Responsável')" />
                            <x-text-input id="nome_responsavel" class="block mt-1 w-full" type="text"
                                name="nome_responsavel" :value="old('nome_responsavel', $desbravador->nome_responsavel)" required />
                        </div>

                        <div>
                            <x-input-label for="telefone_responsavel" :value="__('Telefone do Responsável')" />
                            <x-text-input id="telefone_responsavel" class="block mt-1 w-full" type="text"
                                name="telefone_responsavel" :value="old('telefone_responsavel', $desbravador->telefone_responsavel)" required />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <h3
                        class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-100 dark:border-slate-700 pb-2">
                        Ficha de Saúde
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="numero_sus" :value="__('Cartão SUS')" />
                            <x-text-input id="numero_sus" class="block mt-1 w-full" type="text" name="numero_sus"
                                :value="old('numero_sus', $desbravador->numero_sus)" required />
                        </div>

                        <div>
                            <x-input-label for="tipo_sanguineo" :value="__('Tipo Sanguíneo')" />
                            <select id="tipo_sanguineo" name="tipo_sanguineo"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="">Não informado</option>
                                @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                                    <option value="{{ $tipo }}"
                                        {{ old('tipo_sanguineo', $desbravador->tipo_sanguineo) == $tipo ? 'selected' : '' }}>
                                        {{ $tipo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="plano_saude" :value="__('Plano de Saúde')" />
                            <x-text-input id="plano_saude" class="block mt-1 w-full" type="text" name="plano_saude"
                                :value="old('plano_saude', $desbravador->plano_saude)" />
                        </div>

                        <div class="col-span-1 md:col-span-3">
                            <x-input-label for="alergias" :value="__('Alergias')" />
                            <textarea id="alergias" name="alergias" rows="2"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">{{ old('alergias', $desbravador->alergias) }}</textarea>
                        </div>

                        <div class="col-span-1 md:col-span-3">
                            <x-input-label for="medicamentos_continuos" :value="__('Uso Contínuo de Medicamentos')" />
                            <textarea id="medicamentos_continuos" name="medicamentos_continuos" rows="2"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">{{ old('medicamentos_continuos', $desbravador->medicamentos_continuos) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pb-12">
                    <x-secondary-button onclick="window.history.back()">
                        {{ __('Cancelar') }}
                    </x-secondary-button>

                    <x-primary-button class="bg-blue-600 hover:bg-blue-700">
                        {{ __('Atualizar Dados') }}
                    </x-primary-button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
