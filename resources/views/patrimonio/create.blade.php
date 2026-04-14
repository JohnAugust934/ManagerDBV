<x-app-layout>
    <x-slot name="header">Novo Item de Patrimônio</x-slot>

    <div class="ui-page max-w-4xl mx-auto space-y-6 ui-animate-fade-up">

        {{-- Header Navigation --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('patrimonio.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-[#002F6C] dark:text-slate-400 dark:hover:text-blue-400 font-bold text-sm transition-colors group">
                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-[#002F6C]/10 dark:group-hover:bg-blue-500/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </div>
                Voltar ao Inventário
            </a>
        </div>

        <div class="ui-card overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Dados do Bem Material</h3>
                    <p class="text-sm font-medium text-slate-500 mt-0.5">Preencha as informações para catalogar este equipamento.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <form method="POST" action="{{ route('patrimonio.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="item" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Nome do Item / Equipamento <span class="text-red-500">*</span></label>
                        <input id="item" name="item" type="text" value="{{ old('item') }}" placeholder="Ex: Barraca Iglu 4 Pessoas, Caixa de Som JBL..." required autofocus class="ui-input w-full font-bold">
                        <x-input-error class="mt-2" :messages="$errors->get('item')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="quantidade" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Quantidade <span class="text-red-500">*</span></label>
                            <input id="quantidade" name="quantidade" type="number" min="1" step="1" value="{{ old('quantidade', 1) }}" required class="ui-input w-full font-bold">
                            <x-input-error class="mt-2" :messages="$errors->get('quantidade')" />
                        </div>

                        <div>
                            <label for="estado_conservacao" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Estado de Conservação <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="estado_conservacao" name="estado_conservacao" class="ui-input w-full font-bold appearance-none pr-8" required>
                                    <option value="" disabled selected>Selecione...</option>
                                    @foreach (['Novo', 'Ótimo', 'Bom', 'Regular', 'Ruim', 'Péssimo', 'Inservível'] as $estado)
                                        <option value="{{ $estado }}" {{ old('estado_conservacao') == $estado ? 'selected' : '' }}>{{ mb_strtoupper($estado, 'UTF-8') }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('estado_conservacao')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="valor_estimado" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Valor Estimado / Unitário</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-slate-400 font-black text-sm">R$</span>
                                </div>
                                <input id="valor_estimado" name="valor_estimado" type="number" step="0.01" min="0" value="{{ old('valor_estimado') }}" placeholder="0.00" class="ui-input w-full pl-10 font-bold">
                            </div>
                            <p class="mt-1.5 text-[10px] uppercase font-bold tracking-widest text-slate-400">Deixe em branco se desconhecido.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('valor_estimado')" />
                        </div>

                        <div>
                            <label for="data_aquisicao" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Data de Aquisição</label>
                            <input id="data_aquisicao" name="data_aquisicao" type="date" value="{{ old('data_aquisicao') }}" class="ui-input w-full font-bold">
                            <x-input-error class="mt-2" :messages="$errors->get('data_aquisicao')" />
                        </div>
                    </div>

                    <div>
                        <label for="local_armazenamento" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Local / Responsável do Armazenamento</label>
                        <input id="local_armazenamento" name="local_armazenamento" type="text" value="{{ old('local_armazenamento') }}" placeholder="Ex: Armário A da Igreja, Casa do Diretor..." class="ui-input w-full font-bold">
                        <x-input-error class="mt-2" :messages="$errors->get('local_armazenamento')" />
                    </div>

                    <div>
                        <label for="observacoes" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Observações / Numeração</label>
                        <textarea id="observacoes" name="observacoes" rows="3" class="ui-input w-full font-bold resize-none" placeholder="Descreva se há identificadores, marcas, ou avarias.">{{ old('observacoes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('observacoes')" />
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('patrimonio.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 font-black text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-center transition-all">Cancelar</a>
                        <button type="submit" class="w-full sm:w-auto ui-btn-primary flex justify-center items-center gap-2 h-[52px]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            Adicionar ao Inventário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
