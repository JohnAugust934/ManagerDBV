<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('especialidades.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">
                Nova Especialidade
            </h2>
        </div>
    </x-slot>

    <div class="ui-page max-w-2xl mx-auto ui-animate-fade-up pb-20">
        
        <div class="ui-card overflow-hidden">
            {{-- Header customizado para Form --}}
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-[#002F6C]/5 to-transparent z-0"></div>
                <div class="relative z-10 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 text-[#002F6C] dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Criar Especialidade</h3>
                        <p class="text-[13px] font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-widest">Adicione ao acervo do seu clube.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <form method="POST" action="{{ route('especialidades.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="nome" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Nome da Especialidade <span class="text-red-500">*</span></label>
                        <div class="relative relative group">
                            <input id="nome" name="nome" type="text" class="ui-input w-full font-bold focus:border-[#002F6C]" value="{{ old('nome') }}" placeholder="Ex: Fogueiras e Cozinha ao Ar Livre" required autofocus>
                        </div>
                        @error('nome')
                            <p class="text-xs text-red-500 font-bold mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="area" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Área / Categoria <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <select id="area" name="area" class="ui-input appearance-none w-full font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 focus:border-[#002F6C]" required>
                                <option value="" disabled selected>Selecione a área principal...</option>
                                @foreach (['ADRA', 'Artes e Habilidades Manuais', 'Atividades Agropecuárias', 'Atividades Missionárias e Comunitárias', 'Atividades Profissionais', 'Atividades Recreativas', 'Ciência e Saúde', 'Estudo da Natureza', 'Habilidades Domésticas', 'Mestrados'] as $area)
                                    <option value="{{ $area }}" {{ old('area') == $area ? 'selected' : '' }}>
                                        {{ $area }}
                                    </option>
                                @endforeach
                                <option value="Outra" {{ old('area') == 'Outra' ? 'selected' : '' }}>Outra Categoria</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                        @error('area')
                            <p class="text-xs text-red-500 font-bold mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-8 pb-2 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('especialidades.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 transition-colors text-center">
                            Cancelar
                        </a>
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto px-8 py-3 text-[15px]">
                            Cadastrar Especialidade
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</x-app-layout>
