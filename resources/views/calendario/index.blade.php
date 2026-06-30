<x-app-layout>

    <div class="ui-page space-y-6 max-w-5xl mx-auto">
        <x-page-title title="Calendário do Clube" subtitle="Reuniões, eventos e aniversários do mês." />

        {{-- Navegação mês/ano --}}
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('calendario.index', ['mes' => $mes == 1 ? 12 : $mes - 1, 'ano' => $mes == 1 ? $ano - 1 : $ano]) }}"
               class="ui-btn-secondary shrink-0" aria-label="Mês anterior">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline">Mês anterior</span>
            </a>
            <h2 class="flex-1 text-base sm:text-xl font-black text-slate-800 dark:text-white text-center capitalize">
                {{ $inicio->locale('pt_BR')->translatedFormat('F Y') }}
            </h2>
            <a href="{{ route('calendario.index', ['mes' => $mes == 12 ? 1 : $mes + 1, 'ano' => $mes == 12 ? $ano + 1 : $ano]) }}"
               class="ui-btn-secondary shrink-0" aria-label="Próximo mês">
                <span class="hidden sm:inline">Próximo mês</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Legenda --}}
        <div class="flex flex-wrap gap-4 sm:gap-6 text-xs font-bold text-slate-500">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Reunião</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Evento</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Aniversário</span>
        </div>

        {{-- Grade do calendário --}}
        <div class="ui-card p-2 sm:p-4 overflow-hidden">
            <div class="grid grid-cols-7 mb-1 sm:mb-2">
                @foreach (['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $diaSemana)
                    <div class="text-center text-[10px] sm:text-[11px] font-black text-slate-400 uppercase tracking-widest py-1.5 sm:py-2">
                        <span class="sm:hidden">{{ mb_substr($diaSemana, 0, 1) }}</span>
                        <span class="hidden sm:inline">{{ $diaSemana }}</span>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-0.5 sm:gap-1">
                @for ($i = 0; $i < $inicio->dayOfWeek; $i++)
                    <div class="min-h-[44px] sm:min-h-[80px]"></div>
                @endfor

                @for ($dia = 1; $dia <= $inicio->daysInMonth; $dia++)
                    @php
                        $dataStr = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                        $temReuniao = $reunioes->contains($dataStr);
                        $eventosNoDia = $eventos->filter(fn ($e) =>
                            \Carbon\Carbon::parse($e->data_inicio)->day <= $dia &&
                            \Carbon\Carbon::parse($e->data_fim ?? $e->data_inicio)->day >= $dia
                        );
                        $aniversariantesNoDia = $aniversariantes->where('dia', $dia);
                        $hoje = now()->day === $dia && now()->month === $mes && now()->year === $ano;
                    @endphp
                    <div class="min-h-[44px] sm:min-h-[80px] p-1 sm:p-1.5 rounded-lg sm:rounded-xl border border-transparent flex flex-col
                        {{ $hoje ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' }} transition-colors">
                        <span class="text-[11px] sm:text-xs font-black {{ $hoje ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $dia }}</span>

                        {{-- Mobile: apenas marcadores (pontos) --}}
                        <div class="sm:hidden mt-auto flex flex-wrap items-center gap-0.5 pt-1">
                            @if ($temReuniao)<span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>@endif
                            @if ($eventosNoDia->isNotEmpty())<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>@endif
                            @if ($aniversariantesNoDia->isNotEmpty())<span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>@endif
                        </div>

                        {{-- Desktop: marcadores com rótulo --}}
                        <div class="hidden sm:block mt-1 space-y-0.5">
                            @if ($temReuniao)
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 truncate">Reunião</span>
                                </div>
                            @endif

                            @foreach ($eventosNoDia as $evento)
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 truncate">{{ $evento->nome }}</span>
                                </div>
                            @endforeach

                            @foreach ($aniversariantesNoDia as $aniv)
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                                    <span class="text-[10px] font-medium text-amber-600 dark:text-amber-400 truncate">{{ explode(' ', $aniv['nome'])[0] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Lista de aniversariantes do mês --}}
        @if ($aniversariantes->isNotEmpty())
            <div class="ui-card p-6">
                <h3 class="font-black text-slate-800 dark:text-white mb-4 flex items-center gap-2 capitalize">
                    🎂 Aniversariantes de {{ $inicio->locale('pt_BR')->translatedFormat('F') }}
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach ($aniversariantes as $aniv)
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30">
                            <div class="w-8 h-8 rounded-full bg-amber-200 dark:bg-amber-800 flex items-center justify-center font-black text-amber-800 dark:text-amber-200 text-sm shrink-0">
                                {{ $aniv['dia'] }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-800 dark:text-white leading-none truncate">{{ explode(' ', $aniv['nome'])[0] }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $aniv['idade'] }} anos</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
