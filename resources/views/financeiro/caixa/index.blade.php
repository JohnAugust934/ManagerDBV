<x-app-layout>
    <div class="ui-page space-y-6 ui-animate-fade-up" x-data="{ aba: 'lancamentos', deleteId: null, deleteDesc: '' }">

        <x-page-title title="Fluxo de Caixa" subtitle="Entradas, saídas e saldo do clube.">
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </x-slot:icon>
        </x-page-title>

        {{-- 3 Cards de Resumo --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-4 sm:px-0">

            <div class="ui-card p-6 relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-slate-500/5 dark:bg-white/5"></div>
                <div class="absolute -right-8 -bottom-6 w-32 h-32 rounded-full bg-slate-500/5 dark:bg-white/5"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 relative">Saldo Atual</p>
                <h3 class="text-3xl font-black relative {{ $saldoAtual >= 0 ? 'text-slate-800 dark:text-white' : 'text-red-500 dark:text-red-400' }}">
                    R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                </h3>
                <p class="text-[11px] font-bold text-slate-400 mt-2 relative">Balanço total acumulado</p>
            </div>

            <div class="ui-card p-6 border-l-4 border-emerald-500 dark:border-emerald-600">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Entradas</p>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400">+ R$ {{ number_format($entradas, 2, ',', '.') }}</h3>
            </div>

            <div class="ui-card p-6 border-l-4 border-red-500 dark:border-red-600">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Saídas</p>
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-red-600 dark:text-red-400">- R$ {{ number_format($saidas, 2, ',', '.') }}</h3>
            </div>
        </div>

        {{-- Ação Principal --}}
        <div class="flex sm:justify-end">
            <a href="{{ route('caixa.create') }}" class="ui-btn-primary w-full sm:w-auto group">
                <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                Nova Movimentação
            </a>
        </div>

        {{-- Abas: Lançamentos / Auditoria --}}
        <div class="ui-card overflow-hidden px-4 sm:px-0">

            {{-- Tabs --}}
            <div class="flex border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <button @click="aba = 'lancamentos'"
                    :class="aba === 'lancamentos' ? 'border-b-2 border-[#002F6C] dark:border-blue-400 text-[#002F6C] dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                    class="px-5 py-4 text-[12px] font-black uppercase tracking-widest transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Histórico
                    <span class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full text-[10px] border border-slate-200 dark:border-slate-700">{{ $lancamentos->total() }}</span>
                </button>
                <button @click="aba = 'auditoria'"
                    :class="aba === 'auditoria' ? 'border-b-2 border-[#002F6C] dark:border-blue-400 text-[#002F6C] dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                    class="px-5 py-4 text-[12px] font-black uppercase tracking-widest transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Auditoria
                    <span class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full text-[10px] border border-slate-200 dark:border-slate-700">{{ $auditLogs->count() }}</span>
                </button>
            </div>

            {{-- Aba: Lançamentos --}}
            <div x-show="aba === 'lancamentos'">
                @if ($lancamentos->count() > 0)

                    {{-- Tabela Desktop --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th class="text-right">Valor</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lancamentos as $lancamento)
                                <tr class="group">
                                    <td class="text-[13px] font-black text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($lancamento->data_movimentacao)->format('d/m/Y') }}
                                    </td>
                                    <td class="!whitespace-normal">
                                        <p class="text-[13px] font-bold text-slate-800 dark:text-white">{{ $lancamento->descricao }}</p>
                                        @if($lancamento->criadoPor)
                                            <p class="text-[10px] text-slate-400 mt-0.5">por {{ $lancamento->criadoPor->name }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($lancamento->categoria)
                                            <span class="px-2.5 py-1 text-[10px] font-black rounded-lg bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 uppercase tracking-widest">{{ $lancamento->categoria }}</span>
                                        @else
                                            <span class="text-slate-300 dark:text-slate-700">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-[13px] font-black {{ $lancamento->tipo === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $lancamento->tipo === 'entrada' ? '+' : '-' }} R$ {{ number_format($lancamento->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        <div class="ui-row-actions justify-end">
                                            <a href="{{ route('caixa.edit', $lancamento) }}"
                                               class="p-2 rounded-xl text-slate-400 hover:text-[#002F6C] hover:bg-[#002F6C]/10 dark:hover:text-blue-400 dark:hover:bg-blue-500/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button type="button"
                                                @click="deleteId = {{ $lancamento->id }}; deleteDesc = '{{ addslashes($lancamento->descricao) }}'"
                                                class="p-2 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Cards Mobile --}}
                    <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($lancamentos as $lancamento)
                        <div class="px-4 py-4">
                            <div class="flex items-center gap-4">
                                <div class="shrink-0">
                                    @if ($lancamento->tipo === 'entrada')
                                        <div class="w-11 h-11 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        </div>
                                    @else
                                        <div class="w-11 h-11 rounded-full bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-400 border border-red-100 dark:border-red-500/20">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-slate-800 dark:text-white truncate">{{ $lancamento->descricao }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[11px] font-bold text-slate-400">{{ \Carbon\Carbon::parse($lancamento->data_movimentacao)->format('d/m') }}</span>
                                        @if ($lancamento->categoria)
                                            <span class="text-slate-300 dark:text-slate-700">•</span>
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">{{ $lancamento->categoria }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="shrink-0 pl-2">
                                    <span class="text-sm font-black {{ $lancamento->tipo === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $lancamento->tipo === 'entrada' ? '+' : '-' }} R$ {{ number_format($lancamento->valor, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-3 pl-14">
                                <a href="{{ route('caixa.edit', $lancamento) }}"
                                   class="flex-1 text-center py-2 text-[11px] font-black rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    Editar
                                </a>
                                <button type="button"
                                    @click="deleteId = {{ $lancamento->id }}; deleteDesc = '{{ addslashes($lancamento->descricao) }}'"
                                    class="flex-1 text-center py-2 text-[11px] font-black rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
                                    Excluir
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                        {{ $lancamentos->links() }}
                    </div>
                @else
                    <div class="ui-empty border-none shadow-none py-12">
                        <div class="ui-empty-icon"><svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
                        <h3 class="ui-empty-title">O caixa está vazio</h3>
                        <p class="ui-empty-description">Nenhum lançamento registrado ainda.</p>
                        <div class="mt-6"><a href="{{ route('caixa.create') }}" class="ui-btn-primary">Registrar Primeiro Lançamento</a></div>
                    </div>
                @endif
            </div>

            {{-- Aba: Auditoria --}}
            <div x-show="aba === 'auditoria'" style="display:none">
                @if($auditLogs->count() > 0)
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($auditLogs as $log)
                        <div class="px-5 py-4 flex items-start gap-4">
                            {{-- Ícone da ação --}}
                            <div class="shrink-0 mt-0.5">
                                @if($log->acao === 'criado')
                                    <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                @elseif($log->acao === 'editado')
                                    <div class="w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 flex items-center justify-center text-red-600 dark:text-red-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[11px] font-black uppercase tracking-widest px-2 py-0.5 rounded
                                        @if($log->acao === 'criado') bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400
                                        @elseif($log->acao === 'editado') bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400
                                        @else bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 @endif">
                                        {{ ucfirst($log->acao) }}
                                    </span>
                                    <span class="text-[12px] font-bold text-slate-700 dark:text-slate-200 truncate">
                                        {{ $log->dados_antes['descricao'] ?? $log->dados_depois['descricao'] ?? '—' }}
                                    </span>
                                </div>

                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-0.5">
                                    <span class="text-[11px] text-slate-400">
                                        por <span class="font-bold text-slate-600 dark:text-slate-300">{{ $log->usuario?->name ?? 'Usuário removido' }}</span>
                                    </span>
                                    <span class="text-[11px] text-slate-400">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>

                                {{-- Diff para edições --}}
                                @if($log->acao === 'editado' && $log->dados_antes && $log->dados_depois)
                                    @php
                                        $campos = ['descricao' => 'Descrição', 'valor' => 'Valor', 'tipo' => 'Tipo', 'categoria' => 'Categoria', 'data_movimentacao' => 'Data'];
                                        $mudancas = array_filter($campos, fn($k) => ($log->dados_antes[$k] ?? null) !== ($log->dados_depois[$k] ?? null), ARRAY_FILTER_USE_KEY);
                                    @endphp
                                    @if(count($mudancas))
                                        <div class="mt-2 space-y-1">
                                            @foreach($mudancas as $campo => $label)
                                            <div class="text-[11px] flex flex-wrap gap-1 items-center">
                                                <span class="text-slate-400 font-bold">{{ $label }}:</span>
                                                <span class="line-through text-red-500 dark:text-red-400 font-mono">{{ $log->dados_antes[$campo] ?? '—' }}</span>
                                                <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">{{ $log->dados_depois[$campo] ?? '—' }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif

                                {{-- Dados do registro excluído --}}
                                @if($log->acao === 'excluido' && $log->dados_antes)
                                    <div class="mt-2 flex flex-wrap gap-3">
                                        <span class="text-[11px] font-mono text-red-500 dark:text-red-400">
                                            {{ $log->dados_antes['tipo'] === 'entrada' ? '+' : '-' }} R$ {{ number_format((float)($log->dados_antes['valor'] ?? 0), 2, ',', '.') }}
                                        </span>
                                        @if($log->dados_antes['categoria'] ?? null)
                                            <span class="text-[11px] text-slate-400">{{ $log->dados_antes['categoria'] }}</span>
                                        @endif
                                        <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($log->dados_antes['data_movimentacao'])->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($auditLogs->count() >= 30)
                        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <p class="text-[11px] text-slate-400 text-center">Exibindo os últimos 30 eventos. Registros mais antigos estão preservados no banco.</p>
                        </div>
                    @endif
                @else
                    <div class="ui-empty border-none shadow-none py-12">
                        <div class="ui-empty-icon">
                            <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="ui-empty-title">Sem eventos de auditoria</h3>
                        <p class="ui-empty-description">As próximas criações, edições e exclusões aparecerão aqui.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal de confirmação de exclusão --}}
        <div x-show="deleteId !== null" style="display:none" class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div x-show="deleteId !== null"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/75 dark:bg-black/80 backdrop-blur-sm"
                @click="deleteId = null"></div>

            <div class="flex min-h-full items-end justify-center p-4 sm:items-center sm:p-0">
                <div x-show="deleteId !== null"
                    x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                    class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-red-100 dark:border-red-500/20">

                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-500/20 flex items-center justify-center text-red-600 dark:text-red-400 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Excluir lançamento?</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                    "<span class="font-bold text-slate-700 dark:text-slate-300" x-text="deleteDesc"></span>"
                                    será excluído permanentemente. A ação será registrada no histórico de auditoria.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pb-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <button type="button" @click="deleteId = null" class="ui-btn-secondary w-full sm:w-auto">
                            Cancelar
                        </button>
                        <form :action="`/caixa/${deleteId}`" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-btn-danger w-full sm:w-auto">
                                Excluir Definitivamente
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
