<x-app-layout>
    <x-slot name="header">
        Visão Geral do Clube
    </x-slot>

    <div class="space-y-8">
        <div class="flex flex-col md:flex-row gap-6">
            <div class="flex-1 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Ranking das Unidades
                    </h3>
                    <a href="{{ route('frequencia.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Nova Chamada
                    </a>
                </div>

                @if(isset($ranking) && count($ranking) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($ranking as $index => $unidade)
                    <div class="relative flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border {{ $index === 0 ? 'border-yellow-400 ring-1 ring-yellow-200 bg-yellow-50' : 'border-gray-200' }} transition hover:shadow-sm">
                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full {{ $index === 0 ? 'bg-yellow-200 text-yellow-700' : ($index === 1 ? 'bg-gray-200 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600')) }} font-bold text-lg shadow-sm">
                            #{{ $index + 1 }}
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <div class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $unidade['nome'] }}</div>
                            <div class="text-xs text-gray-500 font-medium">{{ $unidade['pontos'] }} pontos</div>
                        </div>
                        @if($index === 0)
                        <div class="absolute -top-1 -right-1">
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                            </span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500 text-sm bg-gray-50 rounded-lg border border-dashed border-gray-200">
                    Nenhuma pontuação registrada ainda. Realize a primeira chamada!
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <a href="{{ route('desbravadores.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-dbv-blue transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-50 text-dbv-blue rounded-lg group-hover:bg-dbv-blue group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase">Secretaria</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-dbv-blue">Desbravadores</h3>
                <p class="text-sm text-gray-500 mt-2">Gerenciar cadastro de membros, fichas médicas e dados pessoais.</p>
            </a>

            <a href="{{ route('unidades.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-dbv-red transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-red-50 text-dbv-red rounded-lg group-hover:bg-dbv-red group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase">Estrutura</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-dbv-red">Unidades</h3>
                <p class="text-sm text-gray-500 mt-2">Organização das unidades, conselheiros e capitães.</p>
            </a>

            <a href="{{ route('caixa.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-green-600 transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase">Tesouraria</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-green-600">Financeiro</h3>
                <p class="text-sm text-gray-500 mt-2">Controle de caixa, entradas, saídas e fluxo financeiro.</p>
            </a>

            <a href="{{ route('mensalidades.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-yellow-500 transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-yellow-50 text-yellow-600 rounded-lg group-hover:bg-dbv-yellow group-hover:text-black transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase">Tesouraria</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-yellow-600">Mensalidades</h3>
                <p class="text-sm text-gray-500 mt-2">Gerador de boletos mensais e controle de pagamentos.</p>
            </a>

            <a href="{{ route('patrimonio.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-600 transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-lg group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase">Inventário</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-purple-600">Patrimônio</h3>
                <p class="text-sm text-gray-500 mt-2">Controle de bens, barracas, materiais e equipamentos.</p>
            </a>

            <a href="{{ route('atas.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-600 transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase">Secretaria</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600">Atas e Documentos</h3>
                <p class="text-sm text-gray-500 mt-2">Registro oficial de reuniões e decisões da diretoria.</p>
            </a>

        </div>
    </div>
</x-app-layout>