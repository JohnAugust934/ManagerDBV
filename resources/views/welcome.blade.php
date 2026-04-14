<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Desbravadores Manager — O sistema de gestão definitivo para Clubes de Desbravadores. Secretaria, Financeiro, Frequência e Pedagogia em um só lugar.">
    <title>ManagerDBV — Gestão de Clubes de Desbravadores</title>

    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }

        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }
        @keyframes blob  { 0%,100%{border-radius:60% 40% 30% 70%/60% 30% 70% 40%} 50%{border-radius:30% 60% 70% 40%/50% 60% 30% 60%} }
        @keyframes fadeUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        @keyframes pulse-slow { 0%,100%{opacity:.6} 50%{opacity:.25} }

        .animate-float    { animation: float 6s ease-in-out infinite; }
        .animate-blob     { animation: blob 8s ease-in-out infinite; }
        .animate-fade-up  { animation: fadeUp .6s ease both; }
        .animate-pulse-slow { animation: pulse-slow 6s ease-in-out infinite; }

        .grad-text {
            background: linear-gradient(135deg, #60a5fa 0%, #ffffff 50%, #fcd116 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-card:hover .feature-icon { transform: scale(1.1) rotate(-6deg); }
        .feature-card { transition: all .3s cubic-bezier(.4,0,.2,1); }
        .feature-card:hover { transform: translateY(-4px); }

        /* Nav glass */
        .nav-glass {
            background: rgba(0,29,66,0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        /* Scroll bar hidden */
        html { scrollbar-width: none; }
        html::-webkit-scrollbar { display:none; }
    </style>
</head>

<body class="bg-[#001229] text-white antialiased overflow-x-hidden">

    {{-- ════════════════════════════════════════
         FUNDO ANIMADO
    ════════════════════════════════════════ --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-20%] right-[-10%] w-[700px] h-[700px] rounded-full bg-[#002F6C] blur-[130px] opacity-50 animate-pulse-slow"></div>
        <div class="absolute bottom-[-15%] left-[-5%] w-[600px] h-[600px] rounded-full bg-[#D9222A] blur-[130px] opacity-20 animate-pulse-slow" style="animation-delay:3s"></div>
        <div class="absolute top-[40%] left-[30%] w-[400px] h-[400px] rounded-full bg-[#FCD116] blur-[160px] opacity-[0.07]"></div>
        {{-- Grid --}}
        <div class="absolute inset-0 opacity-[0.04]"
            style="background-image:linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);background-size:60px 60px"></div>
    </div>

    {{-- ════════════════════════════════════════
         NAVBAR
    ════════════════════════════════════════ --}}
    <header class="fixed top-0 inset-x-0 z-50 nav-glass">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 h-16 flex items-center justify-between">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center p-1.5 transition-all group-hover:bg-white/20">
                    <img src="{{ asset('favicon.svg') }}" alt="DBV" class="w-full h-full object-contain">
                </div>
                <span class="text-lg font-black tracking-tight text-white">Manager<span class="text-[#FCD116]">DBV</span></span>
            </a>


        </div>
    </header>

    {{-- ════════════════════════════════════════
         HERO
    ════════════════════════════════════════ --}}
    <section class="relative z-10 min-h-screen flex flex-col items-center justify-center text-center px-5 pt-24 pb-16">

        {{-- Badge --}}
        <div class="animate-fade-up inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[#FCD116] text-xs font-black uppercase tracking-widest mb-8">
            <span class="w-2 h-2 rounded-full bg-[#FCD116] animate-pulse inline-block"></span>
            Sistema de Gestão para Clubes de Desbravadores
        </div>

        {{-- Headline --}}
        <h1 class="animate-fade-up text-5xl sm:text-6xl md:text-7xl xl:text-8xl font-black tracking-tight leading-[1.05] mb-6" style="animation-delay:.1s">
            Organize. <span class="grad-text">Engaje.</span><br>
            <span class="grad-text">Inspire.</span>
        </h1>

        {{-- Sub --}}
        <p class="animate-fade-up text-lg sm:text-xl text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed mb-10" style="animation-delay:.2s">
            Do cadastro de membros ao fluxo de caixa — tudo o que um Clube de Desbravadores precisa
            para funcionar com <strong class="text-white font-bold">excelência e paixão</strong>, em um único sistema.
        </p>

        {{-- Ação --}}
        <div class="animate-fade-up flex flex-col sm:flex-row items-center gap-4" style="animation-delay:.3s">
            @auth
                <a href="{{ url('/dashboard') }}"
                    class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-[#D9222A] to-red-500 hover:from-red-500 hover:to-[#D9222A] text-white font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-red-900/40 transition-all active:scale-95 flex items-center justify-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Ir para o Painel
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-[#D9222A] to-red-500 hover:from-red-500 hover:to-[#D9222A] text-white font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-red-900/40 transition-all active:scale-95 flex items-center justify-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Entrar no Sistema
                </a>
                <a href="#recursos" class="w-full sm:w-auto px-8 py-4 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-black uppercase tracking-widest rounded-2xl transition-all text-sm flex items-center justify-center gap-2">
                    Ver Recursos
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
            @endauth
        </div>

        {{-- Stats flutuantes --}}
        <div class="animate-fade-up mt-16 grid grid-cols-3 gap-4 sm:gap-8 max-w-lg mx-auto" style="animation-delay:.4s">
            @foreach([['100%', 'Gratuito'], ['Mobile', 'Primeiro'], ['Multi', 'Usuário']] as $s)
            <div class="flex flex-col items-center">
                <span class="text-2xl sm:text-3xl font-black text-white">{{ $s[0] }}</span>
                <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-widest mt-0.5">{{ $s[1] }}</span>
            </div>
            @endforeach
        </div>

        {{-- Seta scroll --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-float opacity-30">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </section>

    {{-- ════════════════════════════════════════
         RECURSOS
    ════════════════════════════════════════ --}}
    <section id="recursos" class="relative z-10 py-24 px-5 sm:px-8">
        <div class="max-w-7xl mx-auto">

            <div class="text-center mb-16">
                <p class="text-[#FCD116] text-xs font-black uppercase tracking-widest mb-3">Tudo em um Lugar</p>
                <h2 class="text-4xl sm:text-5xl font-black text-white tracking-tight">Menos papelada,<br>mais aventura.</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                $features = [
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'blue', 'title' => 'Gestão de Membros', 'desc' => 'Fichas completas, unidades, especialidades e classes. Tudo organizado por Desbravador.', 'bg' => 'from-blue-600/20 to-blue-900/5', 'icon_bg' => 'bg-blue-500/20 text-blue-400'],
                    ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald', 'title' => 'Financeiro Completo', 'desc' => 'Caixa, mensalidades, carnês e relatórios. Adeus às planilhas confusas.', 'bg' => 'from-emerald-600/20 to-emerald-900/5', 'icon_bg' => 'bg-emerald-500/20 text-emerald-400'],
                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'red', 'title' => 'Frequência Mobile', 'desc' => 'Chamada com cards e toggles grandes. Feito para usar com uma mão durante as reuniões.', 'bg' => 'from-red-600/20 to-red-900/5', 'icon_bg' => 'bg-red-500/20 text-red-400'],
                    ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'purple', 'title' => 'Módulo Pedagógico', 'desc' => 'Acompanhe classes, requisitos e investiduras. Progresso de cada membro em tempo real.', 'bg' => 'from-purple-600/20 to-purple-900/5', 'icon_bg' => 'bg-purple-500/20 text-purple-400'],
                    ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'amber', 'title' => 'Secretaria Digital', 'desc' => 'Atas, atos e documentos sempre organizados. Geração de PDFs em um clique.', 'bg' => 'from-amber-600/20 to-amber-900/5', 'icon_bg' => 'bg-amber-500/20 text-amber-400'],
                    ['icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'cyan', 'title' => 'Patrimônio', 'desc' => 'Inventário de equipamentos, barracas e almoxarifado com controle de estado e valor.', 'bg' => 'from-cyan-600/20 to-cyan-900/5', 'icon_bg' => 'bg-cyan-500/20 text-cyan-400'],
                ];
                @endphp

                @foreach($features as $i => $f)
                <div class="feature-card relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $f['bg'] }} border border-white/5 p-6 sm:p-7" style="animation-delay: {{ $i * 80 }}ms">
                    <div class="feature-icon inline-flex w-12 h-12 rounded-xl {{ $f['icon_bg'] }} items-center justify-center mb-5 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-white mb-2 tracking-tight">{{ $f['title'] }}</h3>
                    <p class="text-sm font-medium text-slate-400 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════
         CTA FINAL
    ════════════════════════════════════════ --}}
    <section class="relative z-10 py-24 px-5 sm:px-8">
        <div class="max-w-3xl mx-auto text-center">
            <div class="relative rounded-[32px] overflow-hidden bg-gradient-to-br from-[#002F6C] to-[#001D42] p-10 sm:p-16 border border-white/10 shadow-2xl">
                <div class="absolute -top-16 -right-16 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-red-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center mx-auto mb-6">
                        <img src="{{ asset('favicon.svg') }}" alt="DBV" class="w-10 h-10 object-contain">
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-4">
                        Pronto para começar?
                    </h2>
                    <p class="text-slate-300 font-medium mb-8 max-w-md mx-auto">
                        Acesse sua conta e tenha todo o controle do clube na palma da sua mão.
                    </p>
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-white hover:bg-blue-50 text-[#002F6C] font-black text-sm uppercase tracking-widest rounded-2xl shadow-xl transition-all active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Ir para o Painel
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-white hover:bg-blue-50 text-[#002F6C] font-black text-sm uppercase tracking-widest rounded-2xl shadow-xl transition-all active:scale-95">
                            Entrar Agora
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════ --}}
    <footer class="relative z-10 py-8 px-5 border-t border-white/5">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center p-1">
                    <img src="{{ asset('favicon.svg') }}" alt="DBV" class="w-full h-full object-contain">
                </div>
                <span class="text-sm font-black text-slate-400">Manager<span class="text-[#FCD116]">DBV</span></span>
            </div>
            <p class="text-xs text-slate-600 font-medium">
                &copy; {{ date('Y') }} Desbravadores Manager — Feito para servir.
            </p>
        </div>
    </footer>

</body>
</html>
