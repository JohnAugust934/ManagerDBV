<x-app-layout>
    <div class="ui-page" x-data="{
        tipo: '{{ old('tipo', $caixa->tipo) }}',
        opcoesCategorias: {
            entrada: ['Mensalidade','Ofertas e Doações','Inscrições de Eventos','Venda de Uniformes','Cantina','Campanha','Outros'],
            saida: ['Materiais de Secretaria','Alimentação/Lanche','Transporte/Combustível','Compra de Uniformes','Equipamentos','Taxas e Repasses','Devolução','Outros']
        },
        get categoriasAtuais() { return this.opcoesCategorias[this.tipo]; }
    }">
        <div class="max-w-3xl mx-auto">

            <x-page-title title="Editar Lançamento" :back="route('caixa.index')" />

            <div class="bg-white dark:bg-slate-800 shadow-lg rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-amber-50/60 dark:bg-amber-500/10">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Editar Lançamento</h3>
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium">A alteração será registrada no histórico de auditoria.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('caixa.update', $caixa) }}" class="space-y-8">
                        @csrf
                        @method('PUT')

                        {{-- Tipo --}}
                        <div>
                            <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                                Tipo de Movimentação <span class="text-red-500">*</span>
                            </span>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="tipo" value="entrada" x-model="tipo" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-green-200 dark:hover:border-green-800 bg-white dark:bg-slate-800 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 peer-checked:shadow-md flex flex-col items-center justify-center text-center gap-2 h-28">
                                        <div class="w-9 h-9 rounded-full bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-200 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        </div>
                                        <span class="font-bold text-slate-600 dark:text-slate-300 text-sm">Entrada</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="tipo" value="saida" x-model="tipo" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-red-200 dark:hover:border-red-800 bg-white dark:bg-slate-800 transition-all peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 peer-checked:shadow-md flex flex-col items-center justify-center text-center gap-2 h-28">
                                        <div class="w-9 h-9 rounded-full bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-200 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        </div>
                                        <span class="font-bold text-slate-600 dark:text-slate-300 text-sm">Saída</span>
                                    </div>
                                </label>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
                        </div>

                        {{-- Valor --}}
                        <div>
                            <x-input-label for="valor" value="Valor (R$) *" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-slate-500 sm:text-lg">R$</span>
                                </div>
                                <input id="valor" name="valor" type="number" step="0.01" min="0.01"
                                    value="{{ old('valor', $caixa->valor) }}"
                                    class="ui-input pl-10 text-2xl font-bold" required />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('valor')" />
                        </div>

                        {{-- Categoria e Data --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="categoria" value="Categoria *" />
                                <div class="relative mt-1">
                                    <select id="categoria" name="categoria" class="ui-input" required>
                                        <option value="" disabled>Selecione uma opção</option>
                                        <template x-for="opcao in categoriasAtuais" :key="opcao">
                                            <option :value="opcao" x-text="opcao"
                                                :selected="opcao === '{{ old('categoria', $caixa->categoria) }}'"></option>
                                        </template>
                                        {{-- Fallback: mantém a categoria salva mesmo que não esteja na lista atual --}}
                                        @if($caixa->categoria)
                                        <option value="{{ $caixa->categoria }}" x-show="!categoriasAtuais.includes('{{ $caixa->categoria }}')" selected>{{ $caixa->categoria }}</option>
                                        @endif
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-700 dark:text-slate-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('categoria')" />
                            </div>
                            <div>
                                <x-input-label for="data_movimentacao" value="Data da Movimentação *" />
                                <x-text-input id="data_movimentacao" name="data_movimentacao" type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('data_movimentacao', $caixa->data_movimentacao?->format('Y-m-d')) }}"
                                    required />
                                <x-input-error class="mt-2" :messages="$errors->get('data_movimentacao')" />
                            </div>
                        </div>

                        {{-- Descrição --}}
                        <div>
                            <x-input-label for="descricao" value="Descrição Detalhada *" />
                            <x-text-input id="descricao" name="descricao" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('descricao', $caixa->descricao) }}"
                                required />
                            <x-input-error class="mt-2" :messages="$errors->get('descricao')" />
                        </div>

                        <div class="flex items-center justify-end pt-6 border-t border-slate-100 dark:border-slate-700 gap-4">
                            <a href="{{ route('caixa.index') }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium">
                                Cancelar
                            </a>
                            <button type="submit" class="ui-btn-primary">
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
