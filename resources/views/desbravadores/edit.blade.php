<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Desbravador: {{ $desbravador->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('desbravadores.update', $desbravador) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-8 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-yellow-800 dark:text-yellow-200">Situação Cadastral</h4>
                                <p class="text-xs text-yellow-600 dark:text-yellow-400">Desmarque para inativar este membro (mantém histórico).</p>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="ativo" id="ativo" value="1" {{ $desbravador->ativo ? 'checked' : '' }} class="w-6 h-6 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="ativo" class="ml-2 font-bold text-gray-900 dark:text-gray-300">ATIVO</label>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Dados do Clube</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="nome" :value="__('Nome Completo *')" />
                                    <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome" :value="old('nome', $desbravador->nome)" required />
                                </div>
                                <div>
                                    <x-input-label for="unidade_id" :value="__('Unidade *')" />
                                    <select name="unidade_id" id="unidade_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        @foreach($unidades as $unidade)
                                        <option value="{{ $unidade->id }}" {{ $desbravador->unidade_id == $unidade->id ? 'selected' : '' }}>{{ $unidade->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="classe_atual" :value="__('Classe Atual *')" />
                                    <select name="classe_atual" id="classe_atual" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        @foreach(['Amigo', 'Companheiro', 'Pesquisador', 'Pioneiro', 'Excursionista', 'Guia'] as $classe)
                                        <option value="{{ $classe }}" {{ $desbravador->classe_atual == $classe ? 'selected' : '' }}>{{ $classe }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="data_nascimento" :value="__('Data de Nascimento *')" />
                                    <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date" name="data_nascimento" :value="old('data_nascimento', $desbravador->data_nascimento->format('Y-m-d'))" required />
                                </div>
                                <div>
                                    <x-input-label for="sexo" :value="__('Sexo *')" />
                                    <select name="sexo" id="sexo" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="M" {{ $desbravador->sexo == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ $desbravador->sexo == 'F' ? 'selected' : '' }}>Feminino</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Contato e Família</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="email" :value="__('E-mail *')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $desbravador->email)" required />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="endereco" :value="__('Endereço Completo *')" />
                                    <x-text-input id="endereco" class="block mt-1 w-full" type="text" name="endereco" :value="old('endereco', $desbravador->endereco)" required />
                                </div>
                                <div>
                                    <x-input-label for="nome_responsavel" :value="__('Nome do Responsável *')" />
                                    <x-text-input id="nome_responsavel" class="block mt-1 w-full" type="text" name="nome_responsavel" :value="old('nome_responsavel', $desbravador->nome_responsavel)" required />
                                </div>
                                <div>
                                    <x-input-label for="telefone_responsavel" :value="__('Telefone do Responsável *')" />
                                    <x-text-input id="telefone_responsavel" class="block mt-1 w-full" type="text" name="telefone_responsavel" :value="old('telefone_responsavel', $desbravador->telefone_responsavel)" required />
                                </div>
                                <div>
                                    <x-input-label for="telefone" :value="__('Telefone Próprio (Opcional)')" />
                                    <x-text-input id="telefone" class="block mt-1 w-full" type="text" name="telefone" :value="old('telefone', $desbravador->telefone)" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-red-500 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Saúde</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="numero_sus" :value="__('Número do Cartão SUS *')" />
                                    <x-text-input id="numero_sus" class="block mt-1 w-full" type="text" name="numero_sus" :value="old('numero_sus', $desbravador->numero_sus)" required />
                                </div>
                                <div>
                                    <x-input-label for="tipo_sanguineo" :value="__('Tipo Sanguíneo')" />
                                    <x-text-input id="tipo_sanguineo" class="block mt-1 w-full" type="text" name="tipo_sanguineo" :value="old('tipo_sanguineo', $desbravador->tipo_sanguineo)" />
                                </div>
                                <div>
                                    <x-input-label for="plano_saude" :value="__('Plano de Saúde (Opcional)')" />
                                    <x-text-input id="plano_saude" class="block mt-1 w-full" type="text" name="plano_saude" :value="old('plano_saude', $desbravador->plano_saude)" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="alergias" :value="__('Alergias')" />
                                    <textarea name="alergias" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2">{{ old('alergias', $desbravador->alergias) }}</textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="medicamentos_continuos" :value="__('Medicamentos Contínuos')" />
                                    <textarea name="medicamentos_continuos" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2">{{ old('medicamentos_continuos', $desbravador->medicamentos_continuos) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('desbravadores.show', $desbravador) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Salvar Alterações') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>