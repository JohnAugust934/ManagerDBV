<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalhes da Ata #{{ $ata->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">

                <div class="bg-gray-100 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Ata de Reunião {{ $ata->tipo }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Data: {{ $ata->data_reuniao->format('d/m/Y') }}</p>
                    </div>
                    <a href="{{ route('atas.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
                </div>

                <div class="p-8 text-gray-900 dark:text-gray-100 leading-relaxed space-y-4">

                    @if($ata->participantes)
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                        <span class="font-bold block mb-1">Participantes:</span>
                        {{ $ata->participantes }}
                    </div>
                    @endif

                    <div class="whitespace-pre-wrap text-justify">{{ $ata->conteudo }}</div>

                    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <div class="text-center">
                            <p class="font-bold">{{ $ata->secretario_responsavel }}</p>
                            <p class="text-xs text-gray-500 uppercase">Secretário(a)</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>