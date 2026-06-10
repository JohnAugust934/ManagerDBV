<x-app-layout>
    <x-slot name="header">Nova Movimentação</x-slot>

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
                class="bg-white dark:bg-slate-800 shadow-lg rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden">

                {{-- Cabeçalho do Card --}}
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        Detalhes do Lançamento
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Preencha os dados com atenção. Lançamentos não podem ser excluídos, apenas estornados.
                    </p>
                </div>

                <div class="p-6 md:p-8">
                    <form id="caixa-form" method="POST" action="{{ route('caixa.store') }}" x-ref="form"
                        class="space-y-8">
                        @csrf

                        {{-- 1. Seletor de Tipo (Cards Visuais) --}}
                        <div>
                            <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                                Tipo de Movimentação <span class="text-red-500">*</span>
                            </span>
                            <div class="grid grid-cols-2 gap-4">
                                {{-- Opção Entrada --}}
                                <label class="cursor-pointer relative">
                                    {{-- Ao mudar o tipo, limpamos a categoria para evitar inconsistência --}}
                                    <input type="radio" name="tipo" value="entrada" x-model="tipo"
                                        x-on:change="categoria = ''" class="peer sr-only">
                                    <div
                                        class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-green-200 dark:hover:border-green-800 bg-white dark:bg-slate-800 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 peer-checked:shadow-md flex flex-col items-center justify-center text-center gap-2 h-32">
                                        <div
                                            class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-200 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                            </svg>
                                        </div>
                                        <span
                                            class="font-bold text-slate-600 dark:text-slate-300 peer-checked:text-green-700 dark:peer-checked:text-green-400">Entrada</span>
                                    </div>
                                </label>

                                {{-- Opção Saída --}}
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="tipo" value="saida" x-model="tipo"
                                        x-on:change="categoria = ''" class="peer sr-only">
                                    <div
                                        class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-red-200 dark:hover:border-red-800 bg-white dark:bg-slate-800 transition-all peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 peer-checked:shadow-md flex flex-col items-center justify-center text-center gap-2 h-32">
                                        <div
                                            class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-200 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                        </div>
                                        <span
                                            class="font-bold text-slate-600 dark:text-slate-300 peer-checked:text-red-700 dark:peer-checked:text-red-400">Saída</span>
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
                                    <span class="text-slate-500 sm:text-lg">R$</span>
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
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-700 dark:text-slate-300">
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
                            class="flex items-center justify-end pt-6 border-t border-slate-100 dark:border-slate-700 gap-4">
                            <a href="{{ route('caixa.index') }}"
                                class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium">
                                Cancelar
                            </a>

                            {{-- Botão que abre o Modal --}}
                            <button type="button"
                                x-on:click="if(valor && descricao && categoria) { showModal = true } else { window.notify('Preencha todos os campos obrigatórios', 'warning') }"
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
                class="fixed inset-0 bg-gray-900/75 dark:bg-black/80 backdrop-blur-sm transition-opacity"
                x-on:click="showModal = false"></div>

            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                {{-- Card do Modal --}}
                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-slate-700">
                    <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">

                            {{-- Ícone de Atenção --}}
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 sm:mx-0 sm:h-12 sm:w-12">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                </svg>
                            </div>

                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-semibold leading-6 text-slate-900 dark:text-white"
                                    id="modal-title">
                                    Confirmar Lançamento?
                                </h3>
                                <div
                                    class="mt-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 space-y-2 text-sm text-slate-600 dark:text-slate-300 text-left">

                                    <div
                                        class="flex justify-between border-b border-slate-200 dark:border-slate-700 pb-2">
                                        <span>Tipo:</span>
                                        <span class="font-bold uppercase"
                                            :class="tipo === 'entrada'? 'text-green-600' : 'text-red-600'"
                                            x-text="tipo"></span>
                                    </div>

                                    <div
                                        class="flex justify-between border-b border-slate-200 dark:border-slate-700 py-2">
                                        <span>Categoria:</span>
                                        <span class="font-medium text-slate-900 dark:text-white"
                                            x-text="categoria"></span>
                                    </div>

                                    <div
                                        class="flex justify-between border-b border-slate-200 dark:border-slate-700 py-2">
                                        <span>Valor:</span>
                                        <span class="font-bold text-slate-900 dark:text-white"
                                            x-text="'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                                    </div>

                                    <div class="pt-2">
                                        <span class="block text-xs text-slate-500">Descrição:</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200 break-all"
                                            x-text="descricao"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botões do Modal --}}
                    <div class="px-4 pb-6 pt-2 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <button type="button" x-on:click="showModal = false"
                            class="ui-btn-secondary px-6 w-full sm:w-auto text-sm">
                            Corrigir
                        </button>
                        <button type="button" x-on:click="submitForm()"
                            class="ui-btn-primary px-6 w-full sm:w-auto text-sm">
                            Confirmar e Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>


