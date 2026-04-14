<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('unidades.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">
                Nova Unidade
            </h2>
        </div>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-2xl mx-auto ui-animate-fade-up">
            
            <div class="ui-card overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-black uppercase tracking-widest text-slate-800 dark:text-white mb-0.5">Criar Unidade</h3>
                            <p class="text-xs text-slate-500 font-medium">
                                Cadastre uma nova unidade para agrupar seus desbravadores.
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

                    <form method="POST" action="{{ route('unidades.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="nome" class="ui-input-label">Nome da Unidade *</label>
                            <input id="nome" name="nome" type="text" class="ui-input" value="{{ old('nome') }}" required autofocus placeholder="Ex: Águias, Órion..." />
                        </div>

                        <div>
                            <label for="conselheiro" class="ui-input-label">Nome do Conselheiro(a) *</label>
                            <input id="conselheiro" name="conselheiro" type="text" class="ui-input" value="{{ old('conselheiro') }}" required placeholder="Quem lidera esta unidade?" />
                        </div>

                        <div>
                            <label for="grito_guerra" class="ui-input-label">Grito de Guerra (Opcional)</label>
                            <textarea id="grito_guerra" name="grito_guerra" rows="4" class="ui-input" placeholder="Digite o grito de guerra aqui...">{{ old('grito_guerra') }}</textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800 mt-8">
                            <a href="{{ route('unidades.index') }}" class="ui-btn-secondary px-8 w-full sm:w-auto">
                                Cancelar
                            </a>
                            <button type="submit" class="ui-btn-primary px-8 w-full sm:w-auto">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Criar Unidade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
