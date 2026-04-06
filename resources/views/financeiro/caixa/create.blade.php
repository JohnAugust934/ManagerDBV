<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Nova Movimentação') }}
        </h2>
    </x-slot>

    {{-- 
        Alpine Data: 
        Gerencia o estado do formulário e as listas de categorias dinâmicas.
    --}}
    <div class="ui-page" x-data="{
        tipo: '{{ old('tipo', 'saida') }}',
        valor: '{{ old('valor') }}',
        descricao: '{{ old('descricao') }}',
        categoria: '{{ old('categoria') }}',
        showModal: false,
    
        // Listas de Categorias por Tipo
        opcoesCategorias: {
            entrada: [
                'Mensalidade',
                'Ofertas e Doações',
                'Inscrições de Eventos',
                'Venda de Uniformes',
                'Cantina',
                'Campanha',
                'Outros'
            ],
            saida: [
                'Materiais de Secretaria',
                'Alimentação/Lanche',
                'Transporte/Combustível',
                'Compra de Uniformes',
                'Equipamentos',
                'Taxas e Repasses',
                'Devolução',
                'Outros'
            ]
        },
    
        // Retorna a lista correta baseada no tipo selecionado
        get categoriasAtuais() {
            return this.opcoesCategorias[this.tipo];
        },
    
        submitForm() {
            this.$refs.form.submit();
        }
    }">
        <div class="max-w-3xl mx-auto">

            <div
                class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">

                {{-- Cabeçalho do Card --}}
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Detalhes do Lançamento
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Preencha os dados com atenção. Lançamentos não podem ser excluídos, apenas estornados.
                    </p>
                </div>

                <div class="p-6 md:p-8">
                    <form id="caixa-form" method="POST" action="{{ route('caixa.store') }}" x-ref="form"
                        class="space-y-8">
                        @csrf

                        {{-- 1. Seletor de Tipo (Cards Visuais) --}}
                        <div>
                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Tipo de Movimentação <span class="text-red-500">*</span>
                            </span>
                            <div class="grid grid-cols-2 gap-4">
                                {{-- Opção Entrada --}}
                                <label class="cursor-pointer relative">
                                    {{-- Ao mudar o tipo, limpamos a categoria para evitar inconsistência --}}
                                    <input type="radio" name="tipo" value="entrada" x-model="tipo"
                                        x-on:change="categoria = ''" class="peer sr-only">
                                    <div
                                        class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-green-200 dark:hover:border-green-800 bg-white dark:bg-gray-800 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 peer-checked:shadow-md flex flex-col items-center justify-center text-center gap-2 h-32">
                                        <div
                                            class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-200 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                            </svg>
                                        </div>
                                        <span
                                            class="font-bold text-gray-600 dark:text-gray-300 peer-checked:text-green-700 dark:peer-checked:text-green-400">Entrada</span>
                                    </div>
                                </label>

                                {{-- Opção Saída --}}
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="tipo" value="saida" x-model="tipo"
                                        x-on:change="categoria = ''" class="peer sr-only">
                                    <div
                                        class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-200 dark:hover:border-red-800 bg-white dark:bg-gray-800 transition-all peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 peer-checked:shadow-md flex flex-col items-center justify-center text-center gap-2 h-32">
                                        <div
                                            class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-200 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                        </div>
                                        <span
                                            class="font-bold text-gray-600 dark:text-gray-300 peer-checked:text-red-700 dark:peer-checked:text-red-400">Saída</span>
                                    </div>
                                </label>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
                        </div>

                        {{-- 2. Valor --}}
                        <div>
                            <x-input-label for="valor" value="Valor (R$) *" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-lg">R$</span>
                                </div>
                                <input id="valor" name="valor" type="number" step="0.01" min="0.01"
                                    x-model="valor"
                                    class="ui-input pl-10 text-2xl font-bold"
                                    placeholder="0,00" required />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('valor')" />
                        </div>

                        {{-- 3. Grid para Categoria e Data --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Categoria (Select Dinâmico) --}}
                            <div>
                                <x-input-label for="categoria" value="Categoria *" />
                                <div class="relative mt-1">
                                    <select id="categoria" name="categoria" x-model="categoria"
                                        class="ui-input"
                                        required>
                                        <option value="" disabled selected>Selecione uma opção</option>
                                        <template x-for="opcao in categoriasAtuais" :key="opcao">
                                            <option :value="opcao" x-text="opcao"
                                                :selected="opcao == '{{ old('categoria') }}'"></option>
                                        </template>
                                    </select>

                                    {{-- Ícone absoluto para indicar que é um select --}}
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('categoria')" />
                            </div>

                            {{-- Data --}}
                            <div>
                                <x-input-label for="data_movimentacao" value="Data da Movimentação *" />
                                <x-text-input id="data_movimentacao" name="data_movimentacao" type="date"
                                    class="mt-1 block w-full" value="{{ old('data_movimentacao', date('Y-m-d')) }}"
                                    required />
                                <x-input-error class="mt-2" :messages="$errors->get('data_movimentacao')" />
                            </div>
                        </div>

                        {{-- 4. Descrição --}}
                        <div>
                            <x-input-label for="descricao" value="Descrição Detalhada *" />
                            <x-text-input id="descricao" name="descricao" type="text" x-model="descricao"
                                class="mt-1 block w-full" placeholder="Ex: Referente a venda de 50 trufas na praça"
                                required />
                            <x-input-error class="mt-2" :messages="$errors->get('descricao')" />
                        </div>

                        {{-- Botões de Ação --}}
                        <div
                            class="flex items-center justify-end pt-6 border-t border-gray-100 dark:border-gray-700 gap-4">
                            <a href="{{ route('caixa.index') }}"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 font-medium">
                                Cancelar
                            </a>

                            {{-- Botão que abre o Modal --}}
                            <button type="button"
                                x-on:click="if(valor && descricao && categoria) { showModal = true } else { alert('Preencha todos os campos obrigatórios') }"
                                class="ui-btn-primary">
                                Registrar
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL DE CONFIRMAÇÃO (ALPINE.JS) --}}
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"
                x-on:click="showModal = false"></div>

            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                {{-- Card do Modal --}}
                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-gray-700">
                    <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">

                            {{-- Ícone de Atenção --}}
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>

                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white"
                                    id="modal-title">
                                    Confirmar Lançamento?
                                </h3>
                                <div
                                    class="mt-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 space-y-2 text-sm text-gray-600 dark:text-gray-300 text-left">

                                    <div
                                        class="flex justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                        <span>Tipo:</span>
                                        <span class="font-bold uppercase"
                                            :class="tipo === 'entrada'? 'text-green-600' : 'text-red-600'"
                                            x-text="tipo"></span>
                                    </div>

                                    <div
                                        class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
                                        <span>Categoria:</span>
                                        <span class="font-medium text-gray-900 dark:text-white"
                                            x-text="categoria"></span>
                                    </div>

                                    <div
                                        class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
                                        <span>Valor:</span>
                                        <span class="font-bold text-gray-900 dark:text-white"
                                            x-text="'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                                    </div>

                                    <div class="pt-2">
                                        <span class="block text-xs text-gray-500">Descrição:</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-200 break-all"
                                            x-text="descricao"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botões do Modal --}}
                    <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="button" x-on:click="submitForm()"
                            class="inline-flex w-full justify-center rounded-lg bg-dbv-blue dark:bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 dark:hover:bg-blue-500 sm:ml-3 sm:w-auto">
                            Confirmar e Salvar
                        </button>
                        <button type="button" x-on:click="showModal = false"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">
                            Corrigir
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>


