<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Progresso: {{ $desbravador->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                    @foreach($classes as $cls)
                    <a href="{{ route('progresso.index', ['desbravador' => $desbravador->id, 'classe_id' => $cls->id]) }}"
                        class="px-4 py-2 rounded-full text-sm font-bold transition 
                           {{ $classeSelecionada->id == $cls->id ? 'bg-indigo-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $cls->nome }}
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 sticky top-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $classeSelecionada->nome }}</h3>
                        <p class="text-sm text-gray-500 mb-6">Requisitos para investidura.</p>

                        @php
                        $total = $classeSelecionada->requisitos->count();
                        $feitos = count(array_intersect($classeSelecionada->requisitos->pluck('id')->toArray(), $cumpridosIds));
                        $porcentagem = $total > 0 ? round(($feitos / $total) * 100) : 0;
                        @endphp

                        <div class="relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                                        Progresso
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold inline-block text-indigo-600">
                                        {{ $porcentagem }}%
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-4 mb-4 text-xs flex rounded bg-indigo-200">
                                <div style="width:{{ $porcentagem }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-500"></div>
                            </div>
                            <p class="text-center text-sm font-bold text-gray-600 dark:text-gray-300">
                                {{ $feitos }} de {{ $total }} requisitos cumpridos
                            </p>
                        </div>

                        <div class="mt-6 border-t pt-4 text-center">
                            <a href="{{ route('desbravadores.show', $desbravador->id) }}" class="text-sm text-gray-500 hover:text-indigo-600 underline">
                                &larr; Voltar ao Perfil
                            </a>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="font-bold text-gray-800 dark:text-white">Lista de Requisitos</h4>
                        </div>

                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($classeSelecionada->requisitos as $req)
                            <li class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-start gap-4">
                                <form action="{{ route('progresso.toggle', $desbravador->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="requisito_id" value="{{ $req->id }}">
                                    <button type="submit"
                                        class="mt-1 w-6 h-6 rounded border flex items-center justify-center transition
                                            {{ in_array($req->id, $cumpridosIds) ? 'bg-green-500 border-green-500 text-white hover:bg-green-600' : 'border-gray-300 bg-white hover:border-indigo-500' }}">
                                        @if(in_array($req->id, $cumpridosIds))
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        @endif
                                    </button>
                                </form>

                                <div class="flex-1">
                                    <span class="text-xs font-bold text-gray-400 block">{{ $req->codigo }} • {{ $req->categoria }}</span>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 {{ in_array($req->id, $cumpridosIds) ? 'line-through text-gray-400' : '' }}">
                                        {{ $req->descricao }}
                                    </p>
                                </div>
                            </li>
                            @endforeach

                            @if($classeSelecionada->requisitos->isEmpty())
                            <li class="p-6 text-center text-gray-500">Nenhum requisito cadastrado para esta classe.</li>
                            @endif
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>