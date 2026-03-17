<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-dbv-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            {{ __('Novo Desbravador') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">

        <div class="px-4 sm:px-0 mb-4">
            <a href="{{ route('desbravadores.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-dbv-blue dark:text-gray-400 dark:hover:text-blue-400 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar para a lista
            </a>
        </div>

        <div
            class="bg-white dark:bg-slate-800 shadow-sm sm:rounded-2xl border-y sm:border border-gray-100 dark:border-slate-700 overflow-hidden">
            <div class="p-4 sm:p-8">

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Ops! Verifique os erros
                                    abaixo:</h3>
                                <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('desbravadores.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <div
                        class="bg-gray-50/50 dark:bg-slate-800/50 p-4 sm:p-6 rounded-xl border border-gray-100 dark:border-slate-700">
                        <h3 class="text-base font-bold text-dbv-blue dark:text-blue-400 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-dbv-yellow" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Dados Pessoais
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="nome" :value="__('Nome Completo *')" />
                                <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                    :value="old('nome')" required autofocus />
                            </div>

                            <div>
                                <x-input-label for="data_nascimento" :value="__('Data de Nascimento *')" />
                                <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date"
                                    name="data_nascimento" :value="old('data_nascimento')" required />
                            </div>

                            <div>
                                <x-input-label for="sexo" :value="__('Sexo *')" />
                                <select id="sexo" name="sexo"
                                    class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-300 focus:border-dbv-blue focus:ring-dbv-blue rounded-md shadow-sm"
                                    required>
                                    <option value="">Selecione...</option>
                                    <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label for="unidade_id" :value="__('Unidade *')" />
                                <select id="unidade_id" name="unidade_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-300 focus:border-dbv-blue focus:ring-dbv-blue rounded-md shadow-sm"
                                    required>
                                    <option value="">Selecione uma unidade...</option>
                                    @foreach ($unidades as $unidade)
                                        <option value="{{ $unidade->id }}"
                                            {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                            {{ $unidade->nome }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="classe_atual" :value="__('Classe Atual')" />
                                <select id="classe_atual" name="classe_atual"
                                    class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-300 focus:border-dbv-blue focus:ring-dbv-blue rounded-md shadow-sm">
                                    <option value="">Selecione a classe...</option>
                                    @foreach ($classes as $classe)
                                        <option value="{{ $classe->id }}"
                                            {{ old('classe_atual') == $classe->id ? 'selected' : '' }}>
                                            {{ $classe->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gray-50/50 dark:bg-slate-800/50 p-4 sm:p-6 rounded-xl border border-gray-100 dark:border-slate-700">
                        <h3 class="text-base font-bold text-dbv-blue dark:text-blue-400 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-dbv-yellow" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                            Documentos
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-4">
                            <div>
                                <x-input-label for="cpf" :value="__('CPF *')" />
                                <x-text-input id="cpf" class="block mt-1 w-full font-mono" type="text"
                                    name="cpf" :value="old('cpf')" required placeholder="000.000.000-00" />
                            </div>
                            <div>
                                <x-input-label for="rg" :value="__('RG')" />
                                <x-text-input id="rg" class="block mt-1 w-full font-mono" type="text"
                                    name="rg" :value="old('rg')" placeholder="00.000.000-X" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gray-50/50 dark:bg-slate-800/50 p-4 sm:p-6 rounded-xl border border-gray-100 dark:border-slate-700">
                        <h3 class="text-base font-bold text-dbv-blue dark:text-blue-400 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-dbv-yellow" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Contato e Responsável
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-4">
                            <div>
                                <x-input-label for="email" :value="__('Email *')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                    :value="old('email')" required />
                            </div>
                            <div>
                                <x-input-label for="telefone" :value="__('Telefone (Celular)')" />
                                <x-text-input id="telefone" class="block mt-1 w-full" type="text"
                                    name="telefone" :value="old('telefone')" placeholder="(XX) XXXXX-XXXX" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="endereco" :value="__('Endereço Completo *')" />
                                <x-text-input id="endereco" class="block mt-1 w-full" type="text"
                                    name="endereco" :value="old('endereco')" required
                                    placeholder="Rua, Número, Bairro, Cidade - Estado" />
                            </div>
                            <div>
                                <x-input-label for="nome_responsavel" :value="__('Nome do Responsável *')" />
                                <x-text-input id="nome_responsavel" class="block mt-1 w-full" type="text"
                                    name="nome_responsavel" :value="old('nome_responsavel')" required />
                            </div>
                            <div>
                                <x-input-label for="telefone_responsavel" :value="__('Telefone do Responsável *')" />
                                <x-text-input id="telefone_responsavel" class="block mt-1 w-full" type="text"
                                    name="telefone_responsavel" :value="old('telefone_responsavel')" required
                                    placeholder="(XX) XXXXX-XXXX" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-red-50/30 dark:bg-red-900/10 p-4 sm:p-6 rounded-xl border border-red-100 dark:border-red-900/30">
                        <h3 class="text-base font-bold text-red-600 dark:text-red-400 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Ficha Médica
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-y-5 gap-x-4">
                            <div class="sm:col-span-3">
                                <x-input-label for="numero_sus" :value="__('Cartão SUS (Obrigatório) *')" />
                                <x-text-input id="numero_sus" class="block mt-1 w-full font-mono" type="text"
                                    name="numero_sus" :value="old('numero_sus')" required />
                            </div>
                            <div class="sm:col-span-1">
                                <x-input-label for="tipo_sanguineo" :value="__('Tipo Sang.')" />
                                <select id="tipo_sanguineo" name="tipo_sanguineo"
                                    class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                    <option value="">Não sei</option>
                                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                                        <option value="{{ $tipo }}"
                                            {{ old('tipo_sanguineo') == $tipo ? 'selected' : '' }}>{{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="plano_saude" :value="__('Plano de Saúde')" />
                                <x-text-input id="plano_saude" class="block mt-1 w-full" type="text"
                                    name="plano_saude" :value="old('plano_saude')" placeholder="Opcional" />
                            </div>
                            <div class="sm:col-span-6">
                                <x-input-label for="alergias" :value="__('Alergias (Se houver)')" />
                                <x-text-input id="alergias" class="block mt-1 w-full" type="text"
                                    name="alergias" :value="old('alergias')"
                                    placeholder="Ex: Dipirona, poeira, amendoim..." />
                            </div>
                            <div class="sm:col-span-6">
                                <x-input-label for="medicamentos_continuos" :value="__('Medicamentos de Uso Contínuo')" />
                                <x-text-input id="medicamentos_continuos" class="block mt-1 w-full" type="text"
                                    name="medicamentos_continuos" :value="old('medicamentos_continuos')"
                                    placeholder="Ex: Insulina, bombinha de asma..." />
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-slate-700">
                        <a href="{{ route('desbravadores.index') }}"
                            class="w-full sm:w-auto text-center px-4 py-3 sm:py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 bg-dbv-blue border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 active:bg-blue-900 transition shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Cadastrar Desbravador') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
