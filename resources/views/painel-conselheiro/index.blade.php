<x-app-layout>
    <x-slot name="header">Minha Unidade — {{ $unidade->nome }}</x-slot>

    <div class="ui-page space-y-6 max-w-5xl mx-auto">

        {{-- Header da Unidade --}}
        <div class="ui-card p-6 flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="w-16 h-16 rounded-2xl bg-[#002F6C] text-white flex items-center justify-center font-black text-3xl shadow-inner shrink-0">
                {{ mb_strtoupper(mb_substr($unidade->nome, 0, 1)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ $unidade->nome }}</h2>
                @if ($unidade->grito_guerra)
                    <p class="text-slate-500 italic text-sm mt-1">"{{ $unidade->grito_guerra }}"</p>
                @endif
                <div class="flex flex-wrap gap-4 mt-3 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <span>{{ $membros->count() }} membros ativos</span>
                    <span>·</span>
                    <span>{{ $membros->where('ausente_recente', true)->count() }} com baixa frequência</span>
                </div>
            </div>
            <a href="{{ route('frequencia.create') }}" class="ui-btn-primary w-full sm:w-auto shrink-0">
                Registrar Chamada
            </a>
        </div>

        {{-- Frequência recente --}}
        @if ($ultimasReunioes->isNotEmpty())
            <div class="ui-card p-6">
                <h3 class="font-black text-slate-700 dark:text-slate-200 mb-4">Últimas reuniões</h3>
                <div class="flex gap-3 flex-wrap">
                    @foreach ($ultimasReunioes as $reuniao)
                        @php $pct = $reuniao->total > 0 ? round($reuniao->presentes / $reuniao->total * 100) : 0; @endphp
                        <div class="flex flex-col items-center p-3 rounded-xl border border-slate-100 dark:border-slate-700 min-w-[80px]">
                            <span class="text-xs font-bold text-slate-400">{{ \Carbon\Carbon::parse($reuniao->data_reuniao)->format('d/m') }}</span>
                            <span class="text-2xl font-black {{ $pct >= 75 ? 'text-emerald-500' : ($pct >= 50 ? 'text-amber-500' : 'text-red-500') }}">{{ $pct }}%</span>
                            <span class="text-[10px] text-slate-400">{{ $reuniao->presentes }}/{{ $reuniao->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tabela de membros --}}
        <div class="ui-card overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-slate-700 dark:text-slate-200">Membros da unidade</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($membros as $membro)
                    <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-[#002F6C]/10 dark:bg-blue-500/10 flex items-center justify-center font-black text-[#002F6C] dark:text-blue-400 text-sm shrink-0">
                            {{ mb_strtoupper(mb_substr($membro->nome, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-slate-800 dark:text-white text-sm leading-none truncate">{{ $membro->nome }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $membro->classe }}</p>
                        </div>
                        <div class="hidden sm:flex flex-col items-center min-w-[80px]">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Classe</span>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 mt-1">
                                <div class="bg-purple-500 h-full rounded-full" style="width: {{ $membro->progresso_classe }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 mt-0.5">{{ $membro->progresso_classe }}%</span>
                        </div>
                        <div class="flex flex-col items-center min-w-[60px]">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Freq.</span>
                            <span class="font-black text-sm mt-0.5 {{ $membro->taxa_frequencia >= 75 ? 'text-emerald-500' : ($membro->taxa_frequencia >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                                {{ $membro->taxa_frequencia }}%
                            </span>
                        </div>
                        <div class="hidden md:flex flex-col items-center min-w-[60px]">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Espec.</span>
                            <span class="font-black text-sm text-slate-600 dark:text-slate-300 mt-0.5">{{ $membro->total_especialidades }}</span>
                        </div>
                        <a href="{{ route('desbravadores.show', $membro->id) }}"
                           class="text-slate-400 hover:text-[#002F6C] dark:hover:text-blue-400 transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-slate-400">Nenhum membro ativo nesta unidade.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
