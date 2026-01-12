<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Livro de Atos Administrativos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-end mb-4">
                <a href="{{ route('atos.create') }}" class="bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 px-4 py-2 rounded-md font-bold hover:opacity-90">
                    + Emitir Novo Ato
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Descrição</th>
                                <th class="px-5 py-3 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Envolvido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atos as $ato)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-5 py-5 text-sm">{{ $ato->data->format('d/m/Y') }}</td>
                                <td class="px-5 py-5 text-sm">
                                    <span class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs font-bold">{{ $ato->tipo }}</span>
                                </td>
                                <td class="px-5 py-5 text-sm font-bold">{{ $ato->descricao_resumida }}</td>
                                <td class="px-5 py-5 text-sm">
                                    {{ $ato->desbravador->nome ?? '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($atos->isEmpty())
                    <p class="text-center text-gray-500 mt-4">Nenhum ato administrativo registrado.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>