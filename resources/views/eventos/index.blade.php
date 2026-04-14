<x-app-layout>
    <x-slot name="header">Calendário de Eventos</x-slot>

    <div class="ui-page space-y-8 max-w-[1400px] ui-animate-fade-up">

        {{-- Cabeçalho com Ação --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 px-4 sm:px-0">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight mb-2">Eventos do Clube</h1>
                <p class="text-slate-500 font-medium">Gerencie o calendário, inscrições e pagamentos de eventos.</p>
            </div>
            @can('secretaria')
                <a href="{{ route('eventos.create') }}" class="ui-btn-primary shrink-0 flex items-center gap-2 px-6 h-12 rounded-2xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    Novo Evento
                </a>
            @endcan
        </div>

        {{-- Grid de Eventos --}}
        @if ($eventos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 px-4 sm:px-0">
                @foreach ($eventos as $evento)
                    @php
                        $dataInicio = \Carbon\Carbon::parse($evento->data_inicio);
                        $isPassado = \Carbon\Carbon::parse($evento->data_fim)->isPast();
                    @endphp

                    <div class="group relative bg-white dark:bg-slate-800/80 rounded-3xl border border-slate-100 dark:border-slate-700/60 overflow-hidden flex flex-col shadow-sm hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-black/50 transition-all duration-300 hover:-translate-y-1">

                        {{-- Top Banner Gradient --}}
                        <div class="h-28 relative overflow-hidden flex items-end {{ $isPassado ? 'bg-gradient-to-br from-slate-500 to-slate-600' : 'bg-gradient-to-br from-[#002F6C] to-blue-600' }}">
                            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white to-transparent"></div>
                            <div class="absolute top-4 right-4">
                                @if ($isPassado)
                                    <span class="px-2.5 py-1 bg-black/20 text-white/80 text-[9px] font-black rounded-full uppercase tracking-widest border border-white/10">Encerrado</span>
                                @else
                                    <span class="px-2.5 py-1 bg-emerald-500/30 text-white text-[9px] font-black rounded-full uppercase tracking-widest border border-emerald-400/30 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                                        Ativo
                                    </span>
                                @endif
                            </div>

                            {{-- Badge de Data --}}
                            <div class="relative m-4 bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl px-4 py-2 flex items-center gap-3">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-[10px] font-black text-white/70 uppercase leading-none">{{ $dataInicio->translatedFormat('M') }}</span>
                                    <span class="text-2xl font-black text-white leading-none">{{ $dataInicio->format('d') }}</span>
                                </div>
                                <div class="w-px h-8 bg-white/20"></div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-white/70 uppercase leading-none">{{ $dataInicio->format('Y') }}</span>
                                    <span class="text-sm font-black text-white leading-tight mt-0.5">{{ $dataInicio->translatedFormat('l') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="text-xl font-black text-slate-800 dark:text-white leading-tight mb-3 line-clamp-2 group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transition-colors uppercase tracking-tight">
                                {{ $evento->nome }}
                            </h3>

                            <div class="space-y-2 mb-5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400 truncate">{{ $evento->local }}</span>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-lg {{ $evento->valor == 0 ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-amber-50 dark:bg-amber-500/10' }} flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 {{ $evento->valor == 0 ? 'text-emerald-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-sm font-black {{ $evento->valor == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $evento->valor == 0 ? 'Gratuito' : 'R$ ' . number_format($evento->valor, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                                        <span class="font-black text-slate-800 dark:text-white">{{ $evento->desbravadores_count }}</span> inscritos
                                    </span>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-700/50">
                                <a href="{{ route('eventos.show', $evento->id) }}" class="flex items-center justify-between w-full px-4 py-3 bg-slate-50 dark:bg-slate-900/50 hover:bg-[#002F6C]/10 dark:hover:bg-blue-500/10 text-slate-600 dark:text-slate-300 hover:text-[#002F6C] dark:hover:text-blue-400 rounded-2xl transition-all duration-200 font-black text-sm uppercase tracking-wider group/btn">
                                    <span>Gerenciar Evento</span>
                                    <svg class="w-5 h-5 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 px-4 sm:px-0">
                {{ $eventos->links() }}
            </div>
        @else
            <div class="px-4 sm:px-0">
                <div class="ui-empty">
                    <div class="ui-empty-icon"><svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    <h3 class="ui-empty-title">Nenhum Evento no Calendário</h3>
                    <p class="ui-empty-description">Comece a planejar o ano do clube criando o primeiro evento do calendário.</p>
                    @can('secretaria')
                    <div class="mt-6">
                        <a href="{{ route('eventos.create') }}" class="ui-btn-primary">Criar Primeiro Evento</a>
                    </div>
                    @endcan
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
