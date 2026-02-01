<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                Ata #{{ $ata->id }}
            </h2>

            <div class="hidden md:flex items-center gap-2 shrink-0">
                <a href="{{ route('atas.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none transition shadow-sm">Voltar</a>
                <button onclick="window.print()"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 dark:bg-slate-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase hover:bg-gray-700">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="grid grid-cols-2 gap-3 md:hidden px-4">
            <a href="{{ route('atas.index') }}"
                class="flex items-center justify-center py-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl font-bold text-sm text-gray-700 dark:text-gray-300 shadow-sm">Voltar</a>
            <button onclick="window.print()"
                class="flex items-center justify-center py-3 bg-gray-800 dark:bg-slate-600 rounded-xl font-bold text-sm text-white shadow-sm">Imprimir</button>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-none md:rounded-2xl shadow-lg border border-gray-200 dark:border-slate-700 p-8 md:p-12 print:shadow-none print:border-none">

                <div class="text-center border-b-2 border-gray-100 dark:border-slate-700 pb-6 mb-6">
                    <h1 class="text-2xl font-serif font-bold text-gray-900 dark:text-white uppercase tracking-wide">Ata
                        de Reunião</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ Auth::user()->club->nome ?? 'Clube de Desbravadores' }}</p>
                </div>

                <div
                    class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 text-sm bg-gray-50 dark:bg-slate-700/50 p-4 rounded-lg">
                    <div>
                        <span class="block font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Data</span>
                        <span
                            class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($ata->data_reuniao)->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="block font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Horário</span>
                        <span
                            class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($ata->hora_inicio)->format('H:i') }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="block font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Local</span>
                        <span class="text-gray-900 dark:text-white">{{ $ata->local }}</span>
                    </div>
                </div>

                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ $ata->titulo }}</h2>

                <div
                    class="prose dark:prose-invert max-w-none text-justify text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line">
                    {{ $ata->conteudo }}
                </div>

                @if ($ata->participantes)
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-slate-700">
                        <span
                            class="block font-bold text-gray-500 dark:text-gray-400 uppercase text-xs mb-2">Participantes</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 italic">{{ $ata->participantes }}</p>
                    </div>
                @endif

                <div class="mt-16 grid grid-cols-2 gap-12 print:block">
                    <div
                        class="border-t border-gray-400 dark:border-slate-500 pt-2 text-center text-xs text-gray-500 dark:text-gray-400">
                        Secretário(a)
                    </div>
                    <div
                        class="border-t border-gray-400 dark:border-slate-500 pt-2 text-center text-xs text-gray-500 dark:text-gray-400">
                        Diretor(a)
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
