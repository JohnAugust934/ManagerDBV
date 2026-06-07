<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('especialidades.show', $especialidade) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-slate-400">Histórico</p>
                <h2 class="font-black text-2xl text-slate-800 dark:text-white leading-tight">{{ $especialidade->nome }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="ui-page max-w-4xl mx-auto ui-animate-fade-up pb-20">
        <div class="ui-card overflow-hidden">
            <div class="h-1.5 w-full" style="background: {{ $especialidade->cor_fundo ?? '#9CA3AF' }}"></div>

            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">Registro de Alterações</h3>
                    <span class="text-xs font-black uppercase tracking-widest text-slate-400">{{ $historico->total() }} eventos</span>
                </div>

                @if($historico->isEmpty())
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 px-6 py-10 text-center">
                        <p class="text-slate-500 font-semibold">Nenhum registro de auditoria encontrado.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($historico as $registro)
                            @php
                                $dados = json_decode($registro->dados, true) ?? [];
                                $acaoConfig = match($registro->acao) {
                                    'created' => ['label' => 'Criada', 'color' => 'text-emerald-700 bg-emerald-100 dark:text-emerald-300 dark:bg-emerald-900/40', 'icon' => 'M12 4v16m8-8H4'],
                                    'updated' => ['label' => 'Editada', 'color' => 'text-blue-700 bg-blue-100 dark:text-blue-300 dark:bg-blue-900/40', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                                    'deleted' => ['label' => 'Removida', 'color' => 'text-red-700 bg-red-100 dark:text-red-300 dark:bg-red-900/40', 'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
                                    default   => ['label' => $registro->acao, 'color' => 'text-slate-600 bg-slate-100 dark:text-slate-300 dark:bg-slate-800', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                };
                            @endphp
                            <div class="flex gap-4 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
                                <div class="shrink-0 mt-0.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black {{ $acaoConfig['color'] }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $acaoConfig['icon'] }}"/></svg>
                                        {{ $acaoConfig['label'] }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                            {{ $registro->user_name ?? 'Sistema' }}
                                        </p>
                                        <p class="text-xs text-slate-400 font-semibold shrink-0">
                                            {{ \Carbon\Carbon::parse($registro->created_at)->format('d/m/Y \à\s H:i') }}
                                        </p>
                                    </div>
                                    @if(!empty($dados))
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @if(isset($dados['nome']))
                                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-0.5">
                                                    {{ $dados['nome'] }}
                                                </span>
                                            @endif
                                            @if(isset($dados['area']))
                                                <span class="text-xs font-semibold text-slate-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-0.5">
                                                    {{ $dados['area'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($historico->hasPages())
                        <div class="mt-6">
                            {{ $historico->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
