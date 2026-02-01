<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                {{ __('Mensalidades') }}
            </h2>

            <button onclick="document.getElementById('modal-gerar').classList.remove('hidden')"
                class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 transition shadow-md shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                Gerar Carnê
            </button>
        </div>
    </x-slot>

    <div class="py-6 space-y-6" x-data="{
        paymentModalOpen: false,
        pagamentoUrl: '',
        nomeDesbravador: '',
        valorMensalidade: '',
        openPaymentModal(url, nome, valor) {
            this.pagamentoUrl = url;
            this.nomeDesbravador = nome;
            this.valorMensalidade = valor;
            this.paymentModalOpen = true;
        }
    }">

        <div class="md:hidden px-4">
            <button onclick="document.getElementById('modal-gerar').classList.remove('hidden')"
                class="w-full flex items-center justify-center px-4 py-3 bg-dbv-blue border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-800 shadow-md transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Gerar Mensalidades
            </button>
        </div>

        <div class="px-4 md:px-0">

            <div
                class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <form method="GET" action="{{ route('mensalidades.index') }}"
                    class="flex items-center gap-2 w-full md:w-auto">
                    <select name="mes"
                        class="flex-1 md:w-40 border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg focus:ring-dbv-blue focus:border-dbv-blue text-sm"
                        onchange="this.form.submit()">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}
                            </option>
                        @endforeach
                    </select>
                    <select name="ano"
                        class="w-24 border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg focus:ring-dbv-blue focus:border-dbv-blue text-sm"
                        onchange="this.form.submit()">
                        @foreach (range(date('Y') - 1, date('Y') + 1) as $y)
                            <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="flex gap-4 text-sm w-full md:w-auto justify-between md:justify-end">
                    <div class="text-center md:text-right">
                        <span class="block text-xs font-bold text-gray-500 uppercase">Recebido</span>
                        <span class="font-bold text-green-600 dark:text-green-400">R$
                            {{ number_format($valorRecebido, 2, ',', '.') }}</span>
                        <span class="text-xs text-gray-400">({{ $totalPago }} pagantes)</span>
                    </div>
                    <div class="text-center md:text-right">
                        <span class="block text-xs font-bold text-gray-500 uppercase">Pendente</span>
                        <span class="font-bold text-red-500 dark:text-red-400">R$
                            {{ number_format($valorPendente, 2, ',', '.') }}</span>
                        <span class="text-xs text-gray-400">({{ $totalPendente }} devendo)</span>
                    </div>
                </div>
            </div>

            @if ($mensalidades->count() > 0)
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                    <div class="divide-y divide-gray-100 dark:divide-slate-700">
                        @foreach ($mensalidades as $mensalidade)
                            <div
                                class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition relative overflow-hidden">

                                <div
                                    class="absolute left-0 top-0 bottom-0 w-1 {{ $mensalidade->status == 'pago' ? 'bg-green-500' : 'bg-red-400' }}">
                                </div>

                                <div class="pl-3 flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white">
                                        {{ $mensalidade->desbravador->nome }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-md font-bold uppercase {{ $mensalidade->status == 'pago' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                            {{ $mensalidade->status == 'pago' ? 'PAGO' : 'PENDENTE' }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                            R$ {{ number_format($mensalidade->valor, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="pl-3 md:pl-0">
                                    @if ($mensalidade->status == 'pendente')
                                        <button
                                            @click="openPaymentModal('{{ route('mensalidades.pagar', $mensalidade->id) }}', '{{ $mensalidade->desbravador->nome }}', '{{ number_format($mensalidade->valor, 2, ',', '.') }}')"
                                            class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase rounded-lg shadow-sm transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Receber
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-green-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
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
                <div
                    class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700">
                    <p class="text-gray-500 dark:text-gray-400">Nenhuma mensalidade gerada para este mês.</p>
                    <button onclick="document.getElementById('modal-gerar').classList.remove('hidden')"
                        class="mt-4 text-dbv-blue hover:underline font-bold text-sm">Gerar Agora</button>
                </div>
            @endif

            <div x-show="paymentModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">

                <div x-show="paymentModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

                <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                    <div x-show="paymentModalOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        @click.away="paymentModalOpen = false"
                        class="relative bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md w-full border border-gray-100 dark:border-slate-700">

                        <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 sm:mx-0 sm:h-10 sm:w-10 mb-4 sm:mb-0">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white"
                                        id="modal-title">
                                        Receber Mensalidade
                                    </h3>
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Confirmar recebimento de:
                                        </p>
                                        <p class="text-2xl font-extrabold text-green-600 dark:text-green-400 mt-1 mb-2"
                                            x-text="'R$ ' + valorMensalidade"></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            Referente a: <strong x-text="nomeDesbravador"></strong>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-4 italic">
                                            * O valor será lançado automaticamente como entrada no caixa.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                            <button type="button" @click="paymentModalOpen = false"
                                class="w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none sm:w-auto sm:text-sm">
                                Cancelar
                            </button>

                            <form :action="pagamentoUrl" method="POST" class="w-full sm:w-auto">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:w-auto sm:text-sm">
                                    Confirmar Recebimento
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div id="modal-gerar" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    onclick="document.getElementById('modal-gerar').classList.add('hidden')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('mensalidades.gerar') }}" method="POST">
                        @csrf
                        <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Gerar Mensalidades
                                em Massa</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Isso criará uma cobrança para
                                <strong>todos</strong> os desbravadores ativos.</p>

                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="mes_gerar" :value="__('Mês')" />
                                    <select name="mes" id="mes_gerar"
                                        class="block w-full mt-1 border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg shadow-sm">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}"
                                                {{ date('m') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="ano_gerar" :value="__('Ano')" />
                                    <select name="ano" id="ano_gerar"
                                        class="block w-full mt-1 border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg shadow-sm">
                                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                        <option value="{{ date('Y') + 1 }}">{{ date('Y') + 1 }}</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <x-input-label for="valor_gerar" :value="__('Valor da Mensalidade (R$)')" />
                                    <x-text-input id="valor_gerar" class="block mt-1 w-full" type="number"
                                        step="0.01" name="valor" value="15.00" required />
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-slate-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-dbv-blue text-base font-medium text-white hover:bg-blue-800 sm:ml-3 sm:w-auto sm:text-sm">
                                Confirmar
                            </button>
                            <button type="button"
                                onclick="document.getElementById('modal-gerar').classList.add('hidden')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</x-app-layout>
