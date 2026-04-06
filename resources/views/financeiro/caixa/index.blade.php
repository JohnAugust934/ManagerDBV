<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Fluxo de Caixa') }}
        </h2>
    </x-slot>

    <div class="ui-page space-y-8">
        <div class="space-y-8">

            {{-- 1. Resumo Financeiro (Widgets) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">

                {{-- Card Saldo --}}
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group transition-all hover:shadow-md">
                    <div class="relative z-10">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Saldo Atual</p>
                        <h3
                            class="text-3xl font-bold {{ $saldoAtual >= 0? 'text-gray-800 dark:text-white' : 'text-red-500' }}">
                            R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                        </h3>
                    </div>
                    {{-- Ícone de Fundo Decorativo --}}
                    <div
                        class="absolute right-4 top-4 text-gray-100 dark:text-gray-700 opacity-50 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 21a9 9 0 100-18 9 9 0 000 18zm0-2a7 7 0 110-14 7 7 0 010 14zm1-11V6h-2v2h-2v2h2v2h-2v2h2v2h2v-2h2v-2h-2v-2h2V8h-2z"
                                opacity="0.3" />
                            <path d="M13.5 8H12v2h1.5a1.5 1.5 0 110 3H12v2h2a3.5 3.5 0 100-7zM10 14h1.5v-2H10v2z" />
                        </svg>
                    </div>
                </div>

                {{-- Card Entradas --}}
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border-l-4 border-green-500 dark:border-green-600 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Entradas</p>
                            <h3 class="text-2xl font-bold text-green-600 dark:text-green-400">
                                + R$ {{ number_format($entradas, 2, ',', '.') }}
                            </h3>
                        </div>
                        <div
                            class="p-3 bg-green-50 dark:bg-green-900/30 rounded-full text-green-600 dark:text-green-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Card Saídas --}}
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border-l-4 border-red-500 dark:border-red-600 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Saídas</p>
                            <h3 class="text-2xl font-bold text-red-600 dark:text-red-400">
                                - R$ {{ number_format($saidas, 2, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-red-50 dark:bg-red-900/30 rounded-full text-red-600 dark:text-red-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Barra de Ação (Botão Principal) --}}
            {{-- No mobile, ocupa largura total. No desktop, alinha à direita. --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="hidden sm:block">
                    {{-- Espaço vazio ou Filtros futuros --}}
                </div>

                <a href="{{ route('caixa.create') }}"
                    class="ui-btn-primary w-full sm:w-auto flex items-center justify-center group">
                    <div class="bg-white/20 p-1 rounded-full group-hover:bg-white/30 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span>Nova Movimentação</span>
                </a>
            </div>

            {{-- 3. Área de Listagem --}}
            <div
                class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                {{-- Cabeçalho da Lista --}}
                <div
                    class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Histórico
                    </h3>
                    <span
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full border border-gray-200 dark:border-gray-600">
                        {{ $lancamentos->total() }} registros
                    </span>
                </div>

                @if ($lancamentos->count() > 0)
                    {{-- Visualização Desktop (Tabela Completa) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Data</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Descrição</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Categoria</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Valor</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($lancamentos as $lancamento)
                                    <tr
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($lancamento->data_movimentacao)->format('d/m/Y') }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $lancamento->descricao }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($lancamento->categoria)
                                                <span
                                                    class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">
                                                    {{ $lancamento->categoria }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">-</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold {{ $lancamento->tipo === 'entrada'? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $lancamento->tipo === 'entrada'? '+' : '-' }} R$
                                            {{ number_format($lancamento->valor, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Visualização Mobile (Lista em Cards Otimizada) --}}
                    <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($lancamentos as $lancamento)
                            <div
                                class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 active:bg-gray-100 transition-colors cursor-default">
                                <div class="flex items-center space-x-4 overflow-hidden">
                                    {{-- Ícone Indicativo (Seta) --}}
                                    <div class="flex-shrink-0">
                                        @if ($lancamento->tipo === 'entrada')
                                            <div
                                                class="w-11 h-11 rounded-full bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                                </svg>
                                            </div>
                                        @else
                                            <div
                                                class="w-11 h-11 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Detalhes --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                            {{ $lancamento->descricao }}
                                        </p>
                                        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1 opacity-70" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($lancamento->data_movimentacao)->format('d/m') }}
                                            </span>
                                            @if ($lancamento->categoria)
                                                <span class="mx-1.5 text-gray-300">•</span>
                                                <span
                                                    class="truncate max-w-[120px] bg-gray-100 dark:bg-gray-700 px-1.5 rounded text-[10px] uppercase tracking-wide">
                                                    {{ $lancamento->categoria }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Valor --}}
                                <div class="flex-shrink-0 text-right pl-3">
                                    <span
                                        class="block text-sm font-bold {{ $lancamento->tipo === 'entrada'? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $lancamento->tipo === 'entrada'? '+' : '-' }} R$
                                        {{ number_format($lancamento->valor, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Paginação --}}
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                        {{ $lancamentos->links() }}
                    </div>
                @else
                    <div class="p-6">
                        <x-empty-state
                            title="O caixa est? vazio"
                            description="Ainda não ha lancamentos registrados para este clube. Que tal comecar agora?">
                            <x-slot:action>
                                <a href="{{ route('caixa.create') }}" class="ui-btn-primary">Registrar Primeiro Lancamento</a>
                            </x-slot:action>
                        </x-empty-state>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>



