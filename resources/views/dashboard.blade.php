<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Visão Geral e Estatísticas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-blue-500 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Membros Ativos</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $totalMembros }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-green-500 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Saldo em Caixa</p>
                        <p class="text-3xl font-bold text-green-600">R$ {{ number_format($saldoAtual, 2, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full text-green-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-pink-500 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Aniversariantes ({{ date('M') }})</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $aniversariantes->count() }}</p>
                    </div>
                    <div class="p-3 bg-pink-100 rounded-full text-pink-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            Ranking das Unidades
                        </h3>
                        <a href="{{ route('frequencia.create') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase">Nova Chamada &rarr;</a>
                    </div>

                    @if(count($ranking) > 0)
                    <div class="space-y-4">
                        @foreach($ranking as $index => $unidade)
                        <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border {{ $index === 0 ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200' }}">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full font-bold {{ $index === 0 ? 'bg-yellow-200 text-yellow-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex justify-between">
                                    <span class="font-bold text-gray-800 dark:text-gray-100">{{ $unidade['nome'] }}</span>
                                    <span class="text-sm font-semibold {{ $index === 0 ? 'text-yellow-700' : 'text-gray-600' }}">{{ $unidade['pontos'] }} pts</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2 dark:bg-gray-600">
                                    @php
                                    $maxPontos = $ranking->first()['pontos'] > 0 ? $ranking->first()['pontos'] : 1;
                                    $percent = ($unidade['pontos'] / $maxPontos) * 100;
                                    @endphp
                                    <div class="h-2 rounded-full {{ $index === 0 ? 'bg-yellow-400' : 'bg-blue-500' }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">Nenhuma pontuação registrada.</p>
                    @endif
                </div>

                <div class="space-y-6">

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Aniversariantes
                        </h3>
                        @if($aniversariantes->count() > 0)
                        <ul class="space-y-3">
                            @foreach($aniversariantes as $bday)
                            <li class="flex justify-between items-center text-sm border-b border-gray-100 pb-2 last:border-0">
                                <span class="text-gray-700 dark:text-gray-300">{{ $bday->nome }}</span>
                                <span class="font-bold text-pink-600 bg-pink-50 px-2 py-1 rounded text-xs">Dia {{ $bday->data_nascimento->format('d') }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-sm text-gray-500 italic">Nenhum aniversariante neste mês.</p>
                        @endif
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Frequência (Últimas 4)
                        </h3>

                        @if(!empty($graficoFrequencia))
                        <div class="flex items-end justify-between gap-2 h-48"> @foreach($graficoFrequencia as $dado)
                            <div class="flex flex-col items-center w-full group">

                                <div class="mb-1 text-center">
                                    <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300 block leading-tight">
                                        {{ $dado['presentes'] }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase">DBV</span>
                                </div>

                                <div class="relative w-full h-32 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-end justify-center overflow-hidden">

                                    <div class="w-full mx-1 bg-indigo-500 rounded-t flex items-center justify-center transition-all duration-1000 ease-out shadow-sm"
                                        style="height: {{ $dado['percentual'] }}%; min-height: 15%;">

                                        <span class="text-[10px] font-bold text-white drop-shadow-md">
                                            {{ $dado['percentual'] }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-2 text-center">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">{{ $dado['data'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="h-32 flex items-center justify-center border-2 border-dashed border-gray-200 rounded-lg">
                            <p class="text-sm text-gray-400">Nenhuma reunião registrada recentemente.</p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('desbravadores.index') }}" class="flex items-center p-4 bg-white hover:bg-gray-50 rounded-lg shadow-sm border border-gray-200 transition">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-3"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg></div>
                    <span class="font-bold text-gray-700">Membros</span>
                </a>
                <a href="{{ route('caixa.index') }}" class="flex items-center p-4 bg-white hover:bg-gray-50 rounded-lg shadow-sm border border-gray-200 transition">
                    <div class="p-2 bg-green-100 text-green-600 rounded-lg mr-3"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg></div>
                    <span class="font-bold text-gray-700">Financeiro</span>
                </a>
                <a href="{{ route('unidades.index') }}" class="flex items-center p-4 bg-white hover:bg-gray-50 rounded-lg shadow-sm border border-gray-200 transition">
                    <div class="p-2 bg-red-100 text-red-600 rounded-lg mr-3"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg></div>
                    <span class="font-bold text-gray-700">Unidades</span>
                </a>
                <a href="{{ route('patrimonio.index') }}" class="flex items-center p-4 bg-white hover:bg-gray-50 rounded-lg shadow-sm border border-gray-200 transition">
                    <div class="p-2 bg-purple-100 text-purple-600 rounded-lg mr-3"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg></div>
                    <span class="font-bold text-gray-700">Patrimônio</span>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>