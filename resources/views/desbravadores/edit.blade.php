<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Editar Desbravador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('desbravadores.update', $desbravador) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-6 flex justify-between items-center">
                            <div class="flex-1 mr-4">
                                <x-input-label for="nome" :value="__('Nome Completo')" />
                                <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                    :value="old('nome', $desbravador->nome)" required />
                                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            </div>
                            <div class="flex items-center mt-6">
                                <input id="ativo" type="checkbox" name="ativo" value="1"
                                    {{ old('ativo', $desbravador->ativo) ? 'checked' : '' }}
                                    class="w-5 h-5 text-dbv-blue bg-gray-100 border-gray-300 rounded focus:ring-dbv-blue dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="ativo"
                                    class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Ativo</label>
                            </div>
                        </div>

                        <div class="mb-6 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                            <h3 class="text-md font-bold mb-3 text-dbv-blue">Dados do Clube</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="unidade_id" :value="__('Unidade')" />
                                    <select id="unidade_id" name="unidade_id"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Selecione a classe...</option>
                                        @foreach ($classes as $classe)
                                            <option value="{{ $classe->id }}"
                                                {{ old('classe_atual', $desbravador->classe_atual) == $classe->id ? 'selected' : '' }}>
                                                {{ $classe->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="data_nascimento" :value="__('Nascimento')" />
                                    <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date"
                                        name="data_nascimento" :value="old(
                                            'data_nascimento',
                                            $desbravador->data_nascimento?->format('Y-m-d'),
                                        )" required />
                                </div>
                                <div>
                                    <x-input-label for="sexo" :value="__('Sexo')" />
                                    <select id="sexo" name="sexo"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="M"
                                            {{ old('sexo', $desbravador->sexo) == 'M' ? 'selected' : '' }}>Masculino
                                        </option>
                                        <option value="F"
                                            {{ old('sexo', $desbravador->sexo) == 'F' ? 'selected' : '' }}>Feminino
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                            <h3 class="text-md font-bold mb-3 text-dbv-blue">Documentos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="cpf" :value="__('CPF *')" />
                                    <x-text-input id="cpf" class="block mt-1 w-full" type="text" name="cpf"
                                        :value="old('cpf', $desbravador->cpf)" required />
                                    <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="rg" :value="__('RG')" />
                                    <x-text-input id="rg" class="block mt-1 w-full" type="text" name="rg"
                                        :value="old('rg', $desbravador->rg)" />
                                    <x-input-error :messages="$errors->get('rg')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-md font-bold mb-3 text-dbv-blue">Contato</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                        :value="old('email', $desbravador->email)" />
                                </div>
                                <div>
                                    <x-input-label for="telefone" :value="__('Telefone')" />
                                    <x-text-input id="telefone" class="block mt-1 w-full" type="text"
                                        name="telefone" :value="old('telefone', $desbravador->telefone)" />
                                </div>
                                <div class="col-span-2">
                                    <x-input-label for="endereco" :value="__('Endereço')" />
                                    <x-text-input id="endereco" class="block mt-1 w-full" type="text"
                                        name="endereco" :value="old('endereco', $desbravador->endereco)" />
                                </div>
                                <div>
                                    <x-input-label for="nome_responsavel" :value="__('Responsável')" />
                                    <x-text-input id="nome_responsavel" class="block mt-1 w-full" type="text"
                                        name="nome_responsavel" :value="old('nome_responsavel', $desbravador->nome_responsavel)" />
                                </div>
                                <div>
                                    <x-input-label for="telefone_responsavel" :value="__('Tel. Responsável')" />
                                    <x-text-input id="telefone_responsavel" class="block mt-1 w-full" type="text"
                                        name="telefone_responsavel" :value="old('telefone_responsavel', $desbravador->telefone_responsavel)" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-md font-bold mb-3 text-dbv-blue">Saúde</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="numero_sus" :value="__('Cartão SUS')" />
                                    <x-text-input id="numero_sus" class="block mt-1 w-full" type="text"
                                        name="numero_sus" :value="old('numero_sus', $desbravador->numero_sus)" />
                                </div>
                                <div>
                                    <x-input-label for="tipo_sanguineo" :value="__('Tipo Sanguíneo')" />
                                    <select id="tipo_sanguineo" name="tipo_sanguineo"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                                    <x-text-input id="plano_saude" class="block mt-1 w-full" type="text"
                                        name="plano_saude" :value="old('plano_saude', $desbravador->plano_saude)" />
                                </div>
                                <div class="col-span-3 md:col-span-1">
                                    <x-input-label for="alergias" :value="__('Alergias')" />
                                    <x-text-input id="alergias" class="block mt-1 w-full" type="text"
                                        name="alergias" :value="old('alergias', $desbravador->alergias)" />
                                </div>
                                <div class="col-span-3 md:col-span-2">
                                    <x-input-label for="medicamentos_continuos" :value="__('Medicamentos')" />
                                    <x-text-input id="medicamentos_continuos" class="block mt-1 w-full"
                                        type="text" name="medicamentos_continuos" :value="old('medicamentos_continuos', $desbravador->medicamentos_continuos)" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <button type="button" class="text-red-600 hover:text-red-800 text-sm font-bold"
                                onclick="if(confirm('Tem certeza? Isso apagará todo o histórico.')) document.getElementById('delete-form').submit()">
                                Excluir Desbravador
                            </button>

                            <div class="flex items-center">
                                <a href="{{ route('desbravadores.index') }}"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline mr-4">
                                    {{ __('Cancelar') }}
                                </a>
                                <x-primary-button>
                                    {{ __('Salvar Alterações') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('desbravadores.destroy', $desbravador) }}"
                        method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
