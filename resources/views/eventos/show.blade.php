<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $evento->nome }}
            </h2>
            <a href="{{ route('eventos.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Voltar</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-blue-500">
                    <p class="text-gray-500 text-sm uppercase">Inscritos</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $evento->desbravadores->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-green-500">
                    <p class="text-gray-500 text-sm uppercase">Arrecadado</p>
                    <p class="text-3xl font-bold text-green-600">
                        R$ {{ number_format($evento->desbravadores->where('pivot.pago', true)->count() * $evento->valor, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-yellow-500">
                    <p class="text-gray-500 text-sm uppercase">Custo Individual</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">R$ {{ number_format($evento->valor, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">Lista de Inscrição</h3>
                    </div>

                    @if($evento->desbravadores->isEmpty())
                    <p class="p-6 text-gray-500">Ninguém inscrito ainda.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Nome</th>
                                    <th class="px-6 py-3 text-center">Pagamento</th>
                                    <th class="px-6 py-3 text-center">Autorização</th>
                                    <th class="px-6 py-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($evento->desbravadores as $dbv)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $dbv->nome }}</td>

                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('eventos.status', [$evento->id, $dbv->id]) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="campo" value="pago">
                                            <input type="hidden" name="valor" value="{{ $dbv->pivot->pago ? '0' : '1' }}">
                                            <button type="submit" class="px-2 py-1 rounded text-xs font-bold {{ $dbv->pivot->pago ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $dbv->pivot->pago ? 'PAGO' : 'PENDENTE' }}
                                            </button>
                                        </form>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('eventos.status', [$evento->id, $dbv->id]) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="campo" value="autorizacao_entregue">
                                            <input type="hidden" name="valor" value="{{ $dbv->pivot->autorizacao_entregue ? '0' : '1' }}">
                                            <button type="submit" class="text-2xl" title="Clique para alterar">
                                                {{ $dbv->pivot->autorizacao_entregue ? '✅' : '❌' }}
                                            </button>
                                        </form>
                                    </td>

                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        <a href="{{ route('eventos.autorizacao', [$evento->id, $dbv->id]) }}" target="_blank" class="text-blue-600 hover:text-blue-800" title="Imprimir Autorização">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                        </a>

                                        <form action="{{ route('eventos.remover-inscricao', [$evento->id, $dbv->id]) }}" method="POST" onsubmit="return confirm('Remover inscrição?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Remover Inscrição">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
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

                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 h-fit sticky top-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-900 dark:text-white">Nova Inscrição</h3>
                    <form action="{{ route('eventos.inscrever', $evento->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Desbravador</label>
                            <select name="desbravador_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm" required>
                                <option value="">Selecione...</option>
                                @foreach($naoInscritos as $dbv)
                                <option value="{{ $dbv->id }}">{{ $dbv->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Inscrever
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>