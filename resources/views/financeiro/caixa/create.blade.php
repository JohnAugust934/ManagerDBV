<x-app-layout>
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

            <x-page-title title="Nova Movimentação" :back="route('caixa.index')" />

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

        {{-- MODAL DE CONFIRMAÇÃO --}}
        <template x-teleport="body">
            <div x-show="showModal"
                 style="display:none"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                 role="dialog" aria-modal="true">

                {{-- Overlay --}}
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm"
                     @click="showModal = false" aria-hidden="true"></div>

                {{-- Painel --}}
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                     class="relative ui-card w-full max-w-md p-0 shadow-2xl shadow-black/30 text-left z-10 overflow-hidden">

                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border transition-colors"
                                 :class="tipo === 'entrada'
                                     ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30'
                                     : 'bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 border-red-200 dark:border-red-500/30'">
                                <template x-if="tipo === 'entrada'">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                </template>
                                <template x-if="tipo === 'saida'">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                </template>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Confirmar Lançamento</h3>
                                <p class="text-[11px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500"
                                   x-text="tipo === 'entrada' ? 'Fluxo de Caixa Positivo' : 'Fluxo de Caixa Negativo'"></p>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-inner space-y-3">
                            <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipo</span>
                                <span class="text-[11px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg"
                                      :class="tipo === 'entrada' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400'"
                                      x-text="tipo === 'entrada' ? 'Entrada' : 'Saída'"></span>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Categoria</span>
                                <span class="font-bold text-slate-800 dark:text-white text-sm" x-text="categoria"></span>
                            </div>
                            <div class="flex justify-between items-end border-b border-slate-200 dark:border-slate-700 pb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor</span>
                                <span class="text-3xl font-black"
                                      :class="tipo === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                                      x-text="'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Descrição</p>
                                <p class="font-bold text-slate-800 dark:text-white text-sm break-words" x-text="descricao"></p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 sm:px-8 py-5 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row gap-3 justify-end items-center">
                        <button type="button" @click="showModal = false"
                                class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">
                            Corrigir
                        </button>
                        <button type="button" @click="submitForm()"
                                class="w-full sm:w-auto px-6 py-3 rounded-xl font-black text-sm text-white transition-all shadow-lg active:scale-95 flex justify-center items-center gap-2"
                                :class="tipo === 'entrada' ? 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-900/20' : 'bg-[#002F6C] hover:bg-[#001D42] dark:bg-blue-600 dark:hover:bg-blue-500 shadow-blue-900/20'">
                            <span>Confirmar e Registrar</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>


