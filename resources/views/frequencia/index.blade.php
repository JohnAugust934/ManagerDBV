<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            📅 Histórico de Frequência
        </h2>
    </x-slot>

    <div class="py-6 space-y-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('frequencia.index') }}" method="GET"
                    class="flex flex-col md:flex-row items-end gap-4">

                    <div class="w-full md:w-1/4">
                        <x-input-label for="mes" :value="__('Mês')" />
                        <select name="mes" id="mes"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-1/4">
                        <x-input-label for="ano" :value="__('Ano')" />
                        <select name="ano" id="ano"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach (range(date('Y'), date('Y') - 5) as $y)
                                <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-auto">
                        <x-primary-button class="h-[42px]">
                            {{ __('Filtrar') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700">

                @if ($datasReunioes->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500 text-lg">Nenhuma chamada registrada para este período.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400 sticky top-0">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3 min-w-[200px] z-10 sticky left-0 bg-gray-100 dark:bg-gray-700 border-r dark:border-gray-600">
                                        Desbravador</th>
                                    <th scope="col" class="px-4 py-3">Unidade</th>
                                    @foreach ($datasReunioes as $data)
                                        <th scope="col"
                                            class="px-2 py-3 text-center border-l dark:border-gray-600 min-w-[60px]">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-lg font-bold">{{ \Carbon\Carbon::parse($data)->format('d') }}</span>
                                                <span
                                                    class="text-[10px]">{{ \Carbon\Carbon::parse($data)->locale('pt_BR')->shortDayName }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                    <th scope="col"
                                        class="px-4 py-3 text-center border-l dark:border-gray-600 bg-blue-50 dark:bg-blue-900/20 text-dbv-blue dark:text-blue-300">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($desbravadores as $dbv)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        {{-- Coluna Fixa do Nome --}}
                                        <td
                                            class="px-4 py-3 font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-800 border-r dark:border-gray-600 shadow-sm z-10">
                                            {{ $dbv->nome }}
                                        </td>

                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $dbv->unidade->nome ?? '-' }}
                                        </td>

                                        @php $presencasTotal = 0; @endphp

                                        {{-- Colunas de Datas --}}
                                        @foreach ($datasReunioes as $data)
                                            @php
                                                // Procura se tem registro nessa data específica
                                                $registro = $dbv->frequencias->first(function ($f) use ($data) {
                                                    return $f->data->format('Y-m-d') === $data;
                                                });
                                                if ($registro && $registro->presente) {
                                                    $presencasTotal++;
                                                }
                                            @endphp

                                            <td
                                                class="px-2 py-3 text-center border-l dark:border-gray-700 border-dashed">
                                                @if ($registro)
                                                    @if ($registro->presente)
                                                        <span
                                                            class="inline-flex items-center justify-center w-6 h-6 bg-green-100 text-green-700 rounded-full"
                                                            title="Presente">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center justify-center w-6 h-6 bg-red-100 text-red-700 rounded-full"
                                                            title="Faltou">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-300 text-xl">-</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Coluna Total --}}
                                        <td
                                            class="px-4 py-3 text-center font-bold text-gray-700 dark:text-gray-300 border-l dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                                            @if ($datasReunioes->count() > 0)
                                                {{ round(($presencasTotal / $datasReunioes->count()) * 100) }}%
                                            @else
                                                0%
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="text-right text-xs text-gray-500 mt-2">
                * A porcentagem é calculada baseada apenas nas reuniões ocorridas.
            </div>
        </div>
    </div>
</x-app-layout>
