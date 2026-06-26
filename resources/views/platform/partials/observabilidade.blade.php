{{-- Painel de Observabilidade (platform-admin).
     Consome exclusivamente o resumo operacional já agregado pelo backend
     (variável $operacional); nada é recalculado aqui. --}}
<div class="mt-8 space-y-4">
    <h2 class="text-lg font-black text-slate-800 dark:text-white">Observabilidade</h2>

    {{-- Indicadores rápidos --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        {{-- Clubes ativos --}}
        <div class="ui-card p-4">
            <p class="text-2xl font-black text-green-600 dark:text-green-400">{{ $operacional['clubesAtivos'] }}</p>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Clubes ativos</p>
        </div>

        {{-- Clubes inativos --}}
        <div class="ui-card p-4">
            <p class="text-2xl font-black {{ $operacional['clubesInativos'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }}">
                {{ $operacional['clubesInativos'] }}
            </p>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Clubes inativos</p>
        </div>

        {{-- Jobs na fila --}}
        <div class="ui-card p-4">
            @if ($operacional['queueSize'] !== null)
                <p class="text-2xl font-black {{ $operacional['queueSize'] > 100 ? 'text-red-600 dark:text-red-400' : ($operacional['queueSize'] > 20 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-white') }}">
                    {{ $operacional['queueSize'] }}
                </p>
            @else
                <p class="text-2xl font-black text-slate-500 dark:text-slate-400">—</p>
            @endif
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Jobs na fila</p>
        </div>

        {{-- Jobs falhos (24h) --}}
        <div class="ui-card p-4">
            @if ($operacional['falhasRecentes'] !== null)
                <p class="text-2xl font-black {{ $operacional['falhasRecentes'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-800 dark:text-white' }}">
                    {{ $operacional['falhasRecentes'] }}
                </p>
            @else
                <p class="text-2xl font-black text-slate-500 dark:text-slate-400">—</p>
            @endif
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Jobs falhos (24h)</p>
        </div>

        {{-- Queries lentas hoje — null = monitoramento desabilitado (não é zero) --}}
        <div class="ui-card p-4">
            @if ($operacional['queriesLentas'] === null)
                <p class="text-base font-black text-slate-500 dark:text-slate-400 leading-tight pt-1">Não monitorado</p>
            @else
                <p class="text-2xl font-black {{ $operacional['queriesLentas'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-white' }}">
                    {{ $operacional['queriesLentas'] }}
                </p>
            @endif
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Queries lentas (hoje)</p>
        </div>
    </div>

    {{-- Linha de status: versão, relatórios em processamento, total de falhas, health --}}
    <div class="ui-card p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-x-6 gap-y-3 text-sm">
        <div class="flex items-center gap-2">
            <span class="text-slate-500 dark:text-slate-400 font-medium">Versão:</span>
            <code class="bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-2 py-0.5 rounded text-xs font-mono">
                {{ $operacional['versao'] }}
            </code>
        </div>

        @if ($operacional['relatoriosPendentes'] !== null && $operacional['relatoriosPendentes'] > 0)
            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300 px-2.5 py-0.5 text-xs font-medium">
                {{ $operacional['relatoriosPendentes'] }} relatório(s) em processamento
            </span>
        @endif

        @if (($operacional['totalFalhas'] ?? 0) > 0)
            <div class="flex items-center gap-2">
                <span class="text-slate-500 dark:text-slate-400">Total de falhas acumuladas:</span>
                <span class="font-bold text-red-600 dark:text-red-400">{{ $operacional['totalFalhas'] }}</span>
            </div>
        @endif

        <a href="{{ route('health') }}" target="_blank"
           class="sm:ml-auto text-xs text-blue-700 dark:text-blue-400 hover:underline font-semibold">
            Ver /health →
        </a>
    </div>

    {{-- Último backup por clube --}}
    <div class="ui-card p-0 overflow-hidden">
        <div class="px-4 sm:px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wide">Último backup por clube</h3>
        </div>

        @php($backups = $operacional['ultimoBackupPorClube'])

        @forelse ($backups as $backup)
            @php($statusBackup = $backup['status'] ?? null)
            @php($okBackup = in_array($statusBackup, ['success', 'sucesso', 'ok'], true))
            @if ($loop->first)
                {{-- Tabela responsiva: vira lista empilhada no mobile (block) e tabela no sm+ --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-gray-100 dark:border-gray-700">
                                <th class="px-5 py-2">Clube</th>
                                <th class="px-5 py-2">Arquivo</th>
                                <th class="px-5 py-2">Status</th>
                                <th class="px-5 py-2 text-right">Tamanho</th>
                                <th class="px-5 py-2">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @endif
                            <tr>
                                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-white">
                                    {{ $backup['clube'] ?? 'Clube #'.$backup['club_id'] }}
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                    @if ($backup['filename'])
                                        <code class="text-xs font-mono break-all">{{ $backup['filename'] }}</code>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider
                                        {{ $okBackup
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $statusBackup ?? 'desconhecido' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-slate-600 dark:text-slate-300 tabular-nums">
                                    {{ $backup['size_bytes'] !== null ? \Illuminate\Support\Number::fileSize($backup['size_bytes']) : '—' }}
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ $backup['created_at'] ? \Illuminate\Support\Carbon::parse($backup['created_at'])->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : '—' }}
                                </td>
                            </tr>
            @if ($loop->last)
                        </tbody>
                    </table>
                </div>
            @endif
        @empty
            <x-empty-state
                title="Nenhum backup de clube registrado"
                description="Assim que um clube tiver ao menos um backup, o registro mais recente aparece aqui." />
        @endforelse

        {{-- Versão mobile: cards empilhados --}}
        @if (count($backups) > 0)
            <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($backups as $backup)
                    @php($statusBackup = $backup['status'] ?? null)
                    @php($okBackup = in_array($statusBackup, ['success', 'sucesso', 'ok'], true))
                    <div class="px-4 py-3 space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-slate-800 dark:text-white truncate">
                                {{ $backup['clube'] ?? 'Clube #'.$backup['club_id'] }}
                            </span>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
                                {{ $okBackup
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'
                                    : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                {{ $statusBackup ?? 'desconhecido' }}
                            </span>
                        </div>
                        @if ($backup['filename'])
                            <code class="block text-xs font-mono text-slate-600 dark:text-slate-300 break-all">{{ $backup['filename'] }}</code>
                        @endif
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span>{{ $backup['size_bytes'] !== null ? \Illuminate\Support\Number::fileSize($backup['size_bytes']) : '—' }}</span>
                            <span>{{ $backup['created_at'] ? \Illuminate\Support\Carbon::parse($backup['created_at'])->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
