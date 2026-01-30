<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Central de Relatórios
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div>
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-4">Relatórios Rápidos</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <a href="{{ route('relatorios.financeiro') }}" target="_blank" class="block group">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition border-l-4 border-green-500">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-full text-green-600 mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">Relatório Financeiro</h4>
                                    <p class="text-sm text-gray-500">Fluxo de caixa completo.</p>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('relatorios.patrimonio') }}" target="_blank" class="block group">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-full text-blue-600 mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">Inventário Patrimonial</h4>
                                    <p class="text-sm text-gray-500">Itens e bens do clube.</p>
                                </div>
                            </div>
                        </div>
                    </a>

                    <form action="{{ route('relatorios.custom') }}" method="POST" target="_blank" class="block h-full">
                        @csrf
                        <input type="hidden" name="tipo" value="desbravadores">
                        <input type="hidden" name="status" value="ativos">
                        <button type="submit" class="w-full h-full bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition border-l-4 border-yellow-500 text-left">
                            <div class="flex items-center">
                                <div class="p-3 bg-yellow-100 rounded-full text-yellow-600 mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">Lista de Ativos</h4>
                                    <p class="text-sm text-gray-500">Relação rápida de membros.</p>
                                </div>
                            </div>
                        </button>
                    </form>

                </div>
            </div>

            <hr class="border-gray-300 dark:border-gray-700">

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm" x-data="{ tipo: 'desbravadores' }">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Gerador de Relatório Personalizado
                </h3>

                <form action="{{ route('relatorios.custom') }}" method="POST" target="_blank">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                        <div>
                            <x-input-label for="tipo" :value="__('Tipo de Relatório')" />
                            <select name="tipo" id="tipo" x-model="tipo" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="desbravadores">Lista de Desbravadores</option>
                                <option value="fichas_medicas">Fichas Médicas (Lote)</option>
                                <option value="caixa">Financeiro (Movimentações)</option>
                                <option value="unidades">Unidades (Estrutura)</option>
                            </select>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                            <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Filtros</h4>

                            <div x-show="tipo === 'desbravadores' || tipo === 'fichas_medicas'">
                                <div class="mb-3">
                                    <label class="block text-sm text-gray-700 dark:text-gray-300">Status</label>
                                    <select name="status" class="w-full rounded-md border-gray-300 text-sm">
                                        <option value="ativos">Apenas Ativos</option>
                                        <option value="todos">Todos (Inclui Inativos)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300">Unidade Específica</label>
                                    <select name="unidade_id" class="w-full rounded-md border-gray-300 text-sm">
                                        <option value="">Todas</option>
                                        @foreach(\App\Models\Unidade::all() as $u)
                                        <option value="{{ $u->id }}">{{ $u->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div x-show="tipo === 'caixa'" style="display: none;">
                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <div>
                                        <label class="block text-xs text-gray-500">De</label>
                                        <input type="date" name="data_inicio" class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500">Até</label>
                                        <input type="date" name="data_fim" class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300">Tipo</label>
                                    <select name="tipo_movimentacao" class="w-full rounded-md border-gray-300 text-sm">
                                        <option value="todos">Entradas e Saídas</option>
                                        <option value="entrada">Apenas Entradas</option>
                                        <option value="saida">Apenas Saídas</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="tipo === 'unidades'" style="display: none;">
                                <p class="text-sm text-gray-500 italic">Lista geral de unidades e seus conselheiros.</p>
                            </div>

                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Gerar PDF Personalizado
                        </x-primary-button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>