<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ $scope === 'unidades' ? route('ranking.unidades') : route('ranking.desbravadores') }}"
                class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-slate-400">Histórico de Ranking</p>
                <h2 class="font-black text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ $scope === 'unidades' ? 'Unidades' : 'Desbravadores' }} — {{ $year }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="ui-page max-w-4xl mx-auto ui-animate-fade-up pb-20 space-y-6">

        {{-- Seletor de ano --}}
        @if($anosDisponiveis->isNotEmpty())
        <form action="{{ route('ranking.ver-snapshot', $scope) }}" method="GET" class="flex items-center gap-3">
            <label class="text-sm font-bold text-slate-500">Consultar outro ano:</label>
            <select name="year" class="h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 focus:outline-none">
                @foreach($anosDisponiveis as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="h-9 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                Ver
            </button>
        </form>
        @endif

        @if(!$snapshot)
            <div class="ui-card p-12 text-center">
                <p class="text-slate-500 font-bold text-lg">Nenhum snapshot salvo para {{ $year }}.</p>
                <p class="text-slate-400 text-sm mt-2">Acesse o ranking em tempo real e clique em "Salvar snapshot" para registrar o estado atual.</p>
            </div>
        @else
            <div class="ui-card overflow-hidden">
                <div class="h-1.5 w-full bg-gradient-to-r from-[#002F6C] to-blue-500"></div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">
                            🏆 Ranking {{ $scope === 'unidades' ? 'das Unidades' : 'Individual' }} — {{ $year }}
                        </h3>
                        <p class="text-xs font-semibold text-slate-400">
                            Salvo em {{ $snapshot->generated_at->format('d/m/Y \à\s H:i') }}
                        </p>
                    </div>

                    <div class="space-y-3">
                        @foreach($snapshot->entries as $i => $entry)
                            <div class="flex items-center gap-4 p-4 rounded-2xl {{ $i === 0 ? 'bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/30' : ($i === 1 ? 'bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700' : ($i === 2 ? 'bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800/30' : 'bg-slate-50 dark:bg-slate-900/30 border border-slate-100 dark:border-slate-800')) }}">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-lg shrink-0
                                    {{ $i === 0 ? 'bg-amber-400 text-white' : ($i === 1 ? 'bg-slate-400 text-white' : ($i === 2 ? 'bg-orange-400 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-300 text-sm')) }}">
                                    {{ $i < 3 ? ['🥇','🥈','🥉'][$i] : ($i + 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-black text-slate-800 dark:text-white truncate">{{ $entry['nome'] }}</p>
                                    @if(isset($entry['unidade']))
                                        <p class="text-xs text-slate-400 font-semibold">{{ $entry['unidade'] }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xl font-black text-[#002F6C] dark:text-blue-400">{{ number_format($entry['pontos']) }}</p>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">pts</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
