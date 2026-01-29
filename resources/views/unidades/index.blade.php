<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Unidades do Clube
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex justify-end bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                <a href="{{ route('unidades.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nova Unidade
                </a>
            </div>

            @if($unidades->isEmpty())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-10 text-center flex flex-col items-center justify-center">
                <div class="p-4 bg-gray-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <p class="text-gray-500 mb-4">Nenhuma unidade cadastrada ainda.</p>
                <a href="{{ route('unidades.create') }}" class="text-indigo-600 hover:underline font-bold">Crie a primeira unidade agora</a>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($unidades as $unidade)
                <a href="{{ route('unidades.show', $unidade->id) }}" class="block group h-full">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition border-l-4 border-indigo-500 h-full flex flex-col">
                        <div class="p-6 flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 transition">
                                    {{ $unidade->nome }}
                                </h3>
                                <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-2 py-1 rounded-full border border-indigo-100">
                                    {{ $unidade->desbravadores_count }} membros
                                </span>
                            </div>

                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-3">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ $unidade->conselheiro }}
                            </div>

                            @if($unidade->grito_guerra)
                            <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded text-xs italic text-gray-500 dark:text-gray-300 border-l-2 border-gray-300">
                                "{{ Str::limit($unidade->grito_guerra, 60) }}"
                            </div>
                            @endif
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 border-t border-gray-100 dark:border-gray-600 text-right">
                            <span class="text-indigo-600 dark:text-indigo-400 text-xs font-bold uppercase tracking-wider group-hover:underline">Acessar Painel &rarr;</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

        </div>
    </div>
</x-app-layout>