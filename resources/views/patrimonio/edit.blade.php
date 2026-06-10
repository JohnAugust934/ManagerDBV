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
            <form id="del-patrimonio" action="{{ route('patrimonio.destroy', $patrimonio->id) }}" method="POST">
                @csrf @method('DELETE')
                <button type="button" onclick="confirmAction({ title: 'Excluir Item', message: 'Excluir este item permanentemente?', formId: 'del-patrimonio', confirmText: 'Excluir', variant: 'danger' })" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 transition-colors">
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

        {{-- Histórico de Manutenção --}}
        <div class="ui-card overflow-hidden" x-data="{ aberto: false }">
            <div class="px-6 sm:px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 dark:text-white">Histórico de Manutenção</h3>
                        <p class="text-xs text-slate-400 font-semibold">{{ $patrimonio->manutencoes->count() }} registro(s)</p>
                    </div>
                </div>
                <button type="button" @click="aberto = !aberto"
                    class="flex items-center gap-2 text-sm font-bold text-[#002F6C] dark:text-blue-400 hover:text-blue-700 transition-colors">
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-45': aberto }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Novo registro
                </button>
            </div>

            {{-- Formulário de nova manutenção --}}
            <div class="px-6 sm:px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-blue-50/50 dark:bg-blue-900/10" x-show="aberto" x-cloak>
                <form action="{{ route('patrimonio.manutencoes.store', $patrimonio) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Data <span class="text-red-500">*</span></label>
                            <input type="date" name="data" value="{{ now()->toDateString() }}" required class="ui-input w-full font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Novo Estado <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="estado_novo" required class="ui-input w-full appearance-none pr-8 font-bold">
                                    @foreach (['Novo', 'Ótimo', 'Bom', 'Regular', 'Ruim', 'Péssimo', 'Inservível'] as $estado)
                                        <option value="{{ $estado }}" {{ $patrimonio->estado_conservacao === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Descrição <span class="text-red-500">*</span></label>
                        <textarea name="descricao" rows="3" required placeholder="Descreva o que foi feito..." class="ui-input w-full resize-none"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="ui-btn-primary px-4 py-2 text-sm">Salvar</button>
                        <button type="button" @click="aberto = false" class="ui-btn-secondary px-4 py-2 text-sm">Cancelar</button>
                    </div>
                </form>
            </div>

            <div class="p-6 sm:p-8">
                @if($patrimonio->manutencoes->isEmpty())
                    <p class="text-sm text-slate-400 font-semibold text-center py-4">Nenhum registro de manutenção.</p>
                @else
                    <div class="space-y-3">
                        @foreach($patrimonio->manutencoes as $man)
                            <div class="group flex items-start gap-4 p-4 rounded-2xl border border-slate-100 dark:border-slate-700 hover:border-slate-200 dark:hover:border-slate-600 transition-colors">
                                <div class="shrink-0 w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($man->estado_anterior && $man->estado_anterior !== $man->estado_novo)
                                                <span class="text-xs font-bold text-slate-400">{{ $man->estado_anterior }}</span>
                                                <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            @endif
                                            <span class="text-xs font-black text-slate-700 dark:text-slate-200">{{ $man->estado_novo }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold shrink-0">
                                            <span>{{ $man->data->format('d/m/Y') }}</span>
                                            @if($man->user)
                                                <span>·</span>
                                                <span>{{ $man->user->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-slate-600 dark:text-slate-300 font-medium">{{ $man->descricao }}</p>
                                </div>
                                <form id="del-manutencao-{{ $man->id }}" action="{{ route('patrimonio.manutencoes.destroy', [$patrimonio, $man]) }}" method="POST"
                                    class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmAction({ title: 'Remover Registro', message: 'Remover este registro?', formId: 'del-manutencao-{{ $man->id }}', confirmText: 'Remover', variant: 'danger' })" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
