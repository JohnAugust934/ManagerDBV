<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('especialidades.show', $especialidade) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">Editar Especialidade</h2>
        </div>
    </x-slot>

    <div class="ui-page max-w-2xl mx-auto ui-animate-fade-up pb-20">
        <div class="ui-card overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-[#002F6C]/5 to-transparent z-0"></div>
                <div class="relative z-10">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Atualizar Especialidade</h3>
                    <p class="text-[13px] font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-widest">{{ $especialidade->codigo ?? 'Sem código' }}</p>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <form method="POST" action="{{ route('especialidades.update', $especialidade) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="nome" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Nome da Especialidade <span class="text-red-500">*</span></label>
                        <input id="nome" name="nome" type="text" class="ui-input w-full font-bold focus:border-[#002F6C]" value="{{ old('nome', $especialidade->nome) }}" required autofocus>
                        @error('nome')
                            <p class="text-xs text-red-500 font-bold mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="area" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Área / Categoria <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <select id="area" name="area" class="ui-input appearance-none w-full font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 focus:border-[#002F6C]" required>
                                @foreach (['ADRA', 'Artes e Habilidades Manuais', 'Atividades Agrícolas', 'Atividades Missionárias e Comunitárias', 'Atividades Profissionais', 'Atividades Recreativas', 'Ciência e Saúde', 'Estudos da Natureza', 'Habilidades Domésticas', 'Mestrados', 'Outra'] as $area)
                                    <option value="{{ $area }}" {{ old('area', $especialidade->area) === $area ? 'selected' : '' }}>{{ $area }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('area')
                            <p class="text-xs text-red-500 font-bold mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-8 pb-2 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('especialidades.show', $especialidade) }}" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 transition-colors text-center">Cancelar</a>
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto px-8 py-3 text-[15px]">Salvar Alterações</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('especialidades.destroy', $especialidade) }}" class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800" onsubmit="return confirm('Tem certeza que deseja excluir esta especialidade?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200">Excluir Especialidade</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
