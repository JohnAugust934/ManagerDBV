<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Novo Desbravador
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Campos marcados com <span class="text-red-500 font-bold">*</span> são obrigatórios para o cadastro.
                    </p>
                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('desbravadores.store') }}" method="POST">
                        @csrf

                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Dados do Clube
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="nome" :value="__('Nome Completo *')" />
                                    <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome" :value="old('nome')" required autofocus />
                                    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="unidade_id" :value="__('Unidade *')" />
                                    <select name="unidade_id" id="unidade_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Selecione...</option>
                                        @foreach($unidades as $unidade)
                                        <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('unidade_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="classe_atual" :value="__('Classe Atual *')" />
                                    <select name="classe_atual" id="classe_atual" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Selecione...</option>
                                        @foreach(['Amigo', 'Companheiro', 'Pesquisador', 'Pioneiro', 'Excursionista', 'Guia'] as $classe)
                                        <option value="{{ $classe }}">{{ $classe }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('classe_atual')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="data_nascimento" :value="__('Data de Nascimento *')" />
                                    <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date" name="data_nascimento" :value="old('data_nascimento')" required />
                                    <x-input-error :messages="$errors->get('data_nascimento')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="sexo" :value="__('Sexo *')" />
                                    <select name="sexo" id="sexo" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Selecione...</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Feminino</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('sexo')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Contato e Família
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="email" :value="__('E-mail *')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="exemplo@email.com" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="endereco" :value="__('Endereço Completo *')" />
                                    <x-text-input id="endereco" class="block mt-1 w-full" type="text" name="endereco" :value="old('endereco')" placeholder="Rua, Número, Bairro, Cidade" required />
                                    <x-input-error :messages="$errors->get('endereco')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="nome_responsavel" :value="__('Nome do Responsável *')" />
                                    <x-text-input id="nome_responsavel" class="block mt-1 w-full" type="text" name="nome_responsavel" :value="old('nome_responsavel')" required />
                                    <x-input-error :messages="$errors->get('nome_responsavel')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="telefone_responsavel" :value="__('Telefone do Responsável *')" />
                                    <x-text-input id="telefone_responsavel" class="block mt-1 w-full" type="text" name="telefone_responsavel" :value="old('telefone_responsavel')" placeholder="(00) 00000-0000" required />
                                    <x-input-error :messages="$errors->get('telefone_responsavel')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="telefone" :value="__('Telefone Próprio (Opcional)')" />
                                    <x-text-input id="telefone" class="block mt-1 w-full" type="text" name="telefone" :value="old('telefone')" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-red-500 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                Saúde
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="numero_sus" :value="__('Número do Cartão SUS *')" />
                                    <x-text-input id="numero_sus" class="block mt-1 w-full" type="text" name="numero_sus" :value="old('numero_sus')" placeholder="000 0000 0000 0000" required />
                                    <x-input-error :messages="$errors->get('numero_sus')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tipo_sanguineo" :value="__('Tipo Sanguíneo')" />
                                    <x-text-input id="tipo_sanguineo" class="block mt-1 w-full" type="text" name="tipo_sanguineo" :value="old('tipo_sanguineo')" placeholder="Ex: O+" />
                                </div>
                                <div>
                                    <x-input-label for="plano_saude" :value="__('Plano de Saúde (Opcional)')" />
                                    <x-text-input id="plano_saude" class="block mt-1 w-full" type="text" name="plano_saude" :value="old('plano_saude')" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="alergias" :value="__('Alergias (Separe por vírgula)')" />
                                    <textarea name="alergias" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2">{{ old('alergias') }}</textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="medicamentos_continuos" :value="__('Medicamentos Contínuos')" />
                                    <textarea name="medicamentos_continuos" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2">{{ old('medicamentos_continuos') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('desbravadores.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Salvar Cadastro') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>