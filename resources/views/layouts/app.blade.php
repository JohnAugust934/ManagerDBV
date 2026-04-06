<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    sidebarOpen: false,
    sidebarPinned: localStorage.getItem('sidebarPinned') !== 'false',
    sidebarHover: false,
    desktopSidebarExpanded() {
        return this.sidebarPinned || this.sidebarHover;
    },
    toggleSidebarPinned() {
        this.sidebarPinned = !this.sidebarPinned;
        localStorage.setItem('sidebarPinned', this.sidebarPinned? 'true' : 'false');
        if (this.sidebarPinned) {
            this.sidebarHover = false;
        }
    }
}" x-init="$watch('darkMode', val => localStorage.setItem('theme', val? 'dark' : 'light'))"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Auth::user()->club->nome?? config('app.name', 'Desbravadores Manager') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <style>
        .manual-capture *,
        .manual-capture *::before,
        .manual-capture *::after {
            animation: none !important;
            transition: none !important;
        }

        @media (min-width: 768px) {
            aside.sidebar-collapsed .sidebar-brand,
            aside.sidebar-collapsed .sidebar-heading,
            aside.sidebar-collapsed .sidebar-chevron,
            aside.sidebar-collapsed .sidebar-footer-text {
                opacity: 0;
                width: 0;
                overflow: hidden;
                pointer-events: none;
            }

            aside.sidebar-collapsed .sidebar-submenu {
                display: none;
            }

            aside.sidebar-collapsed nav a,
            aside.sidebar-collapsed nav button {
                justify-content: center;
                font-size: 0;
                line-height: 0;
                transform: none !important;
            }

            aside.sidebar-collapsed nav svg,
            aside.sidebar-collapsed .sidebar-footer svg {
                margin-right: 0 !important;
            }

            aside.sidebar-collapsed .sidebar-footer {
                justify-content: center;
                gap: 0;
            }

            aside.sidebar-collapsed .sidebar-footer > div:first-child {
                display: none;
            }

            aside.sidebar-collapsed .sidebar-footer form {
                margin: 0 auto;
            }

            aside.sidebar-collapsed .sidebar-footer button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                padding: 0;
            }
        }
    </style>
</head>

<body
    class="font-sans antialiased {{ request()->boolean('manual_capture')? 'manual-capture' : '' }}">
    <a href="#app-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] bg-white text-slate-900 px-3 py-2 rounded-lg shadow">
        Pular para o conteudo
    </a>

    <div class="flex h-screen overflow-hidden">

        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-gray-900/80 backdrop-blur-sm md:hidden" x-cloak></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 bg-gradient-to-b from-dbv-blue to-blue-950 dark:from-slate-900 dark:to-black text-white transition-all duration-300 transform shadow-2xl flex flex-col md:static md:translate-x-0 border-r border-blue-800/50 dark:border-slate-800/80"
            :class="[sidebarOpen? 'translate-x-0' : '-translate-x-full', desktopSidebarExpanded()? 'sidebar-expanded md:w-64' : 'sidebar-collapsed md:w-20']"
            @mouseenter="if (window.innerWidth >= 768 && !sidebarPinned) sidebarHover = true"
            @mouseleave="if (window.innerWidth >= 768) sidebarHover = false">

            <div
                class="flex items-center px-5 h-20 bg-black/10 border-b border-white/5 gap-4 shrink-0 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/5 to-transparent pointer-events-none"></div>
                @if (Auth::user()->club && Auth::user()->club->logo)
                    <img src="{{ asset('storage/' . Auth::user()->club->logo) }}"
                        class="h-11 w-11 rounded-xl object-cover border-[1.5px] border-dbv-yellow/80 shadow-md relative z-10"
                        alt="Logo">
                @else
                    <div
                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-dbv-red to-red-600 flex items-center justify-center font-black text-white border border-white/20 shadow-md relative z-10">
                        DBV
                    </div>
                @endif

                <div class="sidebar-brand flex flex-col overflow-hidden relative z-10 transition-all duration-200">
                    <a href="{{ route('dashboard') }}"
                        class="font-black tracking-wide text-[15px] text-white truncate leading-tight hover:text-dbv-yellow transition-colors">
                        {{ Auth::user()->club->nome?? 'MANAGER' }}
                    </a>
                    <span class="text-[10px] text-blue-200/70 uppercase tracking-widest font-bold mt-0.5">
                        {{ Auth::user()->role === 'master'? 'Master Admin' : 'Sistema de Gestao' }}
                    </span>
                </div>
                <button type="button" @click="toggleSidebarPinned()"
                    class="hidden md:inline-flex items-center justify-center ml-auto h-9 w-9 rounded-xl border border-white/10 bg-white/10 text-white/80 hover:bg-white/20 hover:text-white transition relative z-10"
                    :title="sidebarPinned? 'Recolher menu lateral' : 'Fixar menu lateral aberto'">
                    <svg class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" :class="desktopSidebarExpanded()? '' : 'rotate-180'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto custom-scrollbar">

                @php
                    $linkClass =
                        'group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-300 relative overflow-hidden whitespace-nowrap';
                    $activeClass =
                        'bg-gradient-to-r from-dbv-red to-red-600 text-white shadow-md shadow-red-900/20 translate-x-1';
                    $inactiveClass = 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1';
                    $iconActiveClass = 'text-white';
                    $iconInactiveClass = 'text-blue-300/70 group-hover:text-blue-200 transition-colors';
                @endphp

                {{-- ================= GERAL ================= --}}
                <p class="sidebar-heading px-3 text-[10px] font-extrabold text-blue-400/60 uppercase tracking-widest mb-2">Geral</p>

                <a href="{{ route('dashboard') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('dashboard')? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard')? $iconActiveClass : $iconInactiveClass }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Painel
                </a>

                @if (Auth::user()->role !== 'instrutor')
                    <a href="{{ route('unidades.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('unidades*')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('unidades*')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Unidades
                    </a>
                @endif

                <div x-data="{ open: {{ request()->routeIs('ranking*')? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-semibold transition-all duration-300 rounded-xl {{ request()->routeIs('ranking*')? 'text-white' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('ranking*')? 'text-white' : 'text-blue-300/70' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Ranking
                        </div>
                        <svg :class="open? 'rotate-180' : ''"
                            class="sidebar-chevron w-4 h-4 transition-transform duration-300 text-blue-400/70" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="sidebar-submenu pl-11 pr-3 mt-1 space-y-1">
                        <a href="{{ route('ranking.unidades') }}"
                            class="block px-3 py-2 text-sm transition-all rounded-lg {{ request()->routeIs('ranking.unidades')? 'text-white font-bold bg-white/10 shadow-sm' : 'text-blue-200/70 hover:text-white hover:bg-white/5' }}">Por
                            Unidade</a>
                        <a href="{{ route('ranking.desbravadores') }}"
                            class="block px-3 py-2 text-sm transition-all rounded-lg {{ request()->routeIs('ranking.desbravadores')? 'text-white font-bold bg-white/10 shadow-sm' : 'text-blue-200/70 hover:text-white hover:bg-white/5' }}">Por
                            Desbravador</a>
                    </div>
                </div>

                {{-- ================= SECRETARIA ================= --}}
                @can('secretaria')
                    <p class="sidebar-heading px-3 mt-8 text-[10px] font-extrabold text-blue-400/60 uppercase tracking-widest mb-2">
                        Secretaria</p>

                    <a href="{{ route('club.edit') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('club.edit')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('club.edit')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Meu Clube
                    </a>

                    <a href="{{ route('desbravadores.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('desbravadores*')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('desbravadores*')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Desbravadores
                    </a>

                    <div x-data="{ open: {{ request()->routeIs('atas*') || request()->routeIs('atos*')? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-semibold transition-all duration-300 rounded-xl {{ request()->routeIs('atas*') || request()->routeIs('atos*')? 'text-white' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('atas*') || request()->routeIs('atos*')? 'text-white' : 'text-blue-300/70' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Documentos
                            </div>
                            <svg :class="open? 'rotate-180' : ''"
                                class="sidebar-chevron w-4 h-4 transition-transform duration-300 text-blue-400/70" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="sidebar-submenu pl-11 pr-3 mt-1 space-y-1">
                            <a href="{{ route('atas.index') }}"
                                class="block px-3 py-2 text-sm transition-all rounded-lg {{ request()->routeIs('atas*')? 'text-white font-bold bg-white/10 shadow-sm' : 'text-blue-200/70 hover:text-white hover:bg-white/5' }}">Atas</a>
                            <a href="{{ route('atos.index') }}"
                                class="block px-3 py-2 text-sm transition-all rounded-lg {{ request()->routeIs('atos*')? 'text-white font-bold bg-white/10 shadow-sm' : 'text-blue-200/70 hover:text-white hover:bg-white/5' }}">Atos
                                Oficiais</a>
                        </div>
                    </div>
                @endcan

                {{-- ================= EVENTOS ================= --}}
                @can('eventos')
                    <p class="sidebar-heading px-3 mt-8 text-[10px] font-extrabold text-blue-400/60 uppercase tracking-widest mb-2">Agenda
                    </p>
                    <a href="{{ route('eventos.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('eventos*')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('eventos*')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Eventos
                    </a>
                @endcan

                {{-- ================= PEDAGÓGICO ================= --}}
                @can('pedagogico')
                    <p class="sidebar-heading px-3 mt-8 text-[10px] font-extrabold text-blue-400/60 uppercase tracking-widest mb-2">
                        Pedagógico</p>

                    <a href="{{ route('classes.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('classes*')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('classes*')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Classes
                    </a>

                    <a href="{{ route('especialidades.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('especialidades*')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('especialidades*')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Especialidades
                    </a>

                    <div x-data="{ open: {{ request()->routeIs('frequencia*')? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-semibold transition-all duration-300 rounded-xl {{ request()->routeIs('frequencia*')? 'text-white' : 'text-blue-100/70 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('frequencia*')? 'text-white' : 'text-blue-300/70' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Frequência
                            </div>
                            <svg :class="open? 'rotate-180' : ''"
                                class="sidebar-chevron w-4 h-4 transition-transform duration-300 text-blue-400/70" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="sidebar-submenu pl-11 pr-3 mt-1 space-y-1">
                            <a href="{{ route('frequencia.index') }}"
                                class="block px-3 py-2 text-sm transition-all rounded-lg {{ request()->routeIs('frequencia.index')? 'text-white font-bold bg-white/10 shadow-sm' : 'text-blue-200/70 hover:text-white hover:bg-white/5' }}">Histórico
                                Mensal</a>
                            <a href="{{ route('frequencia.create') }}"
                                class="block px-3 py-2 text-sm transition-all rounded-lg {{ request()->routeIs('frequencia.create')? 'text-white font-bold bg-white/10 shadow-sm' : 'text-blue-200/70 hover:text-white hover:bg-white/5' }}">Nova
                                Chamada</a>
                        </div>
                    </div>
                @endcan

                {{-- ================= FINANCEIRO ================= --}}
                @can('financeiro')
                    <p class="sidebar-heading px-3 mt-8 text-[10px] font-extrabold text-blue-400/60 uppercase tracking-widest mb-2">
                        Financeiro</p>

                    <a href="{{ route('caixa.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('caixa*')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('caixa*')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Caixa
                    </a>

                    <a href="{{ route('mensalidades.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('mensalidades*')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('mensalidades*')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Mensalidades
                    </a>

                    <a href="{{ route('patrimonio.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('patrimonio*')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('patrimonio*')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Patrimônio
                    </a>
                @endcan

                {{-- ================= RELATÓRIOS & SISTEMA ================= --}}
                @can('relatorios')
                    <p class="sidebar-heading px-3 mt-8 text-[10px] font-extrabold text-blue-400/60 uppercase tracking-widest mb-2">
                        Relatórios</p>
                    <a href="{{ route('relatorios.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('relatorios.index')? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('relatorios.index')? $iconActiveClass : $iconInactiveClass }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Relatórios
                    </a>
                @endcan

                <p class="sidebar-heading px-3 mt-8 text-[10px] font-extrabold text-blue-400/60 uppercase tracking-widest mb-2">Sistema
                </p>
                <a href="{{ route('manual.sistema') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('manual.sistema')? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('manual.sistema')? $iconActiveClass : $iconInactiveClass }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Manual do Sistema
                </a>
                <a href="{{ route('sobre') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('sobre')? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('sobre')? $iconActiveClass : $iconInactiveClass }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Sobre o ManagerDBV
                </a>

                {{-- ================= ADMIN MASTER ================= --}}
                @can('master')
                    <div class="mt-8 pt-4 border-t border-white/10">
                        <p class="sidebar-heading px-3 text-[10px] font-extrabold text-red-400/80 uppercase tracking-widest mb-2">Admin
                            Master</p>

                        <a href="{{ route('usuarios.index') }}"
                            class="{{ $linkClass }} {{ request()->routeIs('usuarios*') || request()->routeIs('invites*')? 'bg-red-900/40 text-white shadow-inner ring-1 ring-red-500/30' : 'text-red-300 hover:bg-red-900/20 hover:text-white hover:translate-x-1' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Gestao de Acessos
                        </a>

                        <a href="{{ route('backups.index') }}"
                            class="{{ $linkClass }} {{ request()->routeIs('backups*')? 'bg-red-900/40 text-white shadow-inner ring-1 ring-red-500/30 mt-1.5' : 'text-red-300 hover:bg-red-900/20 hover:text-white hover:translate-x-1 mt-1.5' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Backups & Nuvem
                        </a>
                    </div>
                @endcan
            </nav>

            <div
                class="border-t border-white/10 bg-black/20 p-4 shrink-0 relative overflow-hidden group hover:bg-black/30 transition-colors">
                <div class="sidebar-footer flex items-center gap-3 relative z-10">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-tr from-dbv-red to-red-500 text-white flex items-center justify-center font-bold text-lg shadow-lg border border-white/20 ring-2 ring-transparent group-hover:ring-white/20 transition-all">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="sidebar-footer-text flex-1 overflow-hidden transition-all duration-200">
                        <p class="text-sm font-bold text-white truncate">{{ Str::limit(Auth::user()->name, 15) }}</p>
                        <a href="{{ route('profile.edit') }}"
                            class="text-[11px] font-semibold text-blue-300/80 hover:text-white transition-colors uppercase tracking-wider block mt-0.5">Editar
                            Perfil</a>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="p-2 text-blue-300/70 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-colors"
                            title="Sair do Sistema">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 md:pl-0 transition-all duration-300">

            <header
                class="flex items-center justify-between min-h-16 px-4 sm:px-6 bg-white/95 dark:bg-slate-900/95 border-b border-gray-200 dark:border-slate-700 shadow-sm z-30 sticky top-0 backdrop-blur-sm transition-colors">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="p-2 -ml-2 text-gray-500 hover:text-dbv-blue hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-slate-800 rounded-lg md:hidden focus:outline-none transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1
                        class="text-lg sm:text-xl font-extrabold text-dbv-blue dark:text-blue-200 truncate tracking-tight">
                        {{ $header?? 'Painel de Controle' }}
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="darkMode = !darkMode"
                        class="p-2.5 text-gray-400 hover:text-amber-500 dark:text-gray-400 dark:hover:text-amber-400 transition-colors rounded-full hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none"
                        title="Alternar Tema (Claro/Escuro)">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>
            </header>

            <main id="app-content"
                class="flex-1 overflow-x-hidden overflow-y-auto bg-transparent p-4 sm:p-6 lg:p-8 transition-colors scroll-smooth">

                <div class="mb-6">
                    <x-flash-messages />
                </div>
                <div class="ui-animate-fade-up">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>

</html>



