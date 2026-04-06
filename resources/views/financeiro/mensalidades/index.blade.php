<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Controle de Mensalidades') }}
        </h2>
    </x-slot>

    {{-- Alpine Data para gerenciar os dois Modais (Gerar e Pagar) --}}
    <div class="ui-page space-y-8" x-data="{
        modalPagamentoOpen: false,
        modalGerarOpen: false,
    
        // Dados para o modal de pagamento
        pagamentoUrl: '',
        nomeDesbravador: '',
        valorMensalidade: '',
    
        openPagamento(url, nome, valor) {
            this.pagamentoUrl = url;
            this.nomeDesbravador = nome;
            this.valorMensalidade = valor;
            this.modalPagamentoOpen = true;
        }
    }">
        <div class="space-y-8">

            {{-- 1. Resumo Financeiro do Mês --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Recebido --}}
                <div
                    class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border-l-4 border-green-500 dark:border-green-600">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recebido (Mês)
                    </p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white">
                            R$ {{ number_format($valorRecebido, 2, ',', '.') }}
                        </h3>
                        <span
                            class="text-xs font-medium px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">
                            {{ $totalPago }} pagos
                        </span>
                    </div>
                </div>

                {{-- Pendente --}}
                <div
                    class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border-l-4 border-yellow-500 dark:border-yellow-600">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendente
                        (Mês)</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white">
                            R$ {{ number_format($valorPendente, 2, ',', '.') }}
                        </h3>
                        <span
                            class="text-xs font-medium px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full">
                            {{ $totalPendente }} restantes
                        </span>
                    </div>
                </div>

                {{-- Inadimplência Histórica (Acumulado) --}}
                <div
                    class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border-l-4 border-red-500 dark:border-red-600 md:col-span-2 lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Dívida Total Acumulada</p>
                            <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mt-2">
                                R$ {{ number_format($totalInadimplenteGeral, 2, ',', '.') }}
                            </h3>
                        </div>
                        <div class="text-right">
                            <span
                                class="block text-3xl font-extrabold text-gray-200 dark:text-gray-700">{{ $qtdInadimplentes }}</span>
                            <span class="text-xs text-gray-400">mensalidades atrasadas</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Barra de Ferramentas (Filtros e Ações) --}}
            <div
                class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">

                {{-- Filtros de Data --}}
                <form method="GET" action="{{ route('mensalidades.index') }}" class="w-full sm:w-auto flex gap-2">
                    <div class="relative w-1/2 md:w-40">
                        <label for="filtro_mes" class="sr-only">Filtrar por mes</label>
                        <select name="mes" onchange="this.form.submit()"
                            id="filtro_mes"
                            class="ui-input">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $mes == $m? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative w-1/2 md:w-32">
                        <label for="filtro_ano" class="sr-only">Filtrar por ano</label>
                        <select name="ano" onchange="this.form.submit()"
                            id="filtro_ano"
                            class="ui-input">
                            @foreach (range(date('Y') - 1, date('Y') + 1) as $y)
                                <option value="{{ $y }}" {{ $ano == $y? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                {{-- Botão de Gerar --}}
                <button @click="modalGerarOpen = true"
                    class="ui-btn-primary w-full sm:w-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Gerar Carnê do Mês
                </button>
            </div>

            {{-- 3. Lista de Mensalidades --}}
            @if ($mensalidades->count() > 0)
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

                    {{-- Cabeçalho da Tabela (Apenas Desktop) --}}
                    <div
                        class="hidden md:grid grid-cols-12 gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div class="col-span-5">Desbravador</div>
                        <div class="col-span-3 text-center">Valor</div>
                        <div class="col-span-2 text-center">Status</div>
                        <div class="col-span-2 text-right">Ações</div>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($mensalidades as $mensalidade)
                            <div
                                class="p-4 flex flex-col md:grid md:grid-cols-12 gap-4 items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">

                                {{-- Info Desbravador --}}
                                <div class="w-full md:col-span-5 flex items-center gap-3">
                                    {{-- Avatar com Iniciais --}}
                                    <div
                                        class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ substr($mensalidade->desbravador->nome, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                            {{ $mensalidade->desbravador->nome }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            {{ $mensalidade->desbravador->unidade->nome?? 'Sem Unidade' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Valor (Mobile: Escondido, aparece no rodapé do card) --}}
                                <div
                                    class="hidden md:block col-span-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    R$ {{ number_format($mensalidade->valor, 2, ',', '.') }}
                                </div>

                                {{-- Status --}}
                                <div class="w-full md:col-span-2 flex justify-between md:justify-center items-center">
                                    <span
                                        class="md:hidden text-sm font-medium text-gray-600 dark:text-gray-400">Status:</span>
                                    @if ($mensalidade->status == 'pago')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            PAGO
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            PENDENTE
                                        </span>
                                    @endif
                                </div>

                                {{-- Ações e Valor Mobile --}}
                                <div
                                    class="w-full md:col-span-2 flex items-center justify-between md:justify-end mt-2 md:mt-0 pt-2 md:pt-0 border-t border-gray-100 dark:border-gray-700 md:border-0">

                                    {{-- Valor no Mobile --}}
                                    <div class="md:hidden text-sm font-bold text-gray-900 dark:text-white">
                                        R$ {{ number_format($mensalidade->valor, 2, ',', '.') }}
                                    </div>

                                    @if ($mensalidade->status == 'pendente')
                                        <button
                                            @click="openPagamento('{{ route('mensalidades.pagar', $mensalidade->id) }}', '{{ $mensalidade->desbravador->nome }}', '{{ number_format($mensalidade->valor, 2, ',', '.') }}')"
                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase rounded-lg shadow-sm transition active:scale-95">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            Receber
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic text-right w-full">
                                            Pago em
                                            {{ \Carbon\Carbon::parse($mensalidade->data_pagamento)->format('d/m') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-1">
                    <x-empty-state
                        title="Nenhuma mensalidade encontrada"
                        description="Não h? registros para {{ $mes }}/{{ $ano }}. Voc? pode gerar as cobrancas do periodo com um clique.">
                        <x-slot:action>
                            <button @click="modalGerarOpen = true" class="ui-btn-primary">Gerar Agora</button>
                        </x-slot:action>
                    </x-empty-state>
                </div>
            @endif
        </div>

        {{-- MODAL 1: RECEBER PAGAMENTO --}}
        <div x-show="modalPagamentoOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="modalPagamentoOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
                @click="modalPagamentoOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="modalPagamentoOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-gray-700">

                    <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">Confirmar
                                    Recebimento</h3>
                                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Você está recebendo de:</p>
                                    <p class="text-lg font-bold text-gray-800 dark:text-white truncate"
                                        x-text="nomeDesbravador"></p>
                                    <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Valor:</span>
                                        <span class="text-xl font-extrabold text-green-600 dark:text-green-400"
                                            x-text="'R$ ' + valorMensalidade"></span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">O valor será lançado automaticamente no caixa
                                    como entrada.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <form :action="pagamentoUrl" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:w-auto transition">
                                Confirmar e Receber
                            </button>
                        </form>
                        <button type="button" @click="modalPagamentoOpen = false"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL 2: GERAR MENSALIDADES (MASSIVO) --}}
        <div x-show="modalGerarOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="modalGerarOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
                @click="modalGerarOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="modalGerarOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

                    <form action="{{ route('mensalidades.gerar') }}" method="POST">
                        @csrf
                        <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3
                                class="text-lg font-semibold leading-6 text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                                Gerar Carnê Mensal
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                Isso irá gerar uma cobrança pendente para <strong>todos os desbravadores ativos</strong>
                                do clube.
                            </p>

                            <div class="mt-6 grid grid-cols-2 gap-4">
                                {{-- Mês --}}
                                <div>
                                    <x-input-label for="mes_gerar" :value="__('Mês de Referência')" />
                                    <select name="mes" id="mes_gerar"
                                        class="block w-full mt-1 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 rounded-lg shadow-sm focus:ring-dbv-blue focus:border-dbv-blue">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}"
                                                {{ date('m') == $m? 'selected' : '' }}>
                                                {{ $m }} -
                                                {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Ano --}}
                                <div>
                                    <x-input-label for="ano_gerar" :value="__('Ano')" />
                                    <select name="ano" id="ano_gerar"
                                        class="block w-full mt-1 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 rounded-lg shadow-sm focus:ring-dbv-blue focus:border-dbv-blue">
                                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                        <option value="{{ date('Y') + 1 }}">{{ date('Y') + 1 }}</option>
                                    </select>
                                </div>
                                {{-- Valor --}}
                                <div class="col-span-2">
                                    <x-input-label for="valor_gerar" :value="__('Valor da Mensalidade (R$)')" />
                                    <div class="relative mt-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">R$</span>
                                        </div>
                                        <input type="number" name="valor" id="valor_gerar" step="0.01"
                                            value="15.00" required
                                            class="pl-10 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white rounded-lg shadow-sm focus:ring-dbv-blue focus:border-dbv-blue font-bold text-lg">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700/30 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-lg bg-dbv-blue dark:bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 dark:hover:bg-blue-500 sm:w-auto transition">
                                Gerar Cobranças
                            </button>
                            <button type="button" @click="modalGerarOpen = false"
                                class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>



