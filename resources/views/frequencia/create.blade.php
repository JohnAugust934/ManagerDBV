<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-200 leading-tight">
            {{ __('Registro de Chamada e Pontuação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('frequencia.store') }}" method="POST">
                        @csrf

                        <div class="mb-6 max-w-xs">
                            <x-input-label for="data" :value="__('Data da Reunião')" />
                            <input type="date" name="data" id="data" value="{{ date('Y-m-d') }}" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        </div>

                        @if ($unidades->isEmpty())
                            <div class="text-center py-10 text-gray-500">
                                Nenhuma unidade ou desbravador disponível para chamada.
                            </div>
                        @else
                            @foreach ($unidades as $unidade)
                                @if ($unidade->desbravadores->count() > 0)
                                    <div
                                        class="mb-8 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                        <div
                                            class="bg-dbv-blue text-white px-4 py-2 font-bold flex justify-between items-center">
                                            <span>{{ $unidade->nome }}</span>
                                            <span
                                                class="text-xs bg-white/20 px-2 py-1 rounded">{{ $unidade->desbravadores->count() }}
                                                membros</span>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm text-left">
                                                <thead
                                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                    <tr>
                                                        <th scope="col" class="px-4 py-3">Desbravador</th>
                                                        <th scope="col" class="px-4 py-3 text-center">Presente (10)
                                                        </th>
                                                        <th scope="col" class="px-4 py-3 text-center">Pontual (5)
                                                        </th>
                                                        <th scope="col" class="px-4 py-3 text-center">Bíblia (5)</th>
                                                        <th scope="col" class="px-4 py-3 text-center">Uniforme (10)
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($unidade->desbravadores as $dbv)
                                                        <tr
                                                            class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                                            <td class="px-4 py-3 font-medium">
                                                                {{-- CORREÇÃO: Input Hidden para garantir envio mesmo se tudo estiver desmarcado --}}
                                                                <input type="hidden"
                                                                    name="presencas[{{ $dbv->id }}][registrado]"
                                                                    value="1">
                                                                {{ $dbv->nome }}
                                                            </td>

                                                            <td class="px-4 py-3 text-center">
                                                                <input type="checkbox"
                                                                    name="presencas[{{ $dbv->id }}][presente]"
                                                                    value="1"
                                                                    class="w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-500">
                                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                                <input type="checkbox"
                                                                    name="presencas[{{ $dbv->id }}][pontual]"
                                                                    value="1"
                                                                    class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                                <input type="checkbox"
                                                                    name="presencas[{{ $dbv->id }}][biblia]"
                                                                    value="1"
                                                                    class="w-5 h-5 text-yellow-600 rounded border-gray-300 focus:ring-yellow-500">
                                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                                <input type="checkbox"
                                                                    name="presencas[{{ $dbv->id }}][uniforme]"
                                                                    value="1"
                                                                    class="w-5 h-5 text-purple-600 rounded border-gray-300 focus:ring-purple-500">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <div
                                class="flex items-center justify-end mt-4 sticky bottom-4 bg-white/90 dark:bg-gray-800/90 p-4 border rounded shadow-lg backdrop-blur-sm z-10">
                                <x-primary-button class="ml-4 text-lg px-6 py-3 bg-green-600 hover:bg-green-700">
                                    {{ __('Salvar Chamada') }}
                                </x-primary-button>
                            </div>
                        @endif
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
