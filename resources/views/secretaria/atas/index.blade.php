<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                {{ __('Atas de Reunião') }}
            </h2>

            <a href="{{ route('atas.create') }}"
                class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150 shadow-md shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Ata
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        <div class="md:hidden px-4">
            <a href="{{ route('atas.create') }}"
                class="w-full flex items-center justify-center px-4 py-3 bg-dbv-blue border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-800 shadow-md transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Ata
            </a>
        </div>

        <div class="px-4 md:px-0">
            @if ($atas->isEmpty())
                <div
                    class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700">
                    <p class="text-gray-500 dark:text-gray-400">Nenhuma ata registrada.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($atas as $ata)
                        <a href="{{ route('atas.show', $ata) }}"
                            class="group bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-md hover:border-dbv-blue dark:hover:border-blue-500 transition relative overflow-hidden">

                            <div
                                class="absolute left-0 top-0 bottom-0 w-1.5 bg-gray-200 group-hover:bg-dbv-blue transition-colors">
                            </div>

                            <div class="pl-3">
                                <div class="flex justify-between items-start">
                                    <span
                                        class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($ata->data_reuniao)->format('d/m/Y') }}
                                    </span>
                                    <span
                                        class="text-[10px] bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">
                                        {{ \Carbon\Carbon::parse($ata->hora_inicio)->format('H:i') }}
                                    </span>
                                </div>

                                <h3
                                    class="font-bold text-gray-800 dark:text-white mt-2 group-hover:text-dbv-blue dark:group-hover:text-blue-400 transition-colors">
                                    {{ $ata->titulo ?? 'Reunião Regular' }}
                                </h3>

                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-3">
                                    {{ Str::limit(strip_tags($ata->conteudo), 100) }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $atas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
