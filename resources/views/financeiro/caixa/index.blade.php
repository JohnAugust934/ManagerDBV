<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                {{ __('Fluxo de Caixa') }}
            </h2>

            <a href="{{ route('caixa.create') }}"
                class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 active:bg-blue-900 focus:outline-none transition shadow-md shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                Lançamento
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        <div class="md:hidden px-4">
            <a href="{{ route('caixa.create') }}"
                class="w-full flex items-center justify-center px-4 py-3 bg-dbv-blue border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-800 shadow-md transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Novo Lançamento
            </a>
        </div>

        <div class="px-4 md:px-0">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 relative overflow-hidden">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Saldo
                        Atual</p>
                    <h3
                        class="text-2xl font-extrabold {{ $saldoAtual >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                    </h3>
                    <div class="absolute right-4 top-4 opacity-10">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.15-1.46-3.27-3.4h1.96c.1 1.05 1.18 1.91 2.53 1.91 1.29 0 2.13-.72 2.13-1.71 0-1.12-.57-1.59-1.82-2.16l-.08-.03c-1.3-.53-2.91-1.3-2.91-3.63 0-1.8 1.48-3 3.19-3.37V4h2.67v1.98c1.71.38 3.02 1.61 3.17 3.43h-2.01c-.11-1.04-1.2-1.67-2.3-1.67-1.15 0-1.85.73-1.85 1.58 0 1.1.6 1.58 2.02 2.23l.08.04c1.25.59 2.72 1.3 2.72 3.53 0 2.07-1.56 3.46-3.36 3.8z" />
                        </svg>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Entradas
                        (Total)</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        R$ {{ number_format($entradas, 2, ',', '.') }}
                    </h3>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Saídas
                        (Total)</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        R$ {{ number_format($saidas, 2, ',', '.') }}
                    </h3>
                </div>
            </div>

            @if ($lancamentos->count() > 0)

                <div
                    class="hidden md:block bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Data</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Descrição</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Categoria</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                            @foreach ($lancamentos as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($item->data_movimentacao)->format('d/m/Y') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $item->descricao }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $item->categoria ?? '-' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold {{ $item->tipo == 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $item->tipo == 'entrada' ? '+' : '-' }} R$
                                        {{ number_format($item->valor, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                        {{ $lancamentos->links() }}
                    </div>
                </div>

                <div class="md:hidden space-y-3">
                    @foreach ($lancamentos as $item)
                        <div
                            class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-slate-700 flex justify-between items-center relative overflow-hidden">
                            <div
                                class="absolute left-0 top-0 bottom-0 w-1 {{ $item->tipo == 'entrada' ? 'bg-green-500' : 'bg-red-500' }}">
                            </div>

                            <div class="pl-2">
                                <span
                                    class="text-xs text-gray-400 font-medium">{{ \Carbon\Carbon::parse($item->data_movimentacao)->format('d/m/Y') }}</span>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1">
                                    {{ $item->descricao }}</h4>
                                @if ($item->categoria)
                                    <span
                                        class="text-[10px] bg-gray-100 dark:bg-slate-700 text-gray-500 px-2 py-0.5 rounded-md mt-1 inline-block">{{ $item->categoria }}</span>
                                @endif
                            </div>

                            <div class="text-right">
                                <p
                                    class="text-sm font-bold {{ $item->tipo == 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $item->tipo == 'entrada' ? '+' : '-' }} R$
                                    {{ number_format($item->valor, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        {{ $lancamentos->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">Nenhum lançamento no caixa.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
