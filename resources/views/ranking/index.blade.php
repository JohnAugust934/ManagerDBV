<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            🏆 {{ $titulo }}
        </h2>
    </x-slot>

    <div class="ui-page space-y-8">

        @if ($top3->count() > 0)
            <div class="flex justify-center items-end gap-2 md:gap-8 min-h-[320px] px-2 mb-12">

                {{-- 2º Lugar --}}
                @if (isset($top3[1]))
                    <div class="flex flex-col items-center group w-1/3 max-w-[180px] animate-slide-in"
                        style="animation-delay: 100ms">
                        {{-- Dados do 2º Lugar --}}
                        <div class="mb-4 text-center z-20 relative">
                            <span
                                class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">2º
                                Lugar</span>

                            {{-- Nome truncado para não quebrar no mobile --}}
                            <span
                                class="block text-sm md:text-lg font-bold text-gray-800 dark:text-gray-200 w-24 md:w-full truncate overflow-hidden text-ellipsis px-1">
                                {{ $top3[1]->nome }}
                            </span>

                            {{-- Subtexto (Unidade ou Membros) --}}
                            <span class="block text-[10px] md:text-xs text-gray-400 mb-1">{{ $top3[1]->subtexto }}</span>

                            {{-- PONTOS (Badge) --}}
                            <span
                                class="inline-block px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs font-mono font-bold shadow-sm">
                                {{ $top3[1]->pontos }} pts
                            </span>
                        </div>

                        {{-- Barra do Pódio --}}
                        <div
                            class="w-full bg-gradient-to-t from-gray-400 to-gray-200 dark:from-slate-700 dark:to-slate-600 rounded-t-lg shadow-lg relative h-36 md:h-48 flex items-end justify-center border-t-4 border-gray-400 transition-all duration-300 group-hover:h-40 md:group-hover:h-52 z-10">
                            <div
                                class="absolute -top-5 w-10 h-10 md:w-12 md:h-12 bg-gray-100 dark:bg-slate-800 border-4 border-gray-400 rounded-full flex items-center justify-center font-bold text-gray-600 dark:text-gray-300 shadow-sm z-30">
                                2
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 1º Lugar --}}
                <div class="flex flex-col items-center group w-1/3 max-w-[200px] animate-slide-in z-30">
                    <div class="mb-4 text-center z-20 relative">
                        <div class="flex justify-center mb-1">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-yellow-400 drop-shadow-sm animate-bounce"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>

                        <span
                            class="block text-base md:text-xl font-black text-dbv-blue dark:text-white w-28 md:w-full truncate overflow-hidden text-ellipsis px-1">
                            {{ $top3[0]->nome }}
                        </span>

                        <span class="block text-[10px] md:text-xs text-blue-300 mb-1">{{ $top3[0]->subtexto }}</span>

                        <span
                            class="inline-block px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400 rounded-full text-sm font-mono font-bold border border-yellow-200 dark:border-yellow-700 shadow-md transform scale-110">
                            {{ $top3[0]->pontos }} pts
                        </span>
                    </div>

                    <div
                        class="w-full bg-gradient-to-t from-yellow-300 to-yellow-100 dark:from-yellow-600 dark:to-yellow-500 rounded-t-lg shadow-xl relative h-48 md:h-60 flex items-end justify-center border-t-4 border-yellow-400 transition-all duration-300 group-hover:h-52 md:group-hover:h-64 z-10">
                        <div
                            class="absolute -top-6 w-14 h-14 md:w-16 md:h-16 bg-yellow-200 dark:bg-yellow-900 border-4 border-yellow-400 rounded-full flex items-center justify-center font-black text-xl md:text-2xl text-yellow-800 dark:text-yellow-400 shadow-md z-30">
                            1
                        </div>
                    </div>
                </div>

                {{-- 3º Lugar --}}
                @if (isset($top3[2]))
                    <div class="flex flex-col items-center group w-1/3 max-w-[180px] animate-slide-in"
                        style="animation-delay: 200ms">
                        <div class="mb-4 text-center z-20 relative">
                            <span
                                class="block text-xs font-bold text-orange-800/60 dark:text-orange-300/60 uppercase tracking-widest mb-1">3º
                                Lugar</span>

                            <span
                                class="block text-sm md:text-lg font-bold text-gray-800 dark:text-gray-200 w-24 md:w-full truncate overflow-hidden text-ellipsis px-1">
                                {{ $top3[2]->nome }}
                            </span>

                            <span
                                class="block text-[10px] md:text-xs text-gray-400 mb-1">{{ $top3[2]->subtexto }}</span>

                            <span
                                class="inline-block px-2 py-1 bg-orange-100 dark:bg-orange-900/20 text-orange-800 dark:text-orange-300 rounded text-xs font-mono font-bold shadow-sm">
                                {{ $top3[2]->pontos }} pts
                            </span>
                        </div>

                        <div
                            class="w-full bg-gradient-to-t from-orange-300 to-orange-100 dark:from-orange-800 dark:to-orange-700 rounded-t-lg shadow-lg relative h-28 md:h-36 flex items-end justify-center border-t-4 border-orange-400 transition-all duration-300 group-hover:h-32 md:group-hover:h-40 z-10">
                            <div
                                class="absolute -top-5 w-10 h-10 md:w-12 md:h-12 bg-orange-100 dark:bg-orange-900 border-4 border-orange-400 rounded-full flex items-center justify-center font-bold text-orange-800 dark:text-orange-300 shadow-sm z-30">
                                3
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        @else
            <div class="text-center py-10">
                <p class="text-gray-500 text-lg">Ainda não há pontuações registradas para gerar o ranking.</p>
            </div>
        @endif

        <div class="max-w-4xl mx-auto px-2 sm:px-6">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200">Classificação Geral</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs">
                            <tr>
                                <th class="px-4 md:px-6 py-3 font-semibold text-center w-12">Pos</th>
                                <th class="px-4 md:px-6 py-3 font-semibold">Nome</th>
                                <th class="hidden md:table-cell px-6 py-3 font-semibold text-center">Uniforme</th>
                                <th class="hidden md:table-cell px-6 py-3 font-semibold text-center">Bíblia</th>
                                <th
                                    class="px-4 md:px-6 py-3 font-semibold text-center text-dbv-blue dark:text-blue-400">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($data as $index => $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                                    <td class="px-4 md:px-6 py-4 text-center font-bold text-gray-400">
                                        @if ($index == 0)
                                            🥇
                                        @elseif($index == 1)
                                            🥈
                                        @elseif($index == 2)
                                            🥉
                                        @else
                                            {{ $index + 1 }}º
                                        @endif
                                    </td>
                                    <td class="px-4 md:px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-1.5 h-8 rounded-full mr-3 hidden md:block"
                                                style="background-color: {{ $item->cor }}"></div>
                                            <div>
                                                <p
                                                    class="font-bold text-gray-800 dark:text-gray-100 text-sm md:text-base">
                                                    {{ $item->nome }}</p>
                                                <p class="text-[10px] md:text-xs text-gray-400">{{ $item->subtexto }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="hidden md:table-cell px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                                        {{ $item->detalhes['uniforme'] }}
                                    </td>
                                    <td
                                        class="hidden md:table-cell px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                                        {{ $item->detalhes['biblia'] }}
                                    </td>
                                    <td class="px-4 md:px-6 py-4 text-center">
                                        <span
                                            class="inline-block min-w-[2.5rem] px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-dbv-blue dark:text-blue-300 font-bold border border-blue-100 dark:border-blue-800 text-xs md:text-sm">
                                            {{ $item->pontos }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>


