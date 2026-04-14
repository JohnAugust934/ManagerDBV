<x-app-layout>
    <x-slot name="header">
        Painel de Controle
    </x-slot>

    <div class="ui-page min-h-full space-y-10">
        
        {{-- HEADER DE BOAS VINDAS PREMIUM --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 ui-animate-fade-up">
            <div>
                <h2 class="text-4xl font-black text-slate-800 dark:text-white tracking-tight leading-none mb-2">
                    Olá, <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#002F6C] to-blue-500 dark:from-blue-400 dark:to-indigo-300">{{ explode(' ', Auth::user()->name)[0] }}</span>! 👋
                </h2>
                <p class="text-[15px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}
                </p>
            </div>

            @can('pedagogico')
                @if (!empty(auth()->user()->club_id))
                    <a href="{{ route('frequencia.create') }}" class="ui-btn-primary w-full md:w-auto shadow-xl shadow-blue-900/20 group">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span>Nova Chamada</span>
                    </a>
                @else
                    <span class="w-full sm:w-auto px-5 py-3 rounded-2xl border-2 border-amber-200/50 bg-amber-50 text-amber-800 text-sm font-bold shadow-sm">
                        Vincule um clube para registrar presença
                    </span>
                @endif
            @endcan
        </div>

        {{-- GRID DE ESTATÍSTICAS PREMIUM --}}
        <div class="grid grid-cols-1 {{ auth()->user()->can('financeiro') ? 'md:grid-cols-3' : 'md:grid-cols-1 max-w-md' }} gap-6 lg:gap-8">

            @can('financeiro')
                {{-- CARD 1: SALDO (Glassmorphism + Gradient) --}}
                <div class="relative overflow-hidden rounded-3xl p-7 shadow-2xl transition-all duration-300 hover:-translate-y-2 ui-animate-fade-up border-0 {{ $saldoAtual >= 0 ? 'bg-gradient-to-br from-[#065F46] to-[#047857]' : 'bg-gradient-to-br from-[#991B1B] to-[#B91C1C]' }}" style="animation-delay: 100ms;">
                    <!-- Decorativas -->
                    <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -top-12 -left-12 w-32 h-32 bg-black opacity-10 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-white/70 text-xs font-bold uppercase tracking-widest mb-1 shadow-sm">Saldo em Caixa</p>
                            <h3 class="text-4xl font-black text-white tracking-tighter drop-shadow-md">
                                <span class="text-2xl text-white/80 mr-1 font-bold">R$</span>{{ number_format($saldoAtual, 2, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3.5 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner">
                            <svg class="w-6 h-6 text-white drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: INADIMPLÊNCIA --}}
                <div class="ui-card p-7 hover:-translate-y-2 transition-transform ui-animate-fade-up relative overflow-hidden group" style="animation-delay: 200ms;">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-500/5 dark:bg-orange-500/10 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Inadimplência</p>
                                <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">
                                    {{ $taxaInadimplencia }}<span class="text-2xl text-slate-400 font-bold ml-1">%</span>
                                </h3>
                            </div>
                            <div class="p-3.5 bg-orange-50 dark:bg-orange-500/10 rounded-2xl text-orange-500 dark:text-orange-400 ring-1 ring-orange-500/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-6">
                            <div class="w-full bg-slate-100 dark:bg-slate-700/50 rounded-full h-2 overflow-hidden shadow-inner">
                                <div class="bg-gradient-to-r from-orange-400 to-orange-600 h-full rounded-full transition-all duration-1000" style="width: {{ $taxaInadimplencia }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            {{-- CARD 3: ATIVOS --}}
            <div class="ui-card p-7 hover:-translate-y-2 transition-transform ui-animate-fade-up relative overflow-hidden group" style="animation-delay: 300ms;">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 dark:bg-blue-500/10 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Total de Membros</p>
                            <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">
                                {{ $totalAtivos }}
                            </h3>
                        </div>
                        <div class="p-3.5 bg-blue-50 dark:bg-blue-500/10 rounded-2xl text-blue-600 dark:text-blue-400 ring-1 ring-blue-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="mt-5 flex items-center justify-between">
                        <div class="flex -space-x-3 overflow-hidden ml-1">
                            <div class="inline-flex h-8 w-8 rounded-full border-2 border-white dark:border-slate-800 bg-[#002F6C] items-center justify-center text-[10px] font-bold text-white shadow-sm z-30">D</div>
                            <div class="inline-flex h-8 w-8 rounded-full border-2 border-white dark:border-slate-800 bg-[#D9222A] items-center justify-center text-[10px] font-bold text-white shadow-sm z-20">B</div>
                            <div class="inline-flex h-8 w-8 rounded-full border-2 border-white dark:border-slate-800 bg-[#FCD116] items-center justify-center text-[10px] font-bold text-slate-800 shadow-sm z-10">V</div>
                        </div>
                        <p class="text-xs font-semibold text-slate-400">Ativos no Sistema</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEÇÃO PRINCIPAL (Gráfico & Atalhos) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 ui-animate-fade-up" style="animation-delay: 400ms;">

            {{-- GRÁFICO PREMIUM --}}
            <div class="lg:col-span-2 ui-card p-6 lg:p-8 flex flex-col">
                <div class="flex items-center justify-between mb-8 border-b border-slate-100 dark:border-slate-800/60 pb-6">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-3">
                            <svg class="w-6 h-6 text-[#D9222A]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            Frequência nas Reuniões
                        </h3>
                        <p class="text-sm text-slate-500 mt-1 font-medium">Acompanhamento dos últimos encontros do clube</p>
                    </div>
                    <div class="hidden sm:block">
                        <span class="ui-badge bg-blue-50 dark:bg-blue-500/10 text-[#002F6C] dark:text-blue-400 ring-1 ring-inset ring-blue-500/20">Últimas 5</ui-badge>
                    </div>
                </div>

                <div class="relative flex-1 min-h-[320px] w-full">
                    @if (isset($dadosGrafico) && $dadosGrafico->count() > 0 && $dadosGrafico->sum() > 0)
                        <canvas id="frequenciaChart"></canvas>
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 bg-slate-50/50 dark:bg-slate-800/20 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-700 m-2">
                            <div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center mb-4 text-slate-300 ring-1 ring-slate-100 dark:ring-slate-700">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-500">Sem dados suficientes</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-[200px] text-center">Realize chamadas recentes para gerar as estatísticas do Clube.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ATALHOS RÁPIDOS GLASS --}}
            <div class="ui-card p-6 lg:p-8 bg-gradient-to-b from-white to-slate-50/80 dark:from-slate-900 dark:to-slate-800/80">
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                    Acesso Rápido
                    <svg class="w-5 h-5 text-[#FCD116]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </h3>

                <div class="space-y-4">
                    @can('secretaria')
                        <a href="{{ route('desbravadores.create') }}" class="group flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700/60 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-800 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#002F6C] to-blue-600 text-white flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-slate-200 leading-none mb-1">Novo Membro</span>
                                    <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Secretaria</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-slate-300 group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @endcan

                    @can('financeiro')
                        <a href="{{ route('caixa.create') }}" class="group flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700/60 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-slate-200 leading-none mb-1">Entrada no Caixa</span>
                                    <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Tesouraria</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-slate-300 group-hover:text-emerald-500 transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @endcan

                    @can('pedagogico')
                        <a href="{{ route('classes.index') }}" class="group flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700/60 hover:shadow-md hover:border-purple-200 dark:hover:border-purple-800 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 text-white flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-slate-200 leading-none mb-1">Acompanhar Classes</span>
                                    <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Pedagógico</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-slate-300 group-hover:text-purple-500 transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @endcan

                    @can('relatorios')
                        <a href="{{ route('relatorios.index') }}" class="group flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700/60 hover:shadow-md hover:border-amber-200 dark:hover:border-amber-800 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-[#D9222A] text-white flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-slate-200 leading-none mb-1">Gerar Relatórios</span>
                                    <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Geral</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-slate-300 group-hover:text-[#D9222A] transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT CHART.JS Premium Stylization --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('frequenciaChart');
            const labels = @json($labelsGrafico->values() ?? []);
            const data = @json($dadosGrafico->values() ?? []);

            if (ctx && labels.length > 0) {
                const canvasContext = ctx.getContext('2d');
                
                // Desbravadores Gradient Match
                let gradient = canvasContext.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(0, 47, 108, 0.4)'); // dbv-blue alpha
                gradient.addColorStop(1, 'rgba(0, 47, 108, 0.0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Presença',
                            data: data,
                            borderWidth: 4,
                            borderColor: '#002F6C', // dbv-blue
                            backgroundColor: gradient,
                            fill: true,
                            pointBackgroundColor: '#FCD116', // dbv-yellow
                            pointBorderColor: '#002F6C',
                            pointBorderWidth: 3,
                            pointRadius: 6,
                            pointHoverRadius: 9,
                            tension: 0.4 // Mais smooth
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
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                titleFont: { size: 13, family: 'Inter' },
                                bodyFont: { size: 15, weight: 'bold', family: 'Inter' },
                                padding: 14,
                                cornerRadius: 16,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) { return context.parsed.y + '% Presentes'; }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                border: { display: false },
                                grid: {
                                    color: 'rgba(100, 116, 139, 0.1)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    callback: function(value) { return value + "%" },
                                    font: { size: 12, weight: '600', family: 'Inter' },
                                    color: '#94a3b8',
                                    padding: 10
                                }
                            },
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    font: { size: 12, weight: '600', family: 'Inter' },
                                    color: '#94a3b8',
                                    padding: 10
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
