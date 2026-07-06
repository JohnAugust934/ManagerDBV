<x-app-layout>

    <div class="ui-page">
        {{-- Feedback --}}
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-800 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- Cabeçalho da seção --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Relatórios pesados (fichas completas, fichas médicas, financeiro) são gerados em segundo plano.
                    Cada arquivo fica disponível por <strong>24 horas</strong> após a geração.
                </p>
            </div>
            <a href="{{ route('relatorios.index') }}" class="ui-btn-secondary w-full sm:w-auto">
                ← Gerar novo relatório
            </a>
        </div>

        @if ($relatorios->isEmpty())
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-8 text-center">
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    Nenhum relatório gerado ainda. Acesse a Central de Relatórios e solicite um relatório em lote.
                </p>
            </div>
        @else
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Tipo</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Solicitado em</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Expira em</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach ($relatorios as $relatorio)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                                    {{ $relatorio->tipoLabel() }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($relatorio->status === 'pronto' && ! $relatorio->expirou())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 px-2.5 py-0.5 text-xs font-medium">
                                            Pronto
                                        </span>
                                    @elseif ($relatorio->expirou())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2.5 py-0.5 text-xs font-medium">
                                            Expirado
                                        </span>
                                    @elseif ($relatorio->status === 'processando')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 px-2.5 py-0.5 text-xs font-medium">
                                            Processando…
                                        </span>
                                    @elseif ($relatorio->status === 'erro')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 px-2.5 py-0.5 text-xs font-medium" title="{{ $relatorio->erro }}">
                                            Erro
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 px-2.5 py-0.5 text-xs font-medium">
                                            Aguardando…
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                    {{ $relatorio->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                    {{ $relatorio->expires_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($relatorio->isPronto())
                                        <a href="{{ route('relatorios.download', $relatorio) }}"
                                           class="ui-btn-primary w-full sm:w-auto text-xs py-1.5 px-3">
                                            Baixar PDF
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Auto-refresh quando há relatórios em processamento --}}
            @if ($relatorios->whereIn('status', ['pendente', 'processando'])->isNotEmpty())
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500 text-center">
                    Página será atualizada automaticamente a cada 15 segundos enquanto há relatórios em processamento.
                </p>
                <script>
                    setTimeout(() => window.location.reload(), 15000);
                </script>
            @endif
        @endif
    </div>
</x-app-layout>
