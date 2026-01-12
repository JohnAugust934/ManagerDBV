<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Fluxo de Caixa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 {{ $saldoAtual >= 0 ? 'border-green-500' : 'border-red-500' }}">
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Saldo Atual</h3>
                    <p class="text-3xl font-bold {{ $saldoAtual >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                    </p>
                </div>

                <div class="md:col-span-2 flex items-center justify-end gap-2">
                    <a href="{{ route('relatorios.financeiro') }}" target="_blank" class="inline-flex items-center px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded-lg shadow transition">
                        Imprimir Relatório
                    </a>
                    <a href="{{ route('caixa.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow transition">
                        Nova Movimentação
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Data</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descrição</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Categoria</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lancamentos as $item)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $item->data_movimentacao->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $item->descricao }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $item->categoria ?? 'Geral' }}
                                    </span>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    @if($item->tipo == 'entrada')
                                    <span class="text-green-600 font-bold flex items-center">
                                        Entrada
                                    </span>
                                    @else
                                    <span class="text-red-600 font-bold flex items-center">
                                        Saída
                                    </span>
                                    @endif
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-mono font-bold">
                                    R$ {{ number_format($item->valor, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($lancamentos->isEmpty())
                    <p class="mt-4 text-center text-gray-500">Nenhum lançamento no caixa.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>