<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-dbv-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            {{ __('Unidades do Clube') }}
        </h2>
    </x-slot>

    <div class="ui-page max-w-7xl space-y-8 ui-animate-fade-up">

        {{-- Cabeçalho / Título --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-4 sm:px-0">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white mb-2">Unidades</h1>
                <p class="text-slate-500 font-medium">Gerencie e visualize as estatísticas das unidades ativas.</p>
            </div>
            <a href="{{ route('unidades.create') }}" class="ui-btn-primary w-full sm:w-auto shrink-0 shadow-xl shadow-blue-900/20 group">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Nova Unidade
            </a>
        </div>

        <div class="px-4 md:px-0">
            @if ($unidades->isEmpty())
                <div class="ui-empty mt-0 bg-slate-50/50 dark:bg-slate-900/20">
                    <div class="ui-empty-icon"><svg class="w-8 h-8 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div>
                    <h3 class="ui-empty-title">Nenhuma Unidade Cadastrada</h3>
                    <p class="ui-empty-description">As unidades são a base do clube. Comece criando as unidades para agrupar os desbravadores.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($unidades as $unidade)
                        <div class="ui-card p-6 flex flex-col h-full bg-gradient-to-b from-white to-slate-50/50 dark:from-slate-800 dark:to-slate-900/50 group relative overflow-hidden">
                            
                            {{-- Faixa superior decorativa --}}
                            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#002F6C] to-blue-500"></div>

                            {{-- Ícone bg --}}
                            <div class="absolute -right-6 -top-6 opacity-[0.03] dark:opacity-5 text-slate-900 dark:text-white pointer-events-none transform group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500">
                                <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            </div>

                            <div class="flex items-start justify-between mb-6 relative z-10">
                                <div>
                                    <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight uppercase group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transition-colors">
                                        {{ $unidade->nome }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="text-[12px] font-bold text-slate-500 uppercase tracking-wider">{{ $unidade->conselheiro ?? 'Sem conselheiro' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-5 bg-[#002F6C]/5 dark:bg-blue-500/10 p-3 rounded-xl border border-[#002F6C]/10 dark:border-blue-500/20 inline-flex items-center justify-center w-max relative z-10">
                                <span class="text-xl font-black text-[#002F6C] dark:text-blue-400 leading-none mr-2">{{ $unidade->desbravadores->count() ?? 0 }}</span>
                                <span class="text-[10px] uppercase font-black text-[#002F6C]/70 dark:text-blue-400/70 tracking-widest leading-none">Membros</span>
                            </div>

                            @if ($unidade->grito_guerra)
                                <div class="mt-auto mb-6 pl-4 border-l-4 border-[#FCD116] dark:border-yellow-500 py-1 relative z-10">
                                    <p class="text-[12px] font-bold italic text-slate-500 dark:text-slate-400 line-clamp-3">
                                        "{{ $unidade->grito_guerra }}"
                                    </p>
                                </div>
                            @else
                                <div class="mt-auto"></div>
                            @endif

                            <div class="grid grid-cols-2 gap-3 pt-6 border-t border-slate-100 dark:border-slate-800 relative z-10">
                                <a href="{{ route('unidades.show', $unidade) }}" class="ui-btn-secondary px-0 text-[13px] border-2 group-hover:border-[#002F6C]/30 text-center flex items-center justify-center">
                                    Acessar
                                </a>
                                <a href="{{ route('unidades.edit', $unidade) }}" class="ui-btn-secondary px-0 text-[13px] border-2 group-hover:border-amber-500/30 text-center flex items-center justify-center text-amber-600 dark:text-amber-500 hover:bg-amber-50">
                                    Editar
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
