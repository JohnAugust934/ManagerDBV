<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Livro de Atas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-end mb-4">
                <a href="{{ route('atas.create') }}" class="bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 px-4 py-2 rounded-md font-bold hover:opacity-90">
                    + Nova Ata
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if($atas->isEmpty())
                    <p class="text-center text-gray-500">Nenhuma ata registrada.</p>
                    @else
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($atas as $ata)
                        <a href="{{ route('atas.show', $ata->id) }}" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border hover:shadow-lg transition">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ $ata->tipo }}
                                </span>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    {{ $ata->data_reuniao->format('d/m/Y') }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold mb-2 truncate">Reunião #{{ $ata->id }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-3">
                                {{ $ata->conteudo }}
                            </p>
                            <div class="mt-4 text-xs text-gray-500">
                                Secretário: {{ $ata->secretario_responsavel ?? 'Não informado' }}
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>