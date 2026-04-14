<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased" x-data="{
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    sidebarOpen: false,
    mobileMenuOpen: false
}" x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#002F6C">

    <title>{{ Auth::user()->club->nome ?? config('app.name', 'Desbravadores Manager') }}</title>

    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>

<body class="font-sans antialiased text-slate-800 dark:text-slate-200" style="margin:0; padding:0; background: rgb(var(--ui-bg));">
    <a href="#app-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] ui-btn-primary">
        Pular para o conteúdo
    </a>

    <!-- Top Glow Effect (Desktop) -->
    <div class="fixed top-0 left-0 w-full h-[500px] bg-gradient-to-b from-[#002F6C]/5 dark:from-blue-900/10 to-transparent pointer-events-none z-0"></div>

    <div class="relative z-10 flex h-screen overflow-hidden">

        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen || mobileMenuOpen" 
             x-transition.opacity.duration.300ms
             @click="sidebarOpen = false; mobileMenuOpen = false"
             class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden" x-cloak></div>

        <!-- Sidebar Navigation -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-[280px] m-4 md:m-6 ui-glass rounded-[32px] overflow-hidden flex flex-col transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] shadow-2xl shadow-blue-900/5 dark:shadow-black/50 lg:translate-x-0 lg:static lg:shrink-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-[150%]'"
            style="height: calc(100vh - 2rem)">
            
            <!-- Branding -->
            <div class="px-6 py-8 flex items-center gap-4 border-b border-black/5 dark:border-white/5 relative shrink-0">
                <!-- Club Logo -->
                @if (Auth::user()->club && Auth::user()->club->logo)
                    <img src="{{ asset('storage/' . Auth::user()->club->logo) }}" class="w-12 h-12 rounded-2xl object-cover shadow-sm border border-black/5 dark:border-white/10" alt="Logo">
                @else
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#002F6C] to-[#001D42] p-2.5 shadow-lg shadow-blue-900/20 flex items-center justify-center">
                        <img src="{{ asset('favicon.svg') }}" alt="ManagerDBV" class="w-full h-full object-contain filter drop-shadow">
                    </div>
                @endif
                
                <div class="flex flex-col">
                    <h1 class="font-black text-[17px] text-slate-800 dark:text-white leading-tight uppercase tracking-wide text-gradient-dbv">
                        {{ Str::limit(Auth::user()->club->nome ?? 'MANAGER', 15) }}
                    </h1>
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">
                        {{ Auth::user()->role === 'master' ? 'Master Admin' : 'Sistema de Gestão' }}
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto custom-scrollbar space-y-1">
                @php
                    $linkBase = 'flex items-center gap-3.5 px-4 py-3 rounded-2xl text-[14px] font-bold transition-all duration-300 relative group overflow-hidden';
                    $activeClass = 'bg-gradient-to-r from-[#002F6C]/10 to-transparent dark:from-blue-500/10 text-[#002F6C] dark:text-blue-400';
                    $inactiveClass = 'text-slate-500 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white hover:bg-slate-100/50 dark:hover:bg-slate-800/50';
                    $iconActive = 'text-[#D9222A] dark:text-red-400 drop-shadow-sm';
                    $iconInactive = 'text-slate-400 group-hover:text-[#D9222A]/70 dark:text-slate-500 transition-colors';
                @endphp

                <p class="px-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Visão Geral</p>

                <a href="{{ route('dashboard') }}" class="{{ $linkBase }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                    @if(request()->routeIs('dashboard')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    Painel
                </a>

                @can('secretaria')
                <!-- ACORDEON SECRETARIA -->
                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Secretaria & Clube</p>
                </div>
                
                <a href="{{ route('unidades.index') }}" class="{{ $linkBase }} {{ request()->routeIs('unidades*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('unidades*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('unidades*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Unidades
                </a>

                <a href="{{ route('desbravadores.index') }}" class="{{ $linkBase }} {{ request()->routeIs('desbravadores*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('desbravadores*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('desbravadores*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Desbravadores
                </a>

                <a href="{{ route('club.edit') }}" class="{{ $linkBase }} {{ request()->routeIs('club.edit') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('club.edit')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('club.edit') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    Meu Clube
                </a>
                
                <a href="{{ route('atas.index') }}" class="{{ $linkBase }} {{ request()->routeIs('atas*') || request()->routeIs('atos*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('atas*') || request()->routeIs('atos*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('atas*') || request()->routeIs('atos*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Atas e Atos
                </a>
                @endcan

                @can('pedagogico')
                <!-- ACORDEON PEDAGOGICO -->
                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Pedagógico</p>
                </div>

                <a href="{{ route('frequencia.index') }}" class="{{ $linkBase }} {{ request()->routeIs('frequencia*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('frequencia*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('frequencia*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Frequência Mensal
                </a>
                
                <a href="{{ route('classes.index') }}" class="{{ $linkBase }} {{ request()->routeIs('classes*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('classes*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('classes*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    Classes Regulares
                </a>

                <a href="{{ route('especialidades.index') }}" class="{{ $linkBase }} {{ request()->routeIs('especialidades*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('especialidades*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('especialidades*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Especialidades
                </a>
                
                <a href="{{ route('ranking.desbravadores') }}" class="{{ $linkBase }} {{ request()->routeIs('ranking*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('ranking*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('ranking*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    Rankings
                </a>
                @endcan

                @can('eventos')
                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Eventos</p>
                </div>
                <a href="{{ route('eventos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('eventos*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('eventos*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('eventos*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Calendário
                </a>
                @endcan

                @can('financeiro')
                <!-- ACORDEON FINANCEIRO -->
                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Finanças & Bens</p>
                </div>
                
                <a href="{{ route('mensalidades.index') }}" class="{{ $linkBase }} {{ request()->routeIs('mensalidades*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('mensalidades*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('mensalidades*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Mensalidades
                </a>
                <a href="{{ route('caixa.index') }}" class="{{ $linkBase }} {{ request()->routeIs('caixa*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('caixa*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('caixa*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    Fluxo de Caixa
                </a>
                <a href="{{ route('patrimonio.index') }}" class="{{ $linkBase }} {{ request()->routeIs('patrimonio*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('patrimonio*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('patrimonio*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    Patrimônio
                </a>
                @endcan

                @if (auth()->user()->can('gestao-acessos') || auth()->user()->can('master') || auth()->user()->can('relatorios'))
                <!-- ACORDEON ADMIN -->
                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Avançado</p>
                </div>
                @endif
                
                @can('relatorios')
                <a href="{{ route('relatorios.index') }}" class="{{ $linkBase }} {{ request()->routeIs('relatorios*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('relatorios*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('relatorios*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    Relatórios
                </a>
                @endcan

                @can('gestao-acessos')
                <a href="{{ route('usuarios.index') }}" class="{{ $linkBase }} {{ request()->routeIs('usuarios*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('usuarios*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('usuarios*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Usuários
                </a>
                <a href="{{ route('invites.index') }}" class="{{ $linkBase }} {{ request()->routeIs('invites*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('invites*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('invites*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Convites
                </a>
                @endcan
                
                @can('master')
                <a href="{{ route('backups.index') }}" class="{{ $linkBase }} {{ request()->routeIs('backups*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('backups*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('backups*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    Backups e Nuvem
                </a>
                @endcan

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Ajuda</p>
                </div>
                
                <a href="{{ route('manual.sistema') }}" class="{{ $linkBase }} {{ request()->routeIs('manual*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('manual*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('manual*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    Manual do Sistema
                </a>
                
                <a href="{{ route('sobre') }}" class="{{ $linkBase }} {{ request()->routeIs('sobre*') ? $activeClass : $inactiveClass }}">
                     @if(request()->routeIs('sobre*')) <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#D9222A] rounded-r-full"></div> @endif
                    <svg class="w-5 h-5 {{ request()->routeIs('sobre*') ? $iconActive : $iconInactive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Sobre
                </a>

            </nav>

            <!-- Bottom Profile Area -->
            <div class="px-4 py-4 border-t border-black/5 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 shrink-0">
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full font-bold bg-[#D9222A] text-white flex items-center justify-center shrink-0">
                            {{ mb_strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate text-slate-800 dark:text-slate-200">{{ Auth::user()->name }}</p>
                            <a href="{{ route('profile.edit') }}" class="text-[11px] font-bold text-slate-500 hover:text-[#002F6C] dark:hover:text-blue-400">Ver Perfil</a>
                        </div>
                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Sair">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </button>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            
            <!-- Sleek Header -->
            <header class="h-16 sm:h-20 px-4 sm:px-8 mt-2 sm:mt-4 mx-4 md:mx-6 lg:ml-0 ui-glass rounded-[20px] sm:rounded-[28px] shadow-sm flex items-center justify-between sticky top-2 sm:top-4 z-30 shrink-0 border-b-0 border-white/40 dark:border-white/5">
                <div class="flex items-center gap-3 sm:gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-2 text-slate-500 hover:text-[#002F6C] dark:text-slate-400 dark:hover:text-blue-400 focus:outline-none transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                    </button>
                    <h1 class="text-lg sm:text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                        {{ $header ?? 'Gestão DBV' }}
                    </h1>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <button @click="darkMode = !darkMode" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-colors shadow-inner" title="Alternar Tema">
                        <svg x-show="!darkMode" class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main id="app-content" class="flex-1 overflow-x-hidden overflow-y-auto px-4 sm:px-6 md:px-8 pb-32 sm:pb-12 pt-6 transition-all scroll-smooth relative z-20">
                <div class="mb-6 ui-animate-fade-up">
                    <x-flash-messages />
                </div>
                
                <div class="ui-animate-fade-up" style="animation-delay: 100ms;">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Bottom Navigation Bar for Mobile -->
        <div class="lg:hidden fixed bottom-4 inset-x-3 sm:inset-x-6 z-40 ui-glass rounded-[28px] h-20 shadow-2xl flex items-center justify-around px-2 pb-1 border border-white/50 dark:border-white/10">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl {{ request()->routeIs('dashboard') ? 'bg-gradient-to-t from-blue-500/10 to-transparent text-[#002F6C] dark:text-blue-400' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2h-2a2 2 0 01-2-2v-2z" /></svg>
                <span class="text-[10px] font-bold">Início</span>
            </a>
            
            <a href="{{ route('unidades.index') }}" class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl {{ request()->routeIs('unidades*') ? 'bg-gradient-to-t from-blue-500/10 to-transparent text-[#002F6C] dark:text-blue-400' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="text-[10px] font-bold">Unidades</span>
            </a>

            <!-- Frequencia floating central button -->
            <div class="-mt-8">
                <a href="{{ route('frequencia.create') }}" class="w-16 h-16 rounded-full bg-gradient-to-br from-[#D9222A] to-red-500 text-white shadow-xl shadow-red-900/30 flex items-center justify-center transform hover:scale-105 active:scale-95 transition-all border-[3px] border-white dark:border-slate-800">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                </a>
            </div>

            <a href="{{ route('mensalidades.index') }}" class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl {{ request()->routeIs('mensalidades*') ? 'bg-gradient-to-t from-blue-500/10 to-transparent text-[#002F6C] dark:text-blue-400' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-[10px] font-bold">Finanças</span>
            </a>

            <button @click="sidebarOpen = true" class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" /></svg>
                <span class="text-[10px] font-bold">Menu</span>
            </button>
        </div>
    </div>
</body>
</html>
