<x-app-layout>
    <x-slot name="header">
        Painel de Controle
    </x-slot>

    <div class="ui-page min-h-full space-y-10 max-w-7xl mx-auto">
        
        {{-- HEADER DE BOAS VINDAS HERO PREMIUM --}}
        <div class="relative overflow-hidden rounded-[32px] p-8 md:p-12 ui-animate-fade-up bg-gradient-to-r from-[#001D42] via-[#002F6C] to-blue-900 shadow-2xl shadow-blue-900/40">
            <!-- Efeito Glow Hero -->
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-500/20 rounded-full blur-[100px] pointer-events-none -mt-32 -mr-32"></div>
            <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-indigo-500/20 rounded-full blur-[80px] pointer-events-none -mb-32 -ml-32"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                <div class="flex items-center gap-6">
                    <!-- Avatar Giant -->
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-4xl md:text-5xl font-black text-white shadow-inner shrink-0">
                        {{ mb_strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    
                    <div>
                        <h2 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-none mb-3">
                            Olá, <span class="text-blue-300">{{ explode(' ', Auth::user()->name)[0] }}</span>! 👋
                        </h2>
                        <p class="text-sm md:text-[15px] text-blue-100/70 font-bold flex items-center gap-2 tracking-wide uppercase">
                            <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}
                        </p>
                    </div>
                </div>

                @can('pedagogico')
                    @if (!empty(auth()->user()->club_id))
                        <a href="{{ route('frequencia.create') }}" class="w-full md:w-auto px-8 py-4 bg-white hover:bg-blue-50 text-[#002F6C] font-black text-sm uppercase tracking-widest rounded-2xl shadow-xl shadow-black/10 transition-all active:scale-95 flex items-center justify-center gap-3 shrink-0 group">
                            <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Realizar Chamada
                        </a>
                    @else
                        <span class="w-full md:w-auto px-6 py-4 rounded-2xl bg-amber-500/20 backdrop-blur-md border border-amber-300/30 text-amber-200 text-[11px] font-black uppercase tracking-widest text-center">
                            Vincule um clube para continuar
                        </span>
                    @endif
                @endcan
            </div>
        </div>

        {{-- GRID DE ESTATÍSTICAS KPI (FINTECH STYLE) --}}
        <div class="grid grid-cols-1 {{ auth()->user()->can('financeiro') ? 'md:grid-cols-3' : 'md:grid-cols-1 max-w-md' }} gap-6">

            @can('financeiro')
                {{-- CARD 1: SALDO (Glassmorphism + Azul/Verde) --}}
                <div class="ui-card relative overflow-hidden p-6 lg:p-7 border-b-4 {{ $saldoAtual >= 0 ? 'border-emerald-500 dark:border-emerald-500/50 bg-gradient-to-b from-white to-emerald-50/50 dark:from-slate-900 dark:to-emerald-900/10' : 'border-red-500 dark:border-red-500/50 bg-gradient-to-b from-white to-red-50/50 dark:from-slate-900 dark:to-red-900/10' }} group ui-animate-fade-up" style="animation-delay: 100ms;">
                    <div class="absolute -right-4 -top-4 w-28 h-28 {{ $saldoAtual >= 0 ? 'bg-emerald-500' : 'bg-red-500' }} rounded-full blur-[40px] opacity-10 dark:opacity-20 transition-transform group-hover:scale-125"></div>
                    
                    <div class="relative z-10 flex justify-between items-start mb-4">
                        <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Saldo em Caixa</p>
                        <div class="p-2.5 rounded-xl {{ $saldoAtual >= 0 ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-3xl lg:text-4xl font-black text-slate-800 dark:text-white tracking-tighter">
                            <span class="text-xl text-slate-400 mr-0.5">R$</span>{{ number_format($saldoAtual, 2, ',', '.') }}
                        </h3>
                    </div>
                </div>

                {{-- CARD 2: INADIMPLÊNCIA (Alert) --}}
                <div class="ui-card relative overflow-hidden p-6 lg:p-7 border-b-4 border-amber-500 dark:border-amber-500/50 bg-gradient-to-b from-white to-amber-50/50 dark:from-slate-900 dark:to-amber-900/10 group ui-animate-fade-up" style="animation-delay: 200ms;">
                    <div class="absolute -right-4 -top-4 w-28 h-28 bg-amber-500 rounded-full blur-[40px] opacity-10 dark:opacity-20 transition-transform group-hover:scale-125"></div>
                    
                    <div class="relative z-10 flex justify-between items-start mb-4">
                        <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Taxa de Inadimplência</p>
                        <div class="p-2.5 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="relative z-10">
                        <h3 class="text-3xl lg:text-4xl font-black text-slate-800 dark:text-white tracking-tighter">
                            {{ $taxaInadimplencia }}<span class="text-xl text-slate-400 font-bold ml-1">%</span>
                        </h3>
                        <div class="mt-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden shadow-inner">
                            <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-full rounded-full transition-all duration-1000" style="width: {{ $taxaInadimplencia }}%"></div>
                        </div>
                    </div>
                </div>
            @endcan

            {{-- CARD 3: ATIVOS --}}
            <div class="ui-card relative overflow-hidden p-6 lg:p-7 border-b-4 border-blue-500 dark:border-blue-500/50 bg-gradient-to-b from-white to-blue-50/50 dark:from-slate-900 dark:to-blue-900/10 group ui-animate-fade-up" style="animation-delay: 300ms;">
                <div class="absolute -right-4 -top-4 w-28 h-28 bg-blue-500 rounded-full blur-[40px] opacity-10 dark:opacity-20 transition-transform group-hover:scale-125"></div>
                
                <div class="relative z-10 flex justify-between items-start mb-4">
                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Base de Membros</p>
                    <div class="p-2.5 bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
                
                <div class="relative z-10 flex items-end justify-between">
                    <h3 class="text-3xl lg:text-4xl font-black text-slate-800 dark:text-white tracking-tighter">
                        {{ $totalAtivos }}
                    </h3>
                    
                    <div class="flex -space-x-2 overflow-hidden pb-1">
                        <div class="inline-flex h-7 w-7 rounded-full border-2 border-white dark:border-slate-900 bg-[#002F6C] items-center justify-center text-[9px] font-black text-white shadow-sm z-30">D</div>
                        <div class="inline-flex h-7 w-7 rounded-full border-2 border-white dark:border-slate-900 bg-[#D9222A] items-center justify-center text-[9px] font-black text-white shadow-sm z-20">B</div>
                        <div class="inline-flex h-7 w-7 rounded-full border-2 border-white dark:border-slate-900 bg-amber-400 items-center justify-center text-[9px] font-black text-slate-900 shadow-sm z-10">V</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEÇÃO PRINCIPAL (Gráfico & Atalhos) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 ui-animate-fade-up" style="animation-delay: 400ms;">

            {{-- GRÁFICO DE PRESENÇA --}}
            <div class="lg:col-span-2 ui-card p-0 flex flex-col overflow-hidden">
                <div class="p-6 md:p-8 flex items-center justify-between border-b border-slate-100 dark:border-slate-800/60 pb-6 bg-slate-50/50 dark:bg-slate-900/30">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-3">
                            <svg class="w-6 h-6 text-[#D9222A]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            Frequência Recorrente
                        </h3>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mt-1">Evolução do engajamento ativo</p>
                    </div>
                </div>

                <div class="relative flex-1 min-h-[320px] w-full p-6">
                    @if (isset($dadosGrafico) && $dadosGrafico->count() > 0 && $dadosGrafico->sum() > 0)
                        <canvas id="frequenciaChart"></canvas>
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 bg-slate-50/50 dark:bg-slate-800/20 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-700 m-6">
                            <div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center mb-4 text-slate-300 ring-1 ring-slate-100 dark:ring-slate-700">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-black text-slate-500 uppercase tracking-widest">Sem dados suficientes</p>
                            <p class="text-xs font-bold text-slate-400 mt-2 max-w-[200px] text-center">Inicie as chamadas para preencher os gráficos dinâmicos do clube.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ATALHOS RÁPIDOS GLASS --}}
            <div class="ui-card p-6 lg:p-8 bg-gradient-to-b from-white to-slate-50/80 dark:from-slate-800 dark:to-slate-900/80">
                <h3 class="text-[13px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    HUB Acesso Rápido
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </h3>

                <div class="space-y-4">
                    @can('secretaria')
                        <a href="{{ route('desbravadores.create') }}" class="group flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700/60 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-800 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-[#002F6C] dark:text-blue-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-black text-slate-800 dark:text-slate-200 leading-none mb-1">Novo Membro</span>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Secretaria</span>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-300 group-hover:bg-[#002F6C] group-hover:text-white dark:group-hover:bg-blue-600 transition-all">
                                <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </a>
                    @endcan

                    @can('financeiro')
                        <a href="{{ route('caixa.create') }}" class="group flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700/60 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-black text-slate-800 dark:text-slate-200 leading-none mb-1">Entrada no Caixa</span>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tesouraria</span>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-300 group-hover:bg-emerald-500 group-hover:text-white dark:group-hover:bg-emerald-600 transition-all">
                                <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </a>
                    @endcan

                    @can('pedagogico')
                        <a href="{{ route('classes.index') }}" class="group flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700/60 hover:shadow-md hover:border-purple-200 dark:hover:border-purple-800 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-black text-slate-800 dark:text-slate-200 leading-none mb-1">Evolução Classes</span>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pedagógico</span>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-300 group-hover:bg-purple-600 group-hover:text-white transition-all">
                                <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </div>
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
                            pointBackgroundColor: '#D9222A', // dbv-red para destacar os pontos no dash novo
                            pointBorderColor: '#FFFFFF',
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
                                    label: function(context) { return context.parsed.y + '% de Frequência'; }
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
