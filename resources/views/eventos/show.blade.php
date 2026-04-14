<x-app-layout>
    <x-slot name="header">{{ $evento->nome }}</x-slot>

    <div class="ui-page space-y-6 max-w-[1200px] ui-animate-fade-up">

        {{-- Barra de Ações Topo --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 sm:px-0">
            <a href="{{ route('eventos.index') }}" class="flex items-center gap-2 w-max px-4 py-2.5 rounded-xl font-bold text-sm text-slate-500 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Calendário
            </a>
            @can('secretaria')
            <div class="flex items-center gap-3">
                <a href="{{ route('eventos.edit', $evento->id) }}" class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-black text-sm bg-[#002F6C]/10 hover:bg-[#002F6C]/20 text-[#002F6C] dark:bg-blue-500/20 dark:hover:bg-blue-500/30 dark:text-blue-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </a>
                @if ($evento->desbravadores->count() == 0)
                <form action="{{ route('eventos.destroy', $evento->id) }}" method="POST" onsubmit="return confirm('Excluir este evento permanentemente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-black text-sm bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-500/20 dark:hover:bg-red-500/30 dark:text-red-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Excluir
                    </button>
                </form>
                @endif
            </div>
            @endcan
        </div>

        {{-- DADOS OCULTOS PARA JS --}}
        <input type="hidden" id="evento-valor" value="{{ $evento->valor }}">
        <input type="hidden" id="count-pagos" value="{{ $evento->desbravadores->where('pivot.pago', true)->count() }}">
        <input type="hidden" id="count-total" value="{{ $evento->desbravadores->count() }}">

        {{-- HERO CARD: Detalhes do Evento --}}
        <div class="ui-card overflow-hidden">
            {{-- Banner Superior --}}
            <div class="h-32 sm:h-40 relative bg-gradient-to-br from-[#002F6C] via-blue-700 to-blue-500 overflow-hidden">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-black/30 to-transparent"></div>
                
                <div class="absolute top-4 left-5 flex items-center gap-2">
                    @if (\Carbon\Carbon::parse($evento->data_fim)->isPast())
                        <span class="px-3 py-1 bg-black/20 text-white/80 text-[9px] font-black rounded-full uppercase tracking-widest border border-white/10">Encerrado</span>
                    @else
                        <span class="px-3 py-1 bg-emerald-500/30 text-white text-[9px] font-black rounded-full uppercase tracking-widest border border-emerald-400/30 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Ativo
                        </span>
                    @endif
                </div>

                <div class="absolute bottom-5 left-6">
                    <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight uppercase tracking-tight drop-shadow-md">{{ $evento->nome }}</h1>
                </div>
            </div>

            {{-- Info Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-px bg-slate-100 dark:bg-slate-800">
                <div class="flex items-center gap-3 p-5 bg-white dark:bg-slate-900">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cronograma</p>
                        <p class="text-sm font-black text-slate-800 dark:text-white truncate">{{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y H:i') }}</p>
                        @if ($evento->data_fim)
                        <p class="text-xs font-semibold text-slate-400">até {{ \Carbon\Carbon::parse($evento->data_fim)->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3 p-5 bg-white dark:bg-slate-900">
                    <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Localização</p>
                        <p class="text-sm font-black text-slate-800 dark:text-white">{{ $evento->local }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-5 bg-white dark:bg-slate-900">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Inscrição</p>
                        <p class="text-sm font-black {{ $evento->valor == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-800 dark:text-white' }}">
                            {{ $evento->valor == 0 ? 'Gratuito' : 'R$ ' . number_format($evento->valor, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARDS DE ESTATÍSTICAS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-4 sm:px-0">
            <div class="ui-card p-5">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Inscritos</p>
                <p class="text-4xl font-black text-slate-800 dark:text-white" id="display-inscritos">{{ $evento->desbravadores->count() }}</p>
            </div>
            <div class="ui-card p-5 sm:col-span-2">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Arrecadado / Meta</p>
                        @php $total = $evento->desbravadores->count(); $pagos = $evento->desbravadores->where('pivot.pago', true)->count(); $pct = $total > 0 ? ($pagos / $total) * 100 : 0; @endphp
                        <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400" id="display-arrecadado">
                            R$ {{ number_format($pagos * $evento->valor, 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">de <span id="display-meta">R$ {{ number_format($total * $evento->valor, 2, ',', '.') }}</span> esperados</p>
                    </div>
                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1 rounded-full">{{ round($pct) }}%</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden shadow-inner">
                    <div id="progress-bar" class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>

        {{-- ÁREA PRINCIPAL (Inscritos + Inscrição) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-4 sm:px-0" x-data="{ tab: 'lista' }">

            {{-- Lista de Inscritos --}}
            <div class="lg:col-span-2 ui-card overflow-hidden flex flex-col">
                {{-- Tabs Header --}}
                <div class="flex border-b border-slate-100 dark:border-slate-800">
                    <button @click="tab = 'lista'"
                        :class="tab === 'lista' ? 'border-[#002F6C] text-[#002F6C] dark:border-blue-400 dark:text-blue-400 bg-[#002F6C]/5 dark:bg-blue-500/10' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="flex-1 py-4 px-4 text-xs font-black uppercase tracking-wider border-b-2 transition-all">
                        Lista de Inscritos
                    </button>
                    @if ($naoInscritos->isNotEmpty())
                    <button @click="tab = 'novo'"
                        :class="tab === 'novo' ? 'border-[#002F6C] text-[#002F6C] dark:border-blue-400 dark:text-blue-400 bg-[#002F6C]/5 dark:bg-blue-500/10' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        class="flex-1 py-4 px-4 text-xs font-black uppercase tracking-wider border-b-2 transition-all lg:hidden">
                        Nova Inscrição
                    </button>
                    @endif
                </div>

                {{-- Tab: Lista --}}
                <div x-show="tab === 'lista'" class="flex-1 flex flex-col overflow-hidden">
                    @if ($evento->desbravadores->isEmpty())
                        <div class="flex-1 flex flex-col items-center justify-center p-12 text-center">
                            <svg class="w-16 h-16 mb-4 text-slate-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <p class="text-lg font-black text-slate-400 uppercase tracking-wide">Ninguém inscrito</p>
                            <p class="text-sm text-slate-400 font-medium mt-1">Use o painel ao lado para inscrever membros.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-900/50">
                                        <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500">Desbravador</th>
                                        <th class="px-4 py-4 text-center text-[11px] font-black uppercase tracking-widest text-slate-500">Pagamento</th>
                                        <th class="px-4 py-4 text-center text-[11px] font-black uppercase tracking-widest text-slate-500">Docs</th>
                                        <th class="px-4 py-4 text-right text-[11px] font-black uppercase tracking-widest text-slate-500">Ação</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($evento->desbravadores as $dbv)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors group">
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#002F6C] to-blue-500 text-white flex items-center justify-center font-black text-xs shadow-sm shrink-0">
                                                    {{ mb_strtoupper(substr($dbv->nome, 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-black text-slate-800 dark:text-white text-[13px] uppercase truncate">{{ $dbv->nome }}</p>
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $dbv->unidade->nome ?? 'Sem Unidade' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <button onclick="toggleStatus(this, '{{ route('eventos.status', [$evento->id, $dbv->id]) }}', 'pago')"
                                                data-active="{{ $dbv->pivot->pago ? 'true' : 'false' }}"
                                                class="px-3 py-1.5 rounded-full text-[9px] font-black border transition-all shadow-sm w-24 hover:scale-105 active:scale-95
                                                {{ $dbv->pivot->pago ? 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/30' : 'bg-red-50 text-red-600 border-red-100 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/30' }}">
                                                {{ $dbv->pivot->pago ? 'PAGO' : 'PENDENTE' }}
                                            </button>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <a href="{{ route('eventos.autorizacao', [$evento->id, $dbv->id]) }}" target="_blank"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-[#002F6C] hover:bg-[#002F6C]/10 dark:hover:bg-blue-500/20 dark:hover:text-blue-400 transition-colors"
                                                title="Gerar Autorização">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            <form action="{{ route('eventos.remover-inscricao', [$evento->id, $dbv->id]) }}" method="POST" onsubmit="return confirm('Remover {{ $dbv->nome }} do evento?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 rounded-xl text-slate-300 dark:text-slate-600 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 dark:hover:text-red-400 transition-colors opacity-50 group-hover:opacity-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Painel de Nova Inscrição --}}
            <div class="lg:block" :class="tab === 'novo' ? 'block' : 'hidden lg:block'">
                <div class="ui-card p-6 lg:sticky lg:top-24">
                    <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-xl bg-[#002F6C]/10 dark:bg-blue-500/20 flex items-center justify-center text-[#002F6C] dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-slate-800 dark:text-white uppercase tracking-tight">Adicionar Inscritos</h3>
                            <p class="text-xs font-bold text-slate-500 mt-0.5">Membros ativos não inscritos</p>
                        </div>
                    </div>

                    @if ($naoInscritos->isEmpty())
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-2xl text-sm text-center border border-emerald-200 dark:border-emerald-500/30">
                            <p class="font-black text-base mb-1">Tudo pronto! 🎉</p>
                            <p class="font-medium">Todos os desbravadores ativos já estão inscritos.</p>
                        </div>
                    @else
                        <div x-data="{ mode: 'single' }" class="space-y-4">
                            {{-- Seletor de Modo --}}
                            <div class="flex bg-slate-100 dark:bg-slate-800 p-1.5 rounded-xl gap-1">
                                <button type="button" @click="mode = 'single'"
                                    :class="mode === 'single' ? 'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'"
                                    class="flex-1 py-2.5 text-xs font-black rounded-lg transition-all uppercase tracking-wider">Individual</button>
                                <button type="button" @click="mode = 'multiple'"
                                    :class="mode === 'multiple' ? 'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'"
                                    class="flex-1 py-2.5 text-xs font-black rounded-lg transition-all uppercase tracking-wider">Em Lote</button>
                            </div>

                            {{-- Individual --}}
                            <div x-show="mode === 'single'" x-transition>
                                <form action="{{ route('eventos.inscrever', $evento->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Selecione o membro</label>
                                        <div class="relative">
                                            <select name="desbravador_id" class="ui-input w-full appearance-none font-bold pr-8" required>
                                                <option value="">Clique para selecionar...</option>
                                                @foreach ($naoInscritos as $dbv)
                                                    <option value="{{ $dbv->id }}">{{ $dbv->nome }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></div>
                                        </div>
                                    </div>
                                    <button type="submit" class="ui-btn-primary w-full py-3 flex items-center justify-center gap-2 rounded-2xl font-black">
                                        Inscrever Agora
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    </button>
                                </form>
                            </div>

                            {{-- Lote --}}
                            <div x-show="mode === 'multiple'" x-transition>
                                <form action="{{ route('eventos.inscrever-lote', $evento->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div class="max-h-64 overflow-y-auto border-2 border-slate-100 dark:border-slate-800 rounded-2xl p-2 space-y-1 bg-slate-50 dark:bg-slate-900/50 custom-scrollbar">
                                        <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 mb-1">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="checkbox" onclick="document.querySelectorAll('.check-item').forEach(el => el.checked = this.checked)" class="w-5 h-5 rounded text-[#002F6C] border-2 border-slate-300 dark:border-slate-600">
                                                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Selecionar Todos</span>
                                            </label>
                                        </div>
                                        @foreach ($naoInscritos as $dbv)
                                        <label class="flex items-center gap-3 p-2.5 hover:bg-white dark:hover:bg-slate-800 rounded-xl cursor-pointer transition-colors">
                                            <input type="checkbox" name="desbravadores[]" value="{{ $dbv->id }}" class="check-item w-5 h-5 rounded text-[#002F6C] border-2 border-slate-300 dark:border-slate-600">
                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $dbv->nome }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black rounded-2xl shadow-lg shadow-emerald-900/20 transition-all">
                                        Inscrever Selecionados
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <script>
        async function toggleStatus(button, url, campo) {
            const wasActive = button.getAttribute('data-active') === 'true';
            const newState = !wasActive;
            button.innerText = '...'; button.disabled = true; button.classList.add('opacity-50');
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify({ _method: 'PATCH', campo: campo, valor: newState ? '1' : '0' })
                });
                if (!response.ok) throw new Error('Erro');
                const data = await response.json();
                if (data.success) {
                    button.setAttribute('data-active', newState);
                    button.disabled = false; button.classList.remove('opacity-50');
                    if (campo === 'pago') {
                        button.innerText = newState ? 'PAGO' : 'PENDENTE';
                        button.className = newState
                            ? 'px-3 py-1.5 rounded-full text-[9px] font-black border transition-all shadow-sm w-24 hover:scale-105 active:scale-95 bg-emerald-100 text-emerald-700 border-emerald-200'
                            : 'px-3 py-1.5 rounded-full text-[9px] font-black border transition-all shadow-sm w-24 hover:scale-105 active:scale-95 bg-red-50 text-red-600 border-red-100';
                        updateFinancialCards(newState);
                    }
                }
            } catch (e) { console.error(e); button.innerText = wasActive ? 'PAGO' : 'PENDENTE'; button.disabled = false; button.classList.remove('opacity-50'); }
        }
        function updateFinancialCards(isPaying) {
            const v = parseFloat(document.getElementById('evento-valor').value);
            let pagos = parseInt(document.getElementById('count-pagos').value);
            const total = parseInt(document.getElementById('count-total').value);
            pagos = isPaying ? pagos + 1 : pagos - 1;
            document.getElementById('count-pagos').value = pagos;
            const fmt = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
            document.getElementById('display-arrecadado').innerText = fmt.format(pagos * v);
            document.getElementById('progress-bar').style.width = (total > 0 ? (pagos / total) * 100 : 0) + '%';
        }
    </script>
</x-app-layout>
