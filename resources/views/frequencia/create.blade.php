<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-200 leading-tight">
            {{ __('Registro de Chamada e Pontuação') }}
        </h2>
    </x-slot>

    {{-- Adicionamos o Alpine.js x-data aqui para gerenciar o filtro instantâneo --}}
    <div class="ui-page" x-data="{ filtroUnidade: '' }">
        <div>
            <div class="ui-card overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('frequencia.store') }}" method="POST">
                        @csrf

                        <div
                            class="flex flex-col md:flex-row gap-6 mb-8 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-600">
                            {{-- Campo de Data --}}
                            <div class="w-full md:w-1/3">
                                <x-input-label for="data" :value="__('Data da Reunião')"
                                    class="font-bold text-dbv-blue dark:text-blue-300" />
                                <input type="date" name="data" id="data" value="{{ date('Y-m-d') }}" required
                                    class="ui-input mt-1">
                            </div>

                            {{-- Novo Filtro de Unidade --}}
                            <div class="w-full md:w-1/3">
                                <x-input-label for="filtro" :value="__('Filtrar por Unidade')"
                                    class="font-bold text-dbv-blue dark:text-blue-300" />
                                <select id="filtro" x-model="filtroUnidade"
                                    class="ui-input mt-1 cursor-pointer">
                                    <option value="">Todas as Unidades (Visão Geral)</option>
                                    @foreach ($unidades as $u)
                                        <option value="{{ $u->id }}">{{ $u->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if ($unidades->isEmpty())
                            <div class="text-center py-10 text-gray-500">
                                Nenhuma unidade ou desbravador ativo disponível para chamada.
                            </div>
                        @else
                            @foreach ($unidades as $unidade)
                                @if ($unidade->desbravadores->count() > 0)
                                    {{-- A mágica do filtro acontece aqui no x-show --}}
                                    <div x-show="filtroUnidade === '' || filtroUnidade == '{{ $unidade->id }}'"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        class="mb-8 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">

                                        <div
                                            class="bg-dbv-blue text-white px-4 py-3 font-bold flex justify-between items-center">
                                            <span class="text-lg tracking-wide">{{ $unidade->nome }}</span>
                                            <span class="text-xs bg-white/20 px-3 py-1 rounded-full shadow-inner">
                                                {{ $unidade->desbravadores->count() }} membros
                                            </span>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm text-left">
                                                <thead
                                                    class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                                    <tr>
                                                        <th scope="col" class="px-4 py-4">Desbravador</th>
                                                        <th scope="col" class="px-4 py-4 text-center">Presente (10)
                                                        </th>
                                                        <th scope="col" class="px-4 py-4 text-center">Pontual (5)
                                                        </th>
                                                        <th scope="col" class="px-4 py-4 text-center">Bíblia (5)</th>
                                                        <th scope="col" class="px-4 py-4 text-center">Uniforme (10)
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($unidade->desbravadores as $dbv)
                                                        <tr
                                                            class="border-b dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-gray-600 transition-colors">
                                                            <td
                                                                class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                                                <input type="hidden"
                                                                    name="presencas[{{ $dbv->id }}][registrado]"
                                                                    value="1">
                                                                {{ $dbv->nome }}
                                                            </td>

                                                            <td class="px-4 py-3 text-center">
                                                                <input type="checkbox"
                                                                    name="presencas[{{ $dbv->id }}][presente]"
                                                                    value="1"
                                                                    class="w-5 h-5 text-green-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-green-500 cursor-pointer">
                                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                                <input type="checkbox"
                                                                    name="presencas[{{ $dbv->id }}][pontual]"
                                                                    value="1"
                                                                    class="w-5 h-5 text-blue-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 cursor-pointer">
                                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                                <input type="checkbox"
                                                                    name="presencas[{{ $dbv->id }}][biblia]"
                                                                    value="1"
                                                                    class="w-5 h-5 text-yellow-500 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-yellow-500 cursor-pointer">
                                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                                <input type="checkbox"
                                                                    name="presencas[{{ $dbv->id }}][uniforme]"
                                                                    value="1"
                                                                    class="w-5 h-5 text-purple-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-purple-500 cursor-pointer">
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
                                class="flex items-center justify-end mt-4 sticky bottom-4 bg-white/90 dark:bg-gray-800/90 p-4 border dark:border-gray-700 rounded-xl shadow-2xl backdrop-blur-md z-10">
                                <x-primary-button
                                    class="ml-4 text-lg px-8 py-3 bg-green-600 hover:bg-green-700 shadow-lg shadow-green-500/30 transform transition hover:-translate-y-1">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
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


