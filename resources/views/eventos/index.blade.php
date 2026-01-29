<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Eventos e Acampamentos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex justify-end bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                <a href="{{ route('eventos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Criar Novo Evento
                </a>
            </div>

            @if($eventos->isEmpty())
            <div class="bg-white dark:bg-gray-800 p-12 rounded-lg shadow text-center flex flex-col items-center">
                <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-full mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">Nenhum evento programado.</p>
                <p class="text-sm text-gray-400">Comece criando o primeiro acampamento ou saída do clube.</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($eventos as $evento)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden flex flex-col border border-gray-100 dark:border-gray-700 hover:shadow-lg transition">

                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $evento->nome }}
                            </h3>
                            @if($evento->valor > 0)
                            <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-full border border-green-200">
                                R$ {{ number_format($evento->valor, 2, ',', '.') }}
                            </span>
                            @else
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded-full border border-blue-200">
                                Gratuito
                            </span>
                            @endif
                        </div>

                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $evento->local }}
                        </div>

                        <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-3">
                            {{ $evento->descricao ?? 'Sem descrição informada.' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-between items-center border-t border-gray-100 dark:border-gray-600">
                        <div class="flex items-center text-xs font-bold text-gray-500 uppercase">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $evento->data_inicio->format('d/m/Y') }}
                        </div>
                        <a href="{{ route('eventos.show', $evento->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold uppercase flex items-center hover:underline">
                            Gerenciar
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</x-app-layout>