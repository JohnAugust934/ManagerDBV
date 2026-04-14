<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('unidades.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">
                Editar Unidade
            </h2>
        </div>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-2xl mx-auto ui-animate-fade-up">
            
            <div class="ui-card overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-black uppercase tracking-widest text-slate-800 dark:text-white mb-0.5">Editar Registro</h3>
                            <p class="text-xs text-slate-500 font-medium">
                                Atualize os dados da unidade "{{ $unidade->nome }}".
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    @if ($errors->any())
                        <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 rounded-2xl flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-red-800 dark:text-red-400 mb-2">Por favor, verifique os erros abaixo:</h3>
                                <ul class="text-sm font-medium text-red-700 dark:text-red-300 space-y-1 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('unidades.update', $unidade) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="nome" class="ui-input-label">Nome da Unidade *</label>
                            <input id="nome" name="nome" type="text" class="ui-input" value="{{ old('nome', $unidade->nome) }}" required />
                        </div>

                        <div>
                            <label for="conselheiro" class="ui-input-label">Nome do Conselheiro(a) *</label>
                            <input id="conselheiro" name="conselheiro" type="text" class="ui-input" value="{{ old('conselheiro', $unidade->conselheiro) }}" required />
                        </div>

                        <div>
                            <label for="grito_guerra" class="ui-input-label">Grito de Guerra</label>
                            <textarea id="grito_guerra" name="grito_guerra" rows="4" class="ui-input" placeholder="Digite o grito de guerra aqui...">{{ old('grito_guerra', $unidade->grito_guerra) }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-6 border-t border-slate-100 dark:border-slate-800 mt-8">
                            
                            <button type="button" 
                                onclick="if(confirm('Tem certeza? Isso pode afetar os desbravadores vinculados à unidade.')) document.getElementById('delete-form').submit()"
                                class="ui-btn-danger w-full sm:w-auto px-6 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Excluir Unidade
                            </button>

                            <div class="flex flex-col-reverse sm:flex-row w-full sm:w-auto gap-4">
                                <a href="{{ route('unidades.index') }}" class="ui-btn-secondary px-6 w-full sm:w-auto">
                                    Cancelar
                                </a>
                                <button type="submit" class="ui-btn-primary px-8 w-full sm:w-auto">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Salvar Alterações
                                </button>
                            </div>

                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('unidades.destroy', $unidade) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
