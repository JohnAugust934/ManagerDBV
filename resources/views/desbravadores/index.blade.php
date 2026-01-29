<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestão de Desbravadores
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">

                <div class="flex bg-gray-100 dark:bg-gray-700 p-1 rounded-lg">
                    <a href="{{ route('desbravadores.index', ['status' => 'ativos']) }}"
                        class="px-4 py-2 text-sm font-bold rounded-md transition {{ $status == 'ativos' ? 'bg-white dark:bg-gray-600 shadow text-blue-600 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                        Ativos
                    </a>
                    <a href="{{ route('desbravadores.index', ['status' => 'inativos']) }}"
                        class="px-4 py-2 text-sm font-bold rounded-md transition {{ $status == 'inativos' ? 'bg-white dark:bg-gray-600 shadow text-blue-600 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                        Inativos
                    </a>
                    <a href="{{ route('desbravadores.index', ['status' => 'todos']) }}"
                        class="px-4 py-2 text-sm font-bold rounded-md transition {{ $status == 'todos' ? 'bg-white dark:bg-gray-600 shadow text-blue-600 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                        Todos
                    </a>
                </div>

                <a href="{{ route('desbravadores.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Novo Desbravador
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($desbravadores->isEmpty())
                    <div class="text-center py-10 text-gray-500">
                        Nenhum desbravador encontrado neste filtro.
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Nome</th>
                                    <th class="px-6 py-3">Unidade</th>
                                    <th class="px-6 py-3">Classe</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($desbravadores as $dbv)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                    <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap dark:text-white">
                                        <a href="{{ route('desbravadores.show', $dbv) }}" class="hover:text-blue-600 hover:underline flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                                {{ substr($dbv->nome, 0, 1) }}
                                            </div>
                                            {{ $dbv->nome }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">{{ $dbv->unidade->nome ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $dbv->classe_atual }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($dbv->ativo)
                                        <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-green-200">Ativo</span>
                                        @else
                                        <span class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-red-200">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('desbravadores.show', $dbv) }}" class="text-gray-400 hover:text-blue-600 font-bold text-xs uppercase">Ver Perfil &rarr;</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>