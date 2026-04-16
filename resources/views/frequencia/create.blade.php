<x-app-layout>
    <x-slot name="header">Nova Chamada</x-slot>

    <div class="ui-page space-y-0 max-w-4xl mx-auto pb-32 ui-animate-fade-up" x-data="{ filtroUnidade: '' }">

        {{-- CABEÇALHO HERO COMPACTO --}}
        <div class="relative overflow-hidden rounded-[28px] mb-6 bg-gradient-to-r from-[#001D42] to-[#002F6C] p-6 shadow-xl shadow-blue-900/30">
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-blue-400/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <p class="text-blue-300/80 text-[10px] font-black uppercase tracking-widest mb-1">Frequência</p>
                    <h1 class="text-2xl font-black text-white tracking-tight leading-none">Registro de Chamada</h1>
                    <p class="text-blue-100/60 text-xs font-bold mt-1.5">{{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('l, d/m/Y') }}</p>
                </div>
                <a href="{{ route('frequencia.index') }}" class="shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white text-xs font-black uppercase tracking-widest transition-colors border border-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Histórico
                </a>
            </div>
        </div>

        <form action="{{ route('frequencia.store') }}" method="POST" id="chamada-form">
            @csrf

            {{-- CONFIGURAÇÕES --}}
            <div class="ui-card p-5 mb-5 space-y-4">
                <p class="text-[10px] font-black text-[#002F6C] dark:text-blue-400 uppercase tracking-widest">Configurar Reunião</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="data" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Data da Reunião <span class="text-red-500">*</span></label>
                        <input type="date" name="data" id="data" value="{{ date('Y-m-d') }}" required class="ui-input w-full font-bold text-slate-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Filtrar por Unidade</label>
                        <div class="relative">
                            <select x-model="filtroUnidade" class="ui-input w-full font-bold appearance-none pr-10">
                                <option value="">Todas as Unidades</option>
                                @foreach ($unidades as $u)
                                    <option value="{{ $u->id }}">{{ $u->nome }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                @can('gerenciar-colunas-chamada')
                <div class="pt-1">
                    <a href="{{ route('frequencia.columns.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-black text-[#002F6C] dark:text-blue-400 hover:bg-[#002F6C]/5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Gerenciar Colunas da Chamada
                    </a>
                </div>
                @endcan
            </div>

            {{-- LEGENDA DAS COLUNAS (apenas mobile) --}}
            @if (!empty($usesLegacyColumns) && $usesLegacyColumns)
                @php $cols = [['key'=>'presente','label'=>'Presente','pts'=>10,'icon'=>'M5 13l4 4L19 7','color'=>'emerald'], ['key'=>'pontual','label'=>'Pontual','pts'=>5,'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'blue'], ['key'=>'biblia','label'=>'Bíblia','pts'=>5,'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253','color'=>'purple'], ['key'=>'uniforme','label'=>'Uniforme','pts'=>10,'icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','color'=>'amber']]; @endphp
            @else
                @php
                    $cols = $columns->map(fn($c) => ['key'=>'col_'.$c->id, 'label'=>$c->name, 'pts'=>$c->points, 'id'=>$c->id])->toArray();
                @endphp
            @endif

            {{-- LEGENDA SCROLLÁVEL --}}
            <div class="mb-4 flex items-center gap-2 overflow-x-auto pb-1 px-0.5">
                @if (!empty($usesLegacyColumns) && $usesLegacyColumns)
                    @foreach ($cols as $col)
                        <div class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
                            <div class="w-5 h-5 rounded-full bg-{{ $col['color'] }}-100 dark:bg-{{ $col['color'] }}-500/20 text-{{ $col['color'] }}-600 dark:text-{{ $col['color'] }}-400 flex items-center justify-center">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $col['icon'] }}"/></svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $col['label'] }}</span>
                            <span class="text-[9px] font-black text-slate-400">+{{ $col['pts'] }}pts</span>
                        </div>
                    @endforeach
                @else
                    @foreach ($columns as $col)
                        <div class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ mb_strtoupper($col->name, 'UTF-8') }}</span>
                            <span class="text-[9px] font-black text-slate-400">({{ $col->points }})</span>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- LISTA DE DESBRAVADORES --}}
            @if ($unidades->isEmpty())
                <div class="ui-card p-12 flex flex-col items-center text-center border-dashed border-2 border-slate-200 dark:border-slate-800 bg-transparent shadow-none">
                    <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <h3 class="text-lg font-black text-slate-700 dark:text-slate-300 mb-2">Nenhuma unidade</h3>
                    <p class="text-sm font-bold text-slate-400">Cadastre unidades e vincule desbravadores para realizar chamadas.</p>
                </div>
            @else
                <div class="space-y-8">
                    @foreach ($unidades as $unidade)
                        @if ($unidade->desbravadores->count() > 0)
                            <div x-show="filtroUnidade === '' || filtroUnidade == '{{ $unidade->id }}'"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="space-y-3">

                                {{-- Header da Unidade --}}
                                <div class="flex items-center gap-3 px-1">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#002F6C] to-blue-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md shadow-blue-900/20">
                                        {{ mb_strtoupper(substr($unidade->nome, 0, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        <h2 class="font-black text-slate-800 dark:text-white text-sm uppercase tracking-widest">{{ $unidade->nome }}</h2>
                                    </div>
                                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-black text-[10px] uppercase tracking-widest rounded-full">
                                        {{ $unidade->desbravadores->count() }} membros
                                    </span>
                                </div>

                                {{-- Cards dos Desbravadores --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach ($unidade->desbravadores as $dbv)
                                        <div class="ui-card p-0 overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm" x-data="{ presente: false }">
                                            <input type="hidden" name="presencas[{{ $dbv->id }}][registrado]" value="1">

                                            {{-- Nome do DBV --}}
                                            <div class="flex items-center gap-3 p-4 border-b border-slate-100 dark:border-slate-800">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center font-black text-sm text-slate-500 dark:text-slate-300 shrink-0 border border-slate-200 dark:border-slate-700"
                                                     :class="presente ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white border-emerald-500 dark:border-emerald-500 dark:from-emerald-500 dark:to-emerald-700' : ''">
                                                    {{ mb_strtoupper(substr($dbv->nome, 0, 1)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-black text-sm text-slate-800 dark:text-white uppercase tracking-tight truncate">{{ $dbv->nome }}</p>
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $unidade->nome }}</span>
                                                </div>
                                                {{-- Badge presente --}}
                                                <div x-show="presente" x-transition class="shrink-0">
                                                    <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-[9px] font-black uppercase tracking-widest rounded-full">✓ Presente</span>
                                                </div>
                                            </div>

                                            {{-- Toggle Buttons das Colunas --}}
                                            <div class="flex flex-wrap gap-2 p-3 bg-slate-50/70 dark:bg-slate-900/30">
                                                @if (!empty($usesLegacyColumns) && $usesLegacyColumns)
                                                    {{-- PRESENTE --}}
                                                    <label class="cursor-pointer group" x-data>
                                                        <input type="checkbox" name="presencas[{{ $dbv->id }}][presente]" value="1" class="peer sr-only" @change="presente = $event.target.checked">
                                                        <div class="flex items-center gap-1.5 h-11 px-3 rounded-xl border-2 text-[11px] font-black uppercase tracking-widest transition-all select-none
                                                            border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500
                                                            peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700
                                                            dark:peer-checked:border-emerald-500 dark:peer-checked:bg-emerald-500/20 dark:peer-checked:text-emerald-400
                                                            active:scale-95">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                            <span>Presente</span>
                                                            <span class="text-[9px] opacity-60">+10</span>
                                                        </div>
                                                    </label>
                                                    {{-- PONTUAL --}}
                                                    <label class="cursor-pointer">
                                                        <input type="checkbox" name="presencas[{{ $dbv->id }}][pontual]" value="1" class="peer sr-only">
                                                        <div class="flex items-center gap-1.5 h-11 px-3 rounded-xl border-2 text-[11px] font-black uppercase tracking-widest transition-all select-none
                                                            border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500
                                                            peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                                            dark:peer-checked:border-blue-500 dark:peer-checked:bg-blue-500/20 dark:peer-checked:text-blue-400
                                                            active:scale-95">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <span>Pontual</span>
                                                            <span class="text-[9px] opacity-60">+5</span>
                                                        </div>
                                                    </label>
                                                    {{-- BÍBLIA --}}
                                                    <label class="cursor-pointer">
                                                        <input type="checkbox" name="presencas[{{ $dbv->id }}][biblia]" value="1" class="peer sr-only">
                                                        <div class="flex items-center gap-1.5 h-11 px-3 rounded-xl border-2 text-[11px] font-black uppercase tracking-widest transition-all select-none
                                                            border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500
                                                            peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700
                                                            dark:peer-checked:border-purple-500 dark:peer-checked:bg-purple-500/20 dark:peer-checked:text-purple-400
                                                            active:scale-95">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                            <span>Bíblia</span>
                                                            <span class="text-[9px] opacity-60">+5</span>
                                                        </div>
                                                    </label>
                                                    {{-- UNIFORME --}}
                                                    <label class="cursor-pointer">
                                                        <input type="checkbox" name="presencas[{{ $dbv->id }}][uniforme]" value="1" class="peer sr-only">
                                                        <div class="flex items-center gap-1.5 h-11 px-3 rounded-xl border-2 text-[11px] font-black uppercase tracking-widest transition-all select-none
                                                            border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500
                                                            peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700
                                                            dark:peer-checked:border-amber-500 dark:peer-checked:bg-amber-500/20 dark:peer-checked:text-amber-400
                                                            active:scale-95">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                            <span>Uniforme</span>
                                                            <span class="text-[9px] opacity-60">+10</span>
                                                        </div>
                                                    </label>
                                                @else
                                                    @foreach ($columns as $column)
                                                        <label class="cursor-pointer">
                                                            <input type="checkbox" name="presencas[{{ $dbv->id }}][colunas][{{ $column->id }}]" value="1" class="peer sr-only"
                                                                {{ $column->name === 'Presente' || strtolower($column->name) === 'presente' ? '@change="presente = $event.target.checked"' : '' }}>
                                                            <div class="flex items-center gap-1.5 h-11 px-3 rounded-xl border-2 text-[11px] font-black uppercase tracking-widest transition-all select-none
                                                                border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500
                                                                peer-checked:border-[#002F6C] peer-checked:bg-blue-50 peer-checked:text-[#002F6C]
                                                                dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-500/20 dark:peer-checked:text-blue-400
                                                                active:scale-95">
                                                                <span>{{ $column->name }}</span>
                                                                <span class="text-[9px] opacity-60">+{{ $column->points }}</span>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </form>

        {{-- BOTÃO SALVAR FLUTUANTE (Bottom-safe para mobile) --}}
        @if (!$unidades->isEmpty())
        <div class="fixed bottom-0 left-0 right-0 z-50 p-4 sm:p-6 bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 shadow-2xl shadow-black/20">
            <div class="max-w-4xl mx-auto flex items-center gap-3">
                <div class="flex-1 hidden sm:block">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Chamada em andamento</p>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y') }}</p>
                </div>
                <button type="submit" form="chamada-form"
                    class="flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-4 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-black text-sm uppercase tracking-widest rounded-2xl shadow-xl shadow-emerald-900/30 active:scale-95 transition-all duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    Salvar Chamada
                </button>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
