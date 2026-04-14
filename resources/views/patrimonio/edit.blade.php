<x-app-layout>
    <x-slot name="header">Editar Item de Patrimônio</x-slot>

    <div class="ui-page max-w-4xl mx-auto space-y-6 ui-animate-fade-up">

        {{-- Header Navigation --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('patrimonio.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-[#002F6C] dark:text-slate-400 dark:hover:text-blue-400 font-bold text-sm transition-colors group">
                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-[#002F6C]/10 dark:group-hover:bg-blue-500/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </div>
                Voltar ao Inventário
            </a>
            <form action="{{ route('patrimonio.destroy', $patrimonio->id) }}" method="POST" onsubmit="return confirm('Excluir este item permanentemente?')">
                @csrf @method('DELETE')
                <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Excluir
                </button>
            </form>
        </div>

        <div class="ui-card overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Editando {{ $patrimonio->item }}</h3>
                    <p class="text-sm font-medium text-slate-500 mt-0.5">Atualize as informações, conservação ou localização deste item.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <form method="POST" action="{{ route('patrimonio.update', $patrimonio->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="item" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Nome do Item / Equipamento <span class="text-red-500">*</span></label>
                        <input id="item" name="item" type="text" value="{{ old('item', $patrimonio->item) }}" required class="ui-input w-full font-bold text-slate-800 dark:text-white">
                        <x-input-error class="mt-2" :messages="$errors->get('item')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="quantidade" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Quantidade <span class="text-red-500">*</span></label>
                            <input id="quantidade" name="quantidade" type="number" min="1" step="1" value="{{ old('quantidade', $patrimonio->quantidade) }}" required class="ui-input w-full font-bold">
                            <x-input-error class="mt-2" :messages="$errors->get('quantidade')" />
                        </div>

                        <div>
                            <label for="estado_conservacao" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Estado de Conservação <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="estado_conservacao" name="estado_conservacao" class="ui-input w-full font-bold appearance-none pr-8" required>
                                    @foreach (['Novo', 'Ótimo', 'Bom', 'Regular', 'Ruim', 'Péssimo', 'Inservível'] as $estado)
                                        <option value="{{ $estado }}" {{ old('estado_conservacao', $patrimonio->estado_conservacao) == $estado ? 'selected' : '' }}>{{ mb_strtoupper($estado, 'UTF-8') }}</option>
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
                                <input id="valor_estimado" name="valor_estimado" type="number" step="0.01" min="0" value="{{ old('valor_estimado', $patrimonio->valor_estimado) }}" class="ui-input w-full pl-10 font-bold">
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('valor_estimado')" />
                        </div>

                        <div>
                            <label for="data_aquisicao" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Data de Aquisição</label>
                            <input id="data_aquisicao" name="data_aquisicao" type="date" value="{{ old('data_aquisicao', $patrimonio->data_aquisicao) }}" class="ui-input w-full font-bold">
                            <x-input-error class="mt-2" :messages="$errors->get('data_aquisicao')" />
                        </div>
                    </div>

                    <div>
                        <label for="local_armazenamento" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Local / Responsável do Armazenamento</label>
                        <input id="local_armazenamento" name="local_armazenamento" type="text" value="{{ old('local_armazenamento', $patrimonio->local_armazenamento) }}" class="ui-input w-full font-bold">
                        <x-input-error class="mt-2" :messages="$errors->get('local_armazenamento')" />
                    </div>

                    <div>
                        <label for="observacoes" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Observações / Numeração</label>
                        <textarea id="observacoes" name="observacoes" rows="3" class="ui-input w-full font-bold resize-none">{{ old('observacoes', $patrimonio->observacoes) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('observacoes')" />
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('patrimonio.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 font-black text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-center transition-all">Cancelar</a>
                        <button type="submit" class="w-full sm:w-auto ui-btn-primary flex justify-center items-center gap-2 h-[52px] bg-amber-500 hover:bg-amber-600 shadow-amber-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            Atualizar Bem
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
