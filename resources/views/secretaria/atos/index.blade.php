<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                {{ __('Atos Oficiais') }}
            </h2>

            <a href="{{ route('atos.create') }}"
                class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-dbv-red border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none transition shadow-md shrink-0">
                Novo Ato
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="md:hidden px-4">
            <a href="{{ route('atos.create') }}"
                class="w-full flex items-center justify-center px-4 py-3 bg-dbv-red border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-red-700 shadow-md transition">
                Novo Ato
            </a>
        </div>

        <div class="px-4 md:px-0">
            @if ($atos->isEmpty())
                <div
                    class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700">
                    <p class="text-gray-500 dark:text-gray-400">Nenhum ato oficial registrado.</p>
                </div>
            @else
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                            <thead class="bg-gray-50 dark:bg-slate-700/50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Número</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tipo</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Descrição</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Data</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                                @foreach ($atos as $ato)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-gray-900 dark:text-white">
                                            #{{ $ato->numero }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $ato->tipo }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            {{ Str::limit($ato->descricao, 50) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($ato->data)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
