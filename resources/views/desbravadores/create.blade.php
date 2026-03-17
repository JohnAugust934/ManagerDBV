<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Cadastrar Novo Desbravador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($errors->any())
                        <div
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400">
                            <p class="font-bold">Ops! Algo deu errado.</p>
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('desbravadores.store') }}" method="POST">
                        @csrf

                        <div class="mb-6">
                            <h3 class="text-lg font-bold mb-4 text-dbv-blue border-b pb-2">Dados Pessoais</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="nome" :value="__('Nome Completo *')" />
                                    <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                        :value="old('nome')" required autofocus />
                                    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="data_nascimento" :value="__('Data de Nascimento *')" />
                                    <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date"
                                        name="data_nascimento" :value="old('data_nascimento')" required />
                                    <x-input-error :messages="$errors->get('data_nascimento')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="sexo" :value="__('Sexo *')" />
                                    <select id="sexo" name="sexo"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="">Selecione...</option>
                                        <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino
                                        </option>
                                        <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino
                                        </option>
                                    </select>
                                    <x-input-error :messages="$errors->get('sexo')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="unidade_id" :value="__('Unidade *')" />
                                    <select id="unidade_id" name="unidade_id"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="">Selecione uma unidade...</option>
                                        @foreach ($unidades as $unidade)
                                            <option value="{{ $unidade->id }}"
                                                {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                                {{ $unidade->nome }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('unidade_id')" class="mt-2" />
                                </div>

                                <div class="col-span-2">
                                    <x-input-label for="classe_atual" :value="__('Classe Atual')" />
                                    <select id="classe_atual" name="classe_atual"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Selecione a classe...</option>
                                        @foreach ($classes as $classe)
                                            <option value="{{ $classe->id }}"
                                                {{ old('classe_atual') == $classe->id ? 'selected' : '' }}>
                                                {{ $classe->nome }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('classe_atual')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-bold mb-4 text-dbv-blue border-b pb-2">Documentos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="cpf" :value="__('CPF *')" />
                                    <x-text-input id="cpf" class="block mt-1 w-full" type="text" name="cpf"
                                        :value="old('cpf')" required placeholder="000.000.000-00" />
                                    <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="rg" :value="__('RG')" />
                                    <x-text-input id="rg" class="block mt-1 w-full" type="text" name="rg"
                                        :value="old('rg')" placeholder="00.000.000-X" />
                                    <x-input-error :messages="$errors->get('rg')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-bold mb-4 text-dbv-blue border-b pb-2">Contato e Responsável</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="email" :value="__('Email *')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                        :value="old('email')" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="telefone" :value="__('Telefone (Celular)')" />
                                    <x-text-input id="telefone" class="block mt-1 w-full" type="text"
                                        name="telefone" :value="old('telefone')" placeholder="(XX) XXXXX-XXXX" />
                                    <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
                                </div>
                                <div class="col-span-2">
                                    <x-input-label for="endereco" :value="__('Endereço Completo *')" />
                                    <x-text-input id="endereco" class="block mt-1 w-full" type="text"
                                        name="endereco" :value="old('endereco')" required />
                                    <x-input-error :messages="$errors->get('endereco')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="nome_responsavel" :value="__('Nome do Responsável *')" />
                                    <x-text-input id="nome_responsavel" class="block mt-1 w-full" type="text"
                                        name="nome_responsavel" :value="old('nome_responsavel')" required />
                                    <x-input-error :messages="$errors->get('nome_responsavel')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="telefone_responsavel" :value="__('Telefone do Responsável *')" />
                                    <x-text-input id="telefone_responsavel" class="block mt-1 w-full" type="text"
                                        name="telefone_responsavel" :value="old('telefone_responsavel')" required
                                        placeholder="(XX) XXXXX-XXXX" />
                                    <x-input-error :messages="$errors->get('telefone_responsavel')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-bold mb-4 text-dbv-blue border-b pb-2">Ficha Médica</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="numero_sus" :value="__('Cartão SUS (Obrigatório) *')" />
                                    <x-text-input id="numero_sus" class="block mt-1 w-full" type="text"
                                        name="numero_sus" :value="old('numero_sus')" required />
                                    <x-input-error :messages="$errors->get('numero_sus')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tipo_sanguineo" :value="__('Tipo Sanguíneo')" />
                                    <select id="tipo_sanguineo" name="tipo_sanguineo"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Não sei</option>
                                        @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                                            <option value="{{ $tipo }}"
                                                {{ old('tipo_sanguineo') == $tipo ? 'selected' : '' }}>
                                                {{ $tipo }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('tipo_sanguineo')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="plano_saude" :value="__('Plano de Saúde (Opcional)')" />
                                    <x-text-input id="plano_saude" class="block mt-1 w-full" type="text"
                                        name="plano_saude" :value="old('plano_saude')" />
                                    <x-input-error :messages="$errors->get('plano_saude')" class="mt-2" />
                                </div>
                                <div class="col-span-3 md:col-span-1">
                                    <x-input-label for="alergias" :value="__('Alergias')" />
                                    <x-text-input id="alergias" class="block mt-1 w-full" type="text"
                                        name="alergias" :value="old('alergias')" placeholder="Ex: Dipirona, poeira..." />
                                </div>
                                <div class="col-span-3 md:col-span-2">
                                    <x-input-label for="medicamentos_continuos" :value="__('Medicamentos de Uso Contínuo')" />
                                    <x-text-input id="medicamentos_continuos" class="block mt-1 w-full"
                                        type="text" name="medicamentos_continuos" :value="old('medicamentos_continuos')"
                                        placeholder="Ex: Insulina..." />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('desbravadores.index') }}"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline mr-4">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('Cadastrar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
