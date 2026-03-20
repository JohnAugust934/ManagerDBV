<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                Atas de Reunião
            </h2>

            <a href="{{ route('atas.create') }}"
                class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 shadow-md shrink-0">
                Nova Ata
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="md:hidden px-4">
            <a href="{{ route('atas.create') }}"
                class="w-full flex items-center justify-center px-4 py-3 bg-dbv-blue border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-800 shadow-md transition">
                Nova Ata
            </a>
        </div>

        <div class="px-4 md:px-0">
            @if ($atas->isEmpty())
                <div class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700">
                    <p class="text-gray-500 dark:text-gray-400">Nenhuma ata registrada.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($atas as $ata)
                        <div class="group bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-md hover:border-dbv-blue dark:hover:border-blue-500 transition relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gray-200 group-hover:bg-dbv-blue transition-colors"></div>

                            <div class="pl-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div>
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                            {{ $ata->data_reuniao?->format('d/m/Y') }}
                                        </span>
                                        <h3 class="font-bold text-gray-800 dark:text-white mt-2">
                                            {{ $ata->titulo ?? 'Reunião Regular' }}
                                        </h3>
                                    </div>
                                    <span class="text-[10px] bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">
                                        {{ optional($ata->hora_inicio)->format('H:i') }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-3">
                                    {{ Str::limit(strip_tags($ata->conteudo), 110) }}
                                </p>

                                <div class="mt-4 flex items-center gap-2">
                                    <a href="{{ route('atas.show', $ata) }}"
                                        class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-widest bg-blue-50 text-dbv-blue dark:bg-blue-900/20 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40">
                                        Ver
                                    </a>
                                    <a href="{{ route('atas.edit', $ata) }}"
                                        class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-widest bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40">
                                        Editar
                                    </a>
                                    <form action="{{ route('atas.destroy', $ata) }}" method="POST"
                                        onsubmit="return confirm('Excluir esta ata apagará o registro permanentemente. Deseja continuar?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-widest bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $atas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
