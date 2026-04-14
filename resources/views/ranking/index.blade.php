<x-app-layout>
    <x-slot name="header">Mural Estelar / Ranking</x-slot>

    <div class="ui-page max-w-6xl mx-auto space-y-12 ui-animate-fade-up">

        {{-- Cabeçalho da Gamificação --}}
        <div class="flex items-center justify-between px-4 sm:px-0">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="text-3xl">🏆</span> {{ $titulo }}
                </h1>
                <p class="text-slate-500 font-medium mt-1">Acompanhe as pontuações e destaque das unidades e desbravadores.</p>
            </div>
        </div>

        @if ($top3->count() > 0)
            {{-- Painel do Pódio --}}
            <div class="relative w-full max-w-4xl mx-auto mt-8 mb-16 px-4">
                {{-- Glow background --}}
                <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-[#002F6C]/5 to-transparent dark:from-blue-500/10 pointer-events-none rounded-t-full blur-3xl"></div>
                
                <div class="flex justify-center items-end gap-3 sm:gap-6 md:gap-10 min-h-[360px] relative z-10">

                    {{-- 2º Lugar --}}
                    @if (isset($top3[1]))
                        <div class="flex flex-col items-center group w-1/3 max-w-[180px] ui-animate-fade-up" style="animation-duration: 0.8s;">
                            <div class="mb-4 text-center">
                                <span class="block text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-widest mb-2">2º Lugar</span>
                                <span class="block text-sm md:text-lg font-black text-slate-800 dark:text-white truncate w-24 md:w-full px-1 mb-1 leading-tight">{{ $top3[1]->nome }}</span>
                                <span class="block text-[10px] font-bold text-slate-500 mb-2">{{ $top3[1]->subtexto }}</span>
                                <span class="inline-flex px-3 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-black shadow-sm">{{ $top3[1]->pontos }} pts</span>
                            </div>
                            <div class="w-full relative h-[140px] md:h-[180px] bg-gradient-to-t from-slate-200 to-slate-100 dark:from-slate-800 dark:to-slate-700/50 rounded-t-2xl border-t-4 border-slate-300 dark:border-slate-600 shadow-inner group-hover:h-[150px] md:group-hover:h-[190px] transition-all duration-500 flex justify-center">
                                <div class="absolute -top-6 w-12 h-12 bg-white dark:bg-slate-800 border-4 border-slate-200 dark:border-slate-700 rounded-full flex items-center justify-center font-black text-xl text-slate-400 shadow-md">2</div>
                            </div>
                        </div>
                    @endif

                    {{-- 1º Lugar --}}
                    <div class="flex flex-col items-center group w-1/3 max-w-[220px] z-30 ui-animate-fade-up" style="animation-duration: 0.5s;">
                        <div class="mb-5 text-center">
                            <div class="flex justify-center mb-2">
                                <svg class="w-8 h-8 text-amber-500 drop-shadow-md animate-bounce" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            </div>
                            <span class="block text-lg md:text-2xl font-black text-[#002F6C] dark:text-blue-400 truncate w-28 md:w-full px-1 mb-1 leading-none">{{ $top3[0]->nome }}</span>
                            <span class="block text-[10px] md:text-xs font-bold text-[#002F6C]/60 dark:text-blue-300/80 mb-3">{{ $top3[0]->subtexto }}</span>
                            <span class="inline-flex px-4 py-1.5 bg-gradient-to-r from-amber-400 to-amber-500 text-white rounded-xl text-sm font-black shadow-lg shadow-amber-500/30 transform scale-110">{{ $top3[0]->pontos }} pts</span>
                        </div>
                        <div class="w-full relative h-[190px] md:h-[240px] bg-gradient-to-t from-amber-200 to-amber-100 dark:from-amber-600/50 dark:to-amber-500/20 rounded-t-3xl border-t-[6px] border-amber-400 dark:border-amber-500 shadow-xl shadow-amber-500/10 group-hover:h-[200px] md:group-hover:h-[250px] transition-all duration-500 flex justify-center">
                            <div class="absolute -top-7 w-14 h-14 bg-amber-100 dark:bg-amber-900 border-[6px] border-white dark:border-slate-800 rounded-full flex items-center justify-center font-black text-2xl text-amber-600 dark:text-amber-400 shadow-lg">1</div>
                        </div>
                    </div>

                    {{-- 3º Lugar --}}
                    @if (isset($top3[2]))
                        <div class="flex flex-col items-center group w-1/3 max-w-[180px] ui-animate-fade-up" style="animation-duration: 1s;">
                            <div class="mb-4 text-center">
                                <span class="block text-[10px] md:text-xs font-black text-orange-400/80 dark:text-orange-300/70 uppercase tracking-widest mb-2">3º Lugar</span>
                                <span class="block text-sm md:text-lg font-black text-slate-800 dark:text-white truncate w-24 md:w-full px-1 mb-1 leading-tight">{{ $top3[2]->nome }}</span>
                                <span class="block text-[10px] font-bold text-slate-500 mb-2">{{ $top3[2]->subtexto }}</span>
                                <span class="inline-flex px-3 py-1 bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/30 text-orange-600 dark:text-orange-400 rounded-xl text-xs font-black shadow-sm">{{ $top3[2]->pontos }} pts</span>
                            </div>
                            <div class="w-full relative h-[110px] md:h-[140px] bg-gradient-to-t from-orange-200/50 to-orange-100/50 dark:from-orange-800/30 dark:to-orange-700/20 rounded-t-2xl border-t-4 border-orange-300 dark:border-orange-600 shadow-inner group-hover:h-[120px] md:group-hover:h-[150px] transition-all duration-500 flex justify-center">
                                <div class="absolute -top-5 w-10 h-10 bg-white dark:bg-slate-800 border-4 border-orange-200 dark:border-orange-700 rounded-full flex items-center justify-center font-black text-lg text-orange-500 dark:text-orange-400 shadow-md">3</div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        @else
            <div class="ui-empty py-16 border-none shadow-none text-center">
                <div class="ui-empty-icon text-5xl mb-4">⭐</div>
                <h3 class="ui-empty-title">Nenhuma pontuação registrada</h3>
                <p class="ui-empty-description">Ainda não há dados suficientes para gerar o ranking deste período.</p>
            </div>
        @endif

        {{-- Tabela do Ranking Geral --}}
        <div class="ui-card p-0 overflow-hidden px-0 sm:px-0 mx-4 sm:mx-0">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Classificação Geral</h3>
                    <p class="text-sm font-medium text-slate-500 mt-0.5">Visão consolidada de toda a pontuação.</p>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 text-center w-16">Pos</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500">Nome ou Unidade</th>
                            <th class="hidden md:table-cell px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 text-center">Uniforme</th>
                            <th class="hidden md:table-cell px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 text-center">Bíblia</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 text-center bg-[#002F6C]/5 dark:bg-blue-500/10">Total de Pontos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($data as $index => $item)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-center">
                                    @if ($index == 0)
                                        <span class="text-xl">🥇</span>
                                    @elseif($index == 1)
                                        <span class="text-xl">🥈</span>
                                    @elseif($index == 2)
                                        <span class="text-xl">🥉</span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-black text-xs">{{ $index + 1 }}º</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-1.5 h-10 rounded-full" style="background-color: {{ $item->cor ?? '#CBD5E1' }}"></div>
                                        <div>
                                            <p class="font-black text-slate-800 dark:text-white uppercase tracking-tight">{{ $item->nome }}</p>
                                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5">{{ $item->subtexto }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 text-center">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ $item->detalhes['uniforme'] ?? '-' }}</span>
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 text-center">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ $item->detalhes['biblia'] ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center bg-[#002F6C]/[0.02] dark:bg-blue-500/5">
                                    <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-xl border-2 border-[#002F6C] dark:border-blue-500 bg-[#002F6C] text-white font-black text-sm shadow-md shadow-blue-900/20">
                                        {{ $item->pontos }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
