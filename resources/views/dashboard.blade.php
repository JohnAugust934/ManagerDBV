<x-app-layout>
    {{-- Estilo para animação de entrada suave --}}
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }
    </style>

    <div class="py-8 bg-gray-50 dark:bg-dbv-dark-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- HEADER DE BOAS VINDAS --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 animate-fade-in-up">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-white tracking-tight">
                        Olá, {{ Auth::user()->name }}! 👋
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{-- CORREÇÃO DA DATA: Forçando pt_BR --}}
                        {{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}
                    </p>
                </div>

                <a href="{{ route('frequencia.create') }}"
                    class="w-full md:w-auto bg-dbv-blue hover:bg-blue-800 text-white px-6 py-3 rounded-xl shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2 transition-all hover:scale-105 active:scale-95 group">
                    <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    <span class="font-semibold">Nova Chamada</span>
                </a>
            </div>

            {{-- GRID DE ESTATÍSTICAS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- CARD 1: SALDO --}}
                <div
                    class="relative overflow-hidden rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up delay-100 {{ $saldoAtual >= 0 ? 'bg-gradient-to-br from-emerald-500 to-emerald-700 shadow-emerald-500/20' : 'bg-gradient-to-br from-red-500 to-red-700 shadow-red-500/20' }}">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-white/80 text-xs font-bold uppercase tracking-wider">Saldo em Caixa</p>
                            <h3 class="text-3xl font-extrabold text-white mt-1">
                                R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-white/70 text-xs">
                        <span class="bg-white/20 px-2 py-0.5 rounded text-white mr-2 font-bold">Total</span>
                        Acumulado até hoje
                    </div>
                    <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                </div>

                {{-- CARD 2: INADIMPLÊNCIA --}}
                <div
                    class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all animate-fade-in-up delay-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">
                                Inadimplência (Mês)</p>
                            <h3 class="text-3xl font-extrabold text-gray-800 dark:text-white mt-1">
                                {{ $taxaInadimplencia }}%
                            </h3>
                        </div>
                        <div
                            class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-xl text-orange-600 dark:text-orange-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="bg-orange-500 h-1.5 rounded-full" style="width: {{ $taxaInadimplencia }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">Referente a mensalidades pendentes</p>
                </div>

                {{-- CARD 3: ATIVOS --}}
                <div
                    class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all animate-fade-in-up delay-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">
                                Membros Ativos</p>
                            <h3 class="text-3xl font-extrabold text-gray-800 dark:text-white mt-1">
                                {{ $totalAtivos }}
                            </h3>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl text-dbv-blue dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex -space-x-2 overflow-hidden">
                        {{-- Avatares simulados para dar vida --}}
                        <div
                            class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-800 bg-gray-200 flex items-center justify-center text-xs">
                            DB</div>
                        <div
                            class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-800 bg-blue-200 flex items-center justify-center text-xs">
                            V</div>
                        <div
                            class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-800 bg-gray-100 flex items-center justify-center text-[10px] text-gray-500">
                            +{{ $totalAtivos > 2 ? $totalAtivos - 2 : 0 }}</div>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">Desbravadores regulares</p>
                </div>
            </div>

            {{-- SEÇÃO PRINCIPAL --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up delay-300">

                {{-- GRÁFICO --}}
                <div
                    class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Frequência nas Reuniões</h3>
                            <p class="text-sm text-gray-500">Acompanhamento dos últimos encontros</p>
                        </div>
                        <div class="hidden sm:block">
                            <span
                                class="px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-dbv-blue text-xs font-semibold rounded-full border border-blue-100 dark:border-blue-800">
                                Últimas 5
                            </span>
                        </div>
                    </div>

                    <div class="relative flex-1 min-h-[300px] w-full">
                        {{-- CORREÇÃO: Verifica se há dados REAIS para exibir --}}
                        @if (isset($dadosGrafico) && $dadosGrafico->count() > 0 && $dadosGrafico->sum() > 0)
                            <canvas id="frequenciaChart"></canvas>
                        @else
                            {{-- Empty State Bonito --}}
                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-gray-50 dark:bg-gray-700/20 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600">
                                <div class="p-4 bg-white dark:bg-gray-800 rounded-full shadow-sm mb-3">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500">Ainda não há dados suficientes.</p>
                                <p class="text-xs text-gray-400 mt-1">Faça a primeira chamada para ver o gráfico.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ATALHOS RÁPIDOS (Menu Vertical Elegante) --}}
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6">Acesso Rápido</h3>

                    <div class="space-y-3">
                        <a href="{{ route('desbravadores.create') }}"
                            class="group flex items-center justify-between p-3 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 border border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                        </path>
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Novo Membro</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 transform group-hover:translate-x-1 transition-all"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        <a href="{{ route('caixa.create') }}"
                            class="group flex items-center justify-between p-3 rounded-xl hover:bg-green-50 dark:hover:bg-green-900/20 border border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/50 text-green-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Lançar
                                    Caixa</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-green-500 transform group-hover:translate-x-1 transition-all"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        <a href="{{ route('classes.index') }}"
                            class="group flex items-center justify-between p-3 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-900/20 border border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Classes</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-purple-500 transform group-hover:translate-x-1 transition-all"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        <a href="{{ route('relatorios.index') }}"
                            class="group flex items-center justify-between p-3 rounded-xl hover:bg-orange-50 dark:hover:bg-orange-900/20 border border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Relatórios</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transform group-hover:translate-x-1 transition-all"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPT CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('frequenciaChart');

            // CORREÇÃO: Usar .values() no Blade para garantir array indexado [0, 1, 2] e não objeto {"0": val}
            const labels = @json($labelsGrafico->values());
            const data = @json($dadosGrafico->values());

            if (ctx && labels.length > 0) {
                const canvasContext = ctx.getContext('2d');
                let gradient = canvasContext.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); // Azul
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)'); // Transparente

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Presença',
                            data: data,
                            borderWidth: 3,
                            borderColor: '#3B82F6',
                            backgroundColor: gradient,
                            fill: true,
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: '#3B82F6',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                titleFont: {
                                    size: 13
                                },
                                bodyFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + '% Presentes';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                border: {
                                    display: false
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.1)',
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + "%"
                                    },
                                    font: {
                                        size: 11,
                                        weight: '500'
                                    },
                                    color: '#9CA3AF'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11,
                                        weight: '500'
                                    },
                                    color: '#9CA3AF'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
