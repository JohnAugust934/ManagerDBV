<x-app-layout>
    <x-slot name="header">
        Termos Oficiais (Atas)
    </x-slot>

    <div class="ui-page space-y-6 max-w-7xl mx-auto ui-animate-fade-up">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#002F6C] dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                    Especialidades do Membro
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Condecorações de {{ $desbravador->nome }}</p>
            </div>
            
            <a href="{{ route('desbravadores.show', $desbravador->id) }}" class="ui-btn-secondary w-full md:w-auto flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar ao Perfil
            </a>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- ADICIONAR NOVA ESPECIALIDADE (COM BUSCA EM ALPINE) --}}
            <div class="ui-card p-6 lg:p-8" x-data="{ termoBusca: '' }">
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6">Investir Especialidades</h3>

                <form action="{{ route('desbravadores.salvar-especialidades', $desbravador->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="data_conclusao" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Data de Conclusão / Investidura</label>
                        <input id="data_conclusao" type="date" name="data_conclusao" value="{{ date('Y-m-d') }}" class="ui-input w-full font-bold" required />
                    </div>

                    <div>
                        <div class="flex justify-between items-end mb-2">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest">Catálogo de Especialidades</label>
                            
                            {{-- BUSCA DINÂMICA COM ALPINE --}}
                            <div class="relative w-1/2">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" x-model.debounce.300ms="termoBusca" placeholder="Pesquisar especialidade..." class="w-full pl-9 pr-3 py-1.5 text-xs font-bold bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white focus:border-[#002F6C] focus:ring-1 focus:ring-[#002F6C] outline-none transition-all">
                            </div>
                        </div>

                        {{-- CONTAINER DE LISTA FILTRADO --}}
                        <div class="h-80 overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-700 p-2 space-y-1 bg-slate-50/50 dark:bg-slate-900/30">
                            @foreach($especialidades as $esp)
                                @php $jaTem = $desbravador->especialidades->contains($esp->id); @endphp
                                <label x-show="termoBusca === '' || '{{ strtolower($esp->nome) }}'.includes(termoBusca.toLowerCase())" 
                                       class="flex items-center space-x-3 p-3 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700 cursor-pointer {{ $jaTem? 'opacity-40 grayscale pointer-events-none' : '' }}">
                                    
                                    <input type="checkbox" name="especialidades[]" value="{{ $esp->id }}" {{ $jaTem? 'disabled' : '' }} class="w-5 h-5 rounded-md border-slate-300 text-[#002F6C] focus:ring-[#002F6C] dark:border-slate-600 dark:bg-slate-700">
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $esp->cor_fundo }}"></span>
                                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">
                                                {{ $esp->nome }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($jaTem)
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[9px] font-black uppercase tracking-widest rounded-md shrink-0">
                                            Adquirida
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                            
                            {{-- Mensagem quando busca nao encontra nada --}}
                            <div x-show="termoBusca !== '' && ![...$el.previousElementSibling.parentElement.children].filter(c => c.tagName === 'LABEL' && c.style.display !== 'none').length" style="display:none" class="py-8 text-center">
                                <p class="text-xs font-bold text-slate-400">Nenhum resultado para a busca.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="ui-btn-primary w-full shadow-lg shadow-blue-900/20 active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            Adicionar Selecionadas
                        </button>
                    </div>
                </form>
            </div>

            {{-- JÁ POSSUI --}}
            <div class="ui-card p-6 lg:p-8">
                <div class="flex justify-between items-end mb-6">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white">Mural Cadastrado</h3>
                    <span class="px-3 py-1 bg-blue-50 dark:bg-blue-500/10 text-[#002F6C] dark:text-blue-400 font-black text-xl rounded-xl">
                        {{ $desbravador->especialidades->count() }}
                    </span>
                </div>

                @if($desbravador->especialidades->isEmpty())
                    <div class="flex flex-col items-center justify-center h-72 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl p-6">
                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4 text-slate-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-700 dark:text-slate-300">Nenhuma insígnia</h3>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mt-2">Escolha na esquerda para iniciar</p>
                    </div>
                @else
                    <div class="space-y-3 h-[420px] overflow-y-auto pr-2">
                        @foreach($desbravador->especialidades as $esp)
                            <div class="group flex justify-between items-center p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/50 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-sm" style="border-left: 4px solid {{ $esp->cor_fundo }}">
                                <div>
                                    <span class="font-black text-slate-800 dark:text-white block whitespace-nowrap overflow-hidden text-ellipsis max-w-[200px]" title="{{ $esp->nome }}">{{ $esp->nome }}</span>
                                    <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400 block mt-0.5">Criada em: {{ \Carbon\Carbon::parse($esp->pivot->data_conclusao)->format('d/m/Y') }}</span>
                                </div>

                                <form action="{{ route('desbravadores.remover-especialidade', ['desbravador' => $desbravador->id, 'especialidade' => $esp->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja desinvestir esta especialidade?');" class="shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all opacity-100 md:opacity-0 md:group-hover:opacity-100" title="Revogar Especialidade">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
