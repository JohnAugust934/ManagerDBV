<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Novo Lançamento') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="p-6">
                    <form method="POST" action="{{ route('caixa.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label :value="__('Tipo de Movimentação')" class="mb-2" />
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input type="radio" name="tipo" id="entrada" value="entrada"
                                        class="peer hidden" checked>
                                    <label for="entrada"
                                        class="block text-center px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-slate-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 peer-checked:text-green-700 dark:peer-checked:text-green-400 transition-all">
                                        <span class="font-bold">Entrada</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" name="tipo" id="saida" value="saida"
                                        class="peer hidden">
                                    <label for="saida"
                                        class="block text-center px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-slate-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 peer-checked:text-red-700 dark:peer-checked:text-red-400 transition-all">
                                        <span class="font-bold">Saída</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="valor" :value="__('Valor (R$)')" />
                                <x-text-input id="valor" class="block mt-1 w-full font-bold text-lg" type="number"
                                    step="0.01" name="valor" :value="old('valor')" required placeholder="0,00" />
                            </div>

                            <div>
                                <x-input-label for="data_movimentacao" :value="__('Data')" />
                                <x-text-input id="data_movimentacao" class="block mt-1 w-full" type="date"
                                    name="data_movimentacao" :value="old('data_movimentacao', date('Y-m-d'))" required />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="descricao" :value="__('Descrição')" />
                            <x-text-input id="descricao" class="block mt-1 w-full" type="text" name="descricao"
                                :value="old('descricao')" required placeholder="Ex: Venda de pães, Compra de material..." />
                        </div>

                        <div>
                            <x-input-label for="categoria" :value="__('Categoria (Opcional)')" />
                            <select id="categoria" name="categoria"
                                class="block mt-1 w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue focus:ring-dbv-blue rounded-lg shadow-sm">
                                <option value="">Selecione ou deixe em branco</option>
                                <option value="Mensalidades">Mensalidades</option>
                                <option value="Doações">Doações</option>
                                <option value="Eventos">Eventos</option>
                                <option value="Materiais">Materiais</option>
                                <option value="Alimentação">Alimentação</option>
                                <option value="Transporte">Transporte</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                            <x-secondary-button onclick="window.history.back()">Cancelar</x-secondary-button>
                            <x-primary-button class="w-full md:w-auto justify-center">Confirmar
                                Lançamento</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
