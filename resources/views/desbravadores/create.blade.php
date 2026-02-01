<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Novo Desbravador') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('desbravadores.store') }}" class="space-y-6">
                @csrf

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <h3
                        class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-100 dark:border-slate-700 pb-2">
                        Dados Pessoais
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="col-span-1 md:col-span-2">
                            <x-input-label for="nome" :value="__('Nome Completo')" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                :value="old('nome')" required autofocus />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="sexo" :value="__('Sexo')" />
                            <select id="sexo" name="sexo"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino</option>
                            </select>
                            <x-input-error :messages="$errors->get('sexo')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="data_nascimento" :value="__('Data de Nascimento')" />
                            <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date"
                                name="data_nascimento" :value="old('data_nascimento')" required />
                            <x-input-error :messages="$errors->get('data_nascimento')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="unidade_id" :value="__('Unidade')" />
                            <select id="unidade_id" name="unidade_id"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="">Selecione...</option>
                                @foreach ($unidades as $unidade)
                                    <option value="{{ $unidade->id }}"
                                        {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>{{ $unidade->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('unidade_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="classe_atual" :value="__('Classe Atual')" />
                            <select id="classe_atual" name="classe_atual"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="Amigo">Amigo</option>
                                <option value="Companheiro">Companheiro</option>
                                <option value="Pesquisador">Pesquisador</option>
                                <option value="Pioneiro">Pioneiro</option>
                                <option value="Excursionista">Excursionista</option>
                                <option value="Guia">Guia</option>
                                <option value="Líder">Líder</option>
                            </select>
                            <x-input-error :messages="$errors->get('classe_atual')" class="mt-2" />
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
                            <x-input-label for="email" :value="__('Email (Pessoal ou Responsável)')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="telefone" :value="__('Telefone / WhatsApp')" />
                            <x-text-input id="telefone" class="block mt-1 w-full" type="text" name="telefone"
                                :value="old('telefone')" placeholder="(XX) 9XXXX-XXXX" />
                            <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <x-input-label for="endereco" :value="__('Endereço Completo')" />
                            <x-text-input id="endereco" class="block mt-1 w-full" type="text" name="endereco"
                                :value="old('endereco')" required />
                        </div>

                        <div>
                            <x-input-label for="nome_responsavel" :value="__('Nome do Responsável')" />
                            <x-text-input id="nome_responsavel" class="block mt-1 w-full" type="text"
                                name="nome_responsavel" :value="old('nome_responsavel')" required />
                        </div>

                        <div>
                            <x-input-label for="telefone_responsavel" :value="__('Telefone do Responsável')" />
                            <x-text-input id="telefone_responsavel" class="block mt-1 w-full" type="text"
                                name="telefone_responsavel" :value="old('telefone_responsavel')" required />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <h3
                        class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-100 dark:border-slate-700 pb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                        Ficha de Saúde
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="numero_sus" :value="__('Cartão SUS')" />
                            <x-text-input id="numero_sus" class="block mt-1 w-full" type="text" name="numero_sus"
                                :value="old('numero_sus')" required />
                        </div>

                        <div>
                            <x-input-label for="tipo_sanguineo" :value="__('Tipo Sanguíneo')" />
                            <select id="tipo_sanguineo" name="tipo_sanguineo"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="">Não sei / Não informado</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="plano_saude" :value="__('Plano de Saúde (Opcional)')" />
                            <x-text-input id="plano_saude" class="block mt-1 w-full" type="text"
                                name="plano_saude" :value="old('plano_saude')" />
                        </div>

                        <div class="col-span-1 md:col-span-3">
                            <x-input-label for="alergias" :value="__('Alergias (Alimentos, Remédios, Insetos)')" />
                            <textarea id="alergias" name="alergias" rows="2"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm"
                                placeholder="Caso não tenha, deixe em branco.">{{ old('alergias') }}</textarea>
                        </div>

                        <div class="col-span-1 md:col-span-3">
                            <x-input-label for="medicamentos_continuos" :value="__('Uso Contínuo de Medicamentos?')" />
                            <textarea id="medicamentos_continuos" name="medicamentos_continuos" rows="2"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm"
                                placeholder="Liste quais medicamentos e horários.">{{ old('medicamentos_continuos') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pb-12">
                    <x-secondary-button onclick="window.history.back()">
                        {{ __('Cancelar') }}
                    </x-secondary-button>

                    <x-primary-button class="bg-green-600 hover:bg-green-700 focus:ring-green-500">
                        {{ __('Salvar Cadastro') }}
                    </x-primary-button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
