<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Controle de Patrimônio
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-indigo-100 p-4 rounded-lg shadow border-l-4 border-indigo-500">
                    <h3 class="text-indigo-800 text-sm font-bold uppercase">Total de Itens</h3>
                    <p class="text-2xl font-bold text-indigo-900">{{ $totalItens }} unidades</p>
                </div>
                <div class="bg-emerald-100 p-4 rounded-lg shadow border-l-4 border-emerald-500">
                    <h3 class="text-emerald-800 text-sm font-bold uppercase">Valor Estimado</h3>
                    <p class="text-2xl font-bold text-emerald-900">R$ {{ number_format($valorTotal, 2, ',', '.') }}</p>
                </div>
                <div class="bg-orange-100 p-4 rounded-lg shadow border-l-4 border-orange-500">
                    <h3 class="text-orange-800 text-sm font-bold uppercase">Atenção (Ruim/Inservível)</h3>
                    <p class="text-2xl font-bold text-orange-900">{{ $itensRuins }} registros</p>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('patrimonio.create') }}" class="bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 px-4 py-2 rounded-md font-bold hover:opacity-90">
                    + Adicionar Bem
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Item</th>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Local</th>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itens as $item)
                            <tr class="border-b border-gray-200">
                                <td class="px-5 py-5 bg-white text-sm">
                                    <p class="font-bold">{{ $item->item }}</p>
                                    <p class="text-xs text-gray-500">Val: R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}</p>
                                </td>
                                <td class="px-5 py-5 bg-white text-sm">{{ $item->quantidade }}</td>
                                <td class="px-5 py-5 bg-white text-sm">
                                    @php
                                    $cor = match($item->estado_conservacao) {
                                    'Novo' => 'bg-green-100 text-green-800',
                                    'Bom' => 'bg-blue-100 text-blue-800',
                                    'Regular' => 'bg-yellow-100 text-yellow-800',
                                    'Ruim' => 'bg-orange-100 text-orange-800',
                                    'Inservível' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100'
                                    };
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $cor }}">{{ $item->estado_conservacao }}</span>
                                </td>
                                <td class="px-5 py-5 bg-white text-sm">{{ $item->local_armazenamento ?? '-' }}</td>
                                <td class="px-5 py-5 bg-white text-sm">
                                    <form action="{{ route('patrimonio.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Baixar este item do patrimônio?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline text-xs font-bold">BAIXAR</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($itens->isEmpty())
                    <p class="text-center text-gray-500 mt-4">Nenhum item cadastrado.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>