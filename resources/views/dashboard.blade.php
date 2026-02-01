<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Visão Geral
        </h2>
    </x-slot>

    <div class="space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">

            <div
                class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 relative overflow-hidden group">
                <div class="absolute right-0 top-0 h-full w-2 bg-dbv-blue"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Membros Ativos</p>
                        <h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ $totalMembros }}</h3>
                    </div>
                    <div
                        class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-dbv-blue dark:text-blue-400 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 relative overflow-hidden group">
                <div class="absolute right-0 top-0 h-full w-2 bg-green-500"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Saldo em Caixa</p>
                        <h3 class="text-3xl font-extrabold text-green-600 dark:text-green-400">
                            R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                        </h3>
                    </div>
                    <div
                        class="p-3 bg-green-50 dark:bg-green-900/30 rounded-xl text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 relative overflow-hidden group">
                <div class="absolute right-0 top-0 h-full w-2 bg-dbv-red"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Aniversários ({{ date('M') }})
                        </p>
                        <h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">
                            {{ $aniversariantes->count() }}</h3>
                    </div>
                    <div
                        class="p-3 bg-red-50 dark:bg-red-900/30 rounded-xl text-dbv-red dark:text-red-400 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            <a href="{{ route('desbravadores.index') }}"
                class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 hover:border-dbv-blue dark:hover:border-blue-500 transition group cursor-pointer">
                <div
                    class="p-2 bg-blue-50 dark:bg-blue-900/40 rounded-full text-dbv-blue dark:text-blue-300 mb-2 group-hover:bg-blue-100 dark:group-hover:bg-blue-800 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                        </path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Membros</span>
            </a>

            <a href="{{ route('caixa.index') }}"
                class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 hover:border-green-500 dark:hover:border-green-500 transition group cursor-pointer">
                <div
                    class="p-2 bg-green-50 dark:bg-green-900/40 rounded-full text-green-600 dark:text-green-300 mb-2 group-hover:bg-green-100 dark:group-hover:bg-green-800 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Financeiro</span>
            </a>

            <a href="{{ route('unidades.index') }}"
                class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 hover:border-dbv-yellow dark:hover:border-yellow-500 transition group cursor-pointer">
                <div
                    class="p-2 bg-yellow-50 dark:bg-yellow-900/40 rounded-full text-yellow-600 dark:text-yellow-300 mb-2 group-hover:bg-yellow-100 dark:group-hover:bg-yellow-800 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Unidades</span>
            </a>

            <a href="{{ route('patrimonio.index') }}"
                class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 hover:border-purple-500 dark:hover:border-purple-500 transition group cursor-pointer">
                <div
                    class="p-2 bg-purple-50 dark:bg-purple-900/40 rounded-full text-purple-600 dark:text-purple-300 mb-2 group-hover:bg-purple-100 dark:group-hover:bg-purple-800 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Patrimônio</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div
                class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <svg class="w-5 h-5 text-dbv-yellow" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Ranking das Unidades
                    </h3>
                    <a href="{{ route('frequencia.create') }}"
                        class="text-xs font-bold text-dbv-blue dark:text-blue-400 hover:underline uppercase tracking-wide">
                        + Nova Pontuação
                    </a>
                </div>

                @if (count($ranking) > 0)
                    <div class="space-y-4">
                        @foreach ($ranking as $index => $unidade)
                            <div class="relative group">
                                <div
                                    class="flex items-center p-4 rounded-xl border transition-all duration-300 
                                {{ $index === 0
                                    ? 'bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-700/50 shadow-sm'
                                    : 'bg-gray-50 dark:bg-slate-700/50 border-transparent hover:border-gray-200 dark:hover:border-slate-600' }}">

                                    <div
                                        class="w-10 h-10 shrink-0 flex items-center justify-center rounded-full font-bold text-lg mr-4
                                    {{ $index === 0
                                        ? 'bg-gradient-to-br from-yellow-300 to-yellow-500 text-white shadow-md'
                                        : ($index === 1
                                            ? 'bg-gray-300 text-gray-700'
                                            : ($index === 2
                                                ? 'bg-orange-300 text-orange-800'
                                                : 'bg-gray-200 dark:bg-slate-600 text-gray-500 dark:text-gray-300')) }}">
                                        {{ $index + 1 }}º
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-end mb-2">
                                            <span
                                                class="font-bold text-gray-800 dark:text-gray-100 truncate text-base">{{ $unidade['nome'] }}</span>
                                            <span
                                                class="font-mono font-bold {{ $index === 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400' }}">
                                                {{ $unidade['pontos'] }} <span
                                                    class="text-xs font-normal opacity-70">pts</span>
                                            </span>
                                        </div>

                                        <div
                                            class="w-full bg-gray-200 dark:bg-slate-600 rounded-full h-2.5 overflow-hidden">
                                            @php
                                                $maxPontos =
                                                    $ranking->first()['pontos'] > 0 ? $ranking->first()['pontos'] : 1;
                                                $percent = ($unidade['pontos'] / $maxPontos) * 100;
                                            @endphp
                                            <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $index === 0 ? 'bg-yellow-400' : 'bg-dbv-blue dark:bg-blue-500' }}"
                                                style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 flex flex-col items-center">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">Nenhuma pontuação registrada ainda.</p>
                        <a href="{{ route('frequencia.create') }}"
                            class="mt-2 text-sm font-bold text-dbv-blue hover:underline">Começar agora</a>
                    </div>
                @endif
            </div>

            <div class="space-y-6">

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z">
                            </path>
                        </svg>
                        Aniversariantes
                    </h3>
                    @if ($aniversariantes->count() > 0)
                        <ul class="space-y-3">
                            @foreach ($aniversariantes as $bday)
                                <li
                                    class="flex justify-between items-center text-sm p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                                    <span
                                        class="text-gray-700 dark:text-gray-300 font-medium">{{ $bday->nome }}</span>
                                    <span
                                        class="font-bold text-pink-600 bg-pink-100 dark:bg-pink-900/30 px-2 py-1 rounded text-xs">
                                        Dia {{ $bday->data_nascimento->format('d') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-4 px-4 bg-gray-50 dark:bg-slate-700/30 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">Nenhum aniversariante neste mês.
                            </p>
                        </div>
                    @endif
                </div>

                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-dbv-blue dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        Frequência (Últimas 4)
                    </h3>

                    @if (!empty($graficoFrequencia))
                        <div class="flex items-end justify-between gap-2 h-40 pt-4">
                            @foreach ($graficoFrequencia as $dado)
                                <div class="flex flex-col items-center w-full group relative">

                                    <div
                                        class="absolute -top-8 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-800 text-white text-xs rounded py-1 px-2 mb-1">
                                        {{ $dado['presentes'] }} presentes
                                    </div>

                                    <div
                                        class="relative w-full bg-gray-100 dark:bg-slate-700 rounded-t-lg flex items-end justify-center overflow-hidden h-32">
                                        <div class="w-full mx-1 bg-dbv-blue dark:bg-blue-500 rounded-t flex items-center justify-center transition-all duration-1000 ease-out shadow-sm group-hover:bg-blue-700 dark:group-hover:bg-blue-400"
                                            style="height: {{ $dado['percentual'] }}%; min-height: 15%;">
                                            <span class="text-[10px] font-bold text-white drop-shadow-md">
                                                {{ round($dado['percentual']) }}%
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-2 text-center border-t border-gray-200 dark:border-slate-600 w-full pt-1">
                                        <span
                                            class="text-[10px] font-bold text-gray-500 dark:text-gray-400 block uppercase">
                                            {{ \Carbon\Carbon::createFromFormat('d/m', $dado['data'])->format('d M') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="h-32 flex items-center justify-center border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-lg bg-gray-50 dark:bg-slate-700/20">
                            <p class="text-sm text-gray-400">Sem dados recentes.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
