<x-app-layout>
    <x-slot name="header">Histórico de Frequência</x-slot>

    <div class="ui-page space-y-6 max-w-7xl mx-auto ui-animate-fade-up">

        {{-- HERO BANNER FILTRO — compacto no mobile --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#001D42] to-[#002F6C] px-5 py-4 shadow-xl shadow-blue-900/30">
            <div class="absolute -right-8 -top-8 w-36 h-36 bg-blue-400/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center gap-3">
                {{-- Título (oculto em telas muito pequenas) --}}
                <div class="shrink-0 hidden xs:block sm:block">
                    <p class="text-blue-300/70 text-[9px] font-black uppercase tracking-widest leading-none mb-0.5">Frequência</p>
                    <h1 class="text-base font-black text-white tracking-tight leading-none whitespace-nowrap">Histórico</h1>
                </div>

                {{-- Divider vertical (desktop) --}}
                <div class="hidden sm:block w-px h-8 bg-white/15 shrink-0"></div>

                {{-- Filtro inline --}}
                <form action="{{ route('frequencia.index') }}" method="GET" class="flex-1 flex items-center gap-2">
                    <div class="relative flex-1 min-w-0">
                        <select name="mes" id="mes"
                            class="w-full h-10 pl-3 pr-8 bg-white/10 border border-white/20 rounded-xl text-white font-black text-xs appearance-none focus:outline-none focus:ring-1 focus:ring-white/30 transition-all cursor-pointer truncate">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }} class="bg-slate-800 text-white">
                                    {{ mb_strtoupper(\Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-white/40">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="relative w-20 sm:w-24 shrink-0">
                        <select name="ano" id="ano"
                            class="w-full h-10 pl-3 pr-7 bg-white/10 border border-white/20 rounded-xl text-white font-black text-xs appearance-none focus:outline-none focus:ring-1 focus:ring-white/30 transition-all cursor-pointer">
                            @foreach (range(date('Y'), date('Y') - 5) as $y)
                                <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }} class="bg-slate-800 text-white">{{ $y }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-white/40">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    {{-- Botão Filtrar (icon no mobile, texto no desktop) --}}
                    <button type="submit"
                        class="shrink-0 h-10 w-10 sm:w-auto sm:px-4 bg-white hover:bg-blue-50 text-[#002F6C] font-black text-xs uppercase tracking-widest rounded-xl shadow-md shadow-black/20 transition-all active:scale-95 flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span class="hidden sm:inline">Filtrar</span>
                    </button>
                </form>

                {{-- Botão Nova Chamada (apenas ícone no mobile) --}}
                <a href="{{ route('frequencia.create') }}"
                    class="shrink-0 h-10 w-10 sm:w-auto sm:px-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-black text-xs uppercase tracking-widest rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    <span class="hidden sm:inline whitespace-nowrap">Nova Chamada</span>
                </a>
            </div>
        </div>


        @if ($datasReunioes->isEmpty())
            {{-- EMPTY STATE --}}
            <div class="ui-card p-12 flex flex-col items-center justify-center text-center border-dashed border-2 border-slate-200 dark:border-slate-800 bg-transparent shadow-none">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">Nenhuma chamada registrada</h3>
                <p class="text-sm font-bold text-slate-400 mb-6 max-w-md">
                    Não há registros de frequência para {{ \Carbon\Carbon::create()->month($mes)->locale('pt_BR')->monthName }} de {{ $ano }}.
                </p>
                <a href="{{ route('frequencia.create') }}" class="ui-btn-primary">Realizar Chamada Agora</a>
            </div>
        @else
            {{-- CABEÇALHO DO PERÍODO --}}
            <div>
                <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">
                    {{ mb_strtoupper(\Carbon\Carbon::create()->month($mes)->locale('pt_BR')->monthName) }} / {{ $ano }}
                </h2>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ $datasReunioes->count() }} reunião(ões) registrada(s)</p>
            </div>
            </div>

            {{-- VISUALIZAÇÃO MOBILE: Cards por Desbravador --}}
            <div class="block lg:hidden space-y-3">
                @foreach ($desbravadores as $dbv)
                    @php
                        $presencasTotal = 0;
                        $registros = [];
                        foreach ($datasReunioes as $data) {
                            $reg = $dbv->frequencias->first(fn($f) => $f->data->format('Y-m-d') === $data);
                            if ($reg && $reg->presente) $presencasTotal++;
                            $registros[$data] = $reg;
                        }
                        $pct = $datasReunioes->count() > 0 ? round(($presencasTotal / $datasReunioes->count()) * 100) : 0;
                    @endphp
                    <div class="ui-card p-0 overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm">
                        <div class="flex items-center gap-3 p-4">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-base shrink-0 border-2
                                {{ $pct >= 75 ? 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30' : ($pct >= 50 ? 'bg-amber-50 text-amber-600 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30' : 'bg-red-50 text-red-500 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30') }}">
                                {{ mb_strtoupper(substr($dbv->nome, 0, 1)) }}
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-sm text-slate-800 dark:text-white uppercase tracking-tight truncate">{{ $dbv->nome }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $dbv->unidade->nome ?? '-' }}</p>
                            </div>

                            {{-- % Frequência Big --}}
                            <div class="shrink-0 text-right">
                                <p class="text-2xl font-black {{ $pct >= 75 ? 'text-emerald-600 dark:text-emerald-400' : ($pct >= 50 ? 'text-amber-500 dark:text-amber-400' : 'text-red-500 dark:text-red-400') }}">
                                    {{ $pct }}<span class="text-sm">%</span>
                                </p>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">frequência</p>
                            </div>
                        </div>

                        {{-- Fita de Presenças --}}
                        <div class="px-4 pb-4 flex items-center gap-1 flex-wrap">
                            @foreach ($datasReunioes as $data)
                                @php $reg = $registros[$data] ?? null; @endphp
                                <div class="relative group">
                                    @if ($reg)
                                        @if ($reg->presente)
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center" title="{{ \Carbon\Carbon::parse($data)->format('d/m') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-500/20 text-red-500 dark:text-red-400 flex items-center justify-center" title="{{ \Carbon\Carbon::parse($data)->format('d/m') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-300 dark:text-slate-600 flex items-center justify-center text-xs font-black" title="{{ \Carbon\Carbon::parse($data)->format('d/m') }}">
                                            —
                                        </div>
                                    @endif
                                    {{-- Tooltip da data --}}
                                    <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[9px] font-black px-1.5 py-0.5 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                        {{ \Carbon\Carbon::parse($data)->format('d/m') }}
                                    </div>
                                </div>
                            @endforeach

                            {{-- Barra de progresso --}}
                            <div class="w-full mt-2 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700
                                    {{ $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                                    style="width: {{ $pct }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- VISUALIZAÇÃO DESKTOP: Tabela Completa --}}
            <div class="hidden lg:block ui-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left" style="min-width: max-content;">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                <th class="px-5 py-4 text-[11px] font-black uppercase tracking-wider text-slate-500 sticky left-0 bg-slate-50 dark:bg-slate-900/90 border-r border-dashed border-slate-200 dark:border-slate-700 z-10 min-w-[200px]">Desbravador</th>
                                <th class="px-4 py-4 text-[11px] font-black uppercase tracking-wider text-slate-500 whitespace-nowrap">Unidade</th>
                                @foreach ($datasReunioes as $data)
                                    <th class="px-3 py-4 text-center border-l border-dashed border-slate-200 dark:border-slate-700 min-w-[64px]">
                                        <div class="flex flex-col items-center">
                                            <span class="text-lg font-black text-slate-700 dark:text-slate-200 leading-none">{{ \Carbon\Carbon::parse($data)->format('d') }}</span>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($data)->locale('pt_BR')->shortDayName }}</span>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="px-4 py-4 text-center border-l border-slate-200 dark:border-slate-700 bg-[#002F6C]/5 dark:bg-blue-500/10 text-[11px] font-black uppercase tracking-wider text-[#002F6C] dark:text-blue-400 whitespace-nowrap min-w-[80px]">% Freq.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($desbravadores as $dbv)
                                @php
                                    $presencasTotal = 0;
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-5 py-3.5 font-black text-slate-800 dark:text-white sticky left-0 bg-white dark:bg-slate-900 border-r border-dashed border-slate-200 dark:border-slate-700 z-10 uppercase text-[13px] tracking-tight whitespace-nowrap">{{ $dbv->nome }}</td>
                                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 text-[13px] font-semibold whitespace-nowrap">{{ $dbv->unidade->nome ?? '-' }}</td>

                                    @foreach ($datasReunioes as $data)
                                        @php
                                            $registro = $dbv->frequencias->first(fn($f) => $f->data->format('Y-m-d') === $data);
                                            if ($registro && $registro->presente) $presencasTotal++;
                                        @endphp
                                        <td class="px-3 py-3.5 text-center border-l border-dashed border-slate-100 dark:border-slate-800">
                                            @if ($registro)
                                                @if ($registro->presente)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-full">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-7 h-7 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 rounded-full">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-200 dark:text-slate-700 font-black text-lg">—</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-4 py-3.5 text-center border-l border-slate-200 dark:border-slate-700 bg-[#002F6C]/5 dark:bg-blue-500/5">
                                        @php $pct = $datasReunioes->count() > 0 ? round(($presencasTotal / $datasReunioes->count()) * 100) : 0; @endphp
                                        <span class="inline-block px-2 py-1 rounded-lg text-[11px] font-black
                                            {{ $pct >= 75 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : ($pct >= 50 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400') }}">
                                            {{ $pct }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30">
                    <p class="text-[11px] text-slate-400 font-bold">* A porcentagem é calculada com base nas reuniões ocorridas no período selecionado. Verde ≥ 75% · Amarelo ≥ 50% · Vermelho &lt; 50%</p>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
