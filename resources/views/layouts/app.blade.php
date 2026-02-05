<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    sidebarOpen: false
}" x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Auth::user()->club->nome ?? config('app.name', 'Desbravadores Manager') }}</title>

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
</head>

<body
    class="font-sans antialiased bg-gray-50 text-gray-900 dark:bg-dbv-dark-bg dark:text-gray-100 transition-colors duration-300">

    <div class="flex h-screen overflow-hidden">

        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-gray-900/80 backdrop-blur-sm md:hidden" x-cloak></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-dbv-blue dark:bg-slate-900 text-white transition-transform duration-300 transform shadow-2xl flex flex-col md:static md:translate-x-0 border-r border-blue-800 dark:border-slate-800"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            <div
                class="flex items-center px-6 h-20 bg-blue-950/50 dark:bg-black/20 border-b border-blue-800/50 dark:border-slate-800 gap-3 shrink-0">
                @if (Auth::user()->club && Auth::user()->club->logo)
                    <img src="{{ asset('storage/' . Auth::user()->club->logo) }}"
                        class="h-10 w-10 rounded-full object-cover border-2 border-dbv-yellow shadow-md" alt="Logo">
                @else
                    <div
                        class="h-10 w-10 rounded-full bg-dbv-red flex items-center justify-center font-bold text-white border-2 border-white">
                        DBV
                    </div>
                @endif

                <div class="flex flex-col overflow-hidden">
                    <a href="{{ route('dashboard') }}"
                        class="font-bold tracking-wide text-sm text-white truncate leading-tight hover:text-dbv-yellow transition">
                        {{ Auth::user()->club->nome ?? 'MANAGER' }}
                    </a>
                    <span class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold">
                        {{ Auth::user()->role === 'master' ? 'Master Admin' : 'Sistema de Gestão' }}
                    </span>
                </div>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto custom-scrollbar">

                @php
                    $linkClass =
                        'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200';
                    $activeClass = 'bg-dbv-red text-white shadow-lg translate-x-1';
                    $inactiveClass = 'text-blue-100 hover:bg-white/10 hover:text-white hover:translate-x-1';
                @endphp

                {{-- ================= GERAL ================= --}}
                <p class="px-3 text-[10px] font-bold text-blue-300/80 uppercase tracking-wider mb-2">Geral</p>

                {{-- 1. Painel --}}
                <a href="{{ route('dashboard') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Painel
                </a>

                {{-- 2. Ranking (Dropdown) --}}
                <div x-data="{ open: {{ request()->routeIs('ranking*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-blue-100 transition-colors duration-200 rounded-lg hover:bg-white/10 hover:text-white">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('ranking*') ? 'text-white' : 'text-blue-300' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Ranking
                        </div>
                        <svg :class="open ? 'rotate-180' : ''"
                            class="w-4 h-4 transition-transform duration-200 text-blue-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="pl-11 pr-3 mt-1 space-y-1">
                        <a href="{{ route('ranking.unidades') }}"
                            class="block px-3 py-2 text-sm text-blue-200 transition-colors rounded-md hover:text-white hover:bg-white/5 {{ request()->routeIs('ranking.unidades') ? 'text-white bg-white/10' : '' }}">
                            Por Unidade
                        </a>
                        <a href="{{ route('ranking.desbravadores') }}"
                            class="block px-3 py-2 text-sm text-blue-200 transition-colors rounded-md hover:text-white hover:bg-white/5 {{ request()->routeIs('ranking.desbravadores') ? 'text-white bg-white/10' : '' }}">
                            Por Desbravador
                        </a>
                    </div>
                </div>

                {{-- ================= SECRETARIA ================= --}}
                @can('secretaria')
                    <p class="px-3 mt-6 text-[10px] font-bold text-blue-300/80 uppercase tracking-wider mb-2">Secretaria</p>

                    {{-- 1. Meu Clube --}}
                    <a href="{{ route('club.edit') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('club.edit') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('club.edit') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Meu Clube
                    </a>

                    {{-- 2. Desbravadores --}}
                    <a href="{{ route('desbravadores.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('desbravadores*') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('desbravadores*') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Desbravadores
                    </a>

                    {{-- 3. Unidades --}}
                    <a href="{{ route('unidades.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('unidades*') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('unidades*') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Unidades
                    </a>

                    {{-- 4. Eventos (MOVIDO PARA CÁ) --}}
                    <a href="{{ route('eventos.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('eventos*') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('eventos*') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Eventos
                    </a>

                    {{-- 5. Frequência (Dropdown) --}}
                    <div x-data="{ open: {{ request()->routeIs('frequencia*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-blue-100 transition-colors duration-200 rounded-lg hover:bg-white/10 hover:text-white">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('frequencia*') ? 'text-white' : 'text-blue-300' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Frequência
                            </div>
                            <svg :class="open ? 'rotate-180' : ''"
                                class="w-4 h-4 transition-transform duration-200 text-blue-300" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-11 pr-3 mt-1 space-y-1">
                            {{-- Opção 1: Histórico (Padrão) --}}
                            <a href="{{ route('frequencia.index') }}"
                                class="block px-3 py-2 text-sm text-blue-200 transition-colors rounded-md hover:text-white hover:bg-white/5 {{ request()->routeIs('frequencia.index') ? 'text-white bg-white/10' : '' }}">
                                Histórico Mensal
                            </a>

                            {{-- Opção 2: Nova Chamada --}}
                            <a href="{{ route('frequencia.create') }}"
                                class="block px-3 py-2 text-sm text-blue-200 transition-colors rounded-md hover:text-white hover:bg-white/5 {{ request()->routeIs('frequencia.create') ? 'text-white bg-white/10' : '' }}">
                                Nova Chamada
                            </a>
                        </div>
                    </div>

                    {{-- 6. Documentos --}}
                    <div x-data="{ open: {{ request()->routeIs('atas*') || request()->routeIs('atos*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-blue-100 transition-colors duration-200 rounded-lg hover:bg-white/10 hover:text-white">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 text-blue-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Documentos
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-11 pr-3 mt-1 space-y-1">
                            <a href="{{ route('atas.index') }}"
                                class="block px-3 py-2 text-sm text-blue-200 transition-colors rounded-md hover:text-white hover:bg-white/5 {{ request()->routeIs('atas*') ? 'text-white bg-white/10' : '' }}">Atas</a>
                            <a href="{{ route('atos.index') }}"
                                class="block px-3 py-2 text-sm text-blue-200 transition-colors rounded-md hover:text-white hover:bg-white/5 {{ request()->routeIs('atos*') ? 'text-white bg-white/10' : '' }}">Atos
                                Oficiais</a>
                        </div>
                    </div>
                @endcan


                {{-- ================= FINANCEIRO ================= --}}
                @can('financeiro')
                    <p class="px-3 mt-6 text-[10px] font-bold text-blue-300/80 uppercase tracking-wider mb-2">Financeiro
                    </p>

                    {{-- 1. Caixa --}}
                    <a href="{{ route('caixa.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('caixa*') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('caixa*') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Caixa
                    </a>

                    {{-- 2. Mensalidades --}}
                    <a href="{{ route('mensalidades.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('mensalidades*') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('mensalidades*') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Mensalidades
                    </a>

                    {{-- 3. Patrimônio --}}
                    <a href="{{ route('patrimonio.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('patrimonio*') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('patrimonio*') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Patrimônio
                    </a>
                @endcan


                {{-- ================= PEDAGÓGICO ================= --}}
                @can('pedagogico')
                    <p class="px-3 mt-6 text-[10px] font-bold text-blue-300/80 uppercase tracking-wider mb-2">Pedagógico
                    </p>

                    {{-- 1. Classes --}}
                    <a href="{{ route('classes.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('classes*') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('classes*') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Classes
                    </a>

                    {{-- 2. Especialidades --}}
                    <a href="{{ route('especialidades.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('especialidades*') ? $activeClass : $inactiveClass }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('especialidades*') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Especialidades
                    </a>
                @endcan

                {{-- ================= RELATÓRIOS ================= --}}
                <p class="px-3 mt-6 text-[10px] font-bold text-blue-300/80 uppercase tracking-wider mb-2">Relatórios
                </p>
                <a href="{{ route('relatorios.index') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('relatorios.index') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('relatorios.index') ? 'text-white' : 'text-blue-300 group-hover:text-white' }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Relatórios
                </a>

                {{-- ================= ADMIN MASTER ================= --}}
                @can('master')
                    <div class="mt-8 pt-4 border-t border-white/10">
                        <p class="px-3 text-[10px] font-bold text-red-400 uppercase tracking-wider mb-2">Admin Master</p>
                        <a href="{{ route('usuarios.index') }}"
                            class="{{ $linkClass }} {{ request()->routeIs('usuarios*') ? 'bg-red-900/50 text-white' : 'text-red-300 hover:bg-red-900/30 hover:text-white' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Gestão de Acessos
                        </a>
                    </div>
                @endcan
            </nav>

            <div class="border-t border-blue-800 dark:border-slate-800 bg-blue-950 dark:bg-slate-900 p-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-tr from-dbv-red to-red-500 text-white flex items-center justify-center font-bold text-lg shadow-lg border border-white/20">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-bold text-white truncate">{{ Str::limit(Auth::user()->name, 15) }}</p>
                        <a href="{{ route('profile.edit') }}"
                            class="text-[11px] text-blue-300 hover:text-white transition">Editar Perfil</a>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="p-2 text-blue-300 hover:text-red-400 hover:bg-white/5 rounded-full transition"
                            title="Sair">
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
                class="flex items-center justify-between h-16 px-4 bg-white dark:bg-dbv-dark-surface border-b border-gray-200 dark:border-slate-700 shadow-sm z-30 sticky top-0 transition-colors">

                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="p-2 -ml-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-md md:hidden focus:outline-none focus:ring-2 focus:ring-inset focus:ring-dbv-blue">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <h1 class="text-lg font-bold text-dbv-blue dark:text-gray-100 truncate">
                        {{ $header ?? 'Painel de Controle' }}
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="darkMode = !darkMode"
                        class="p-2 text-gray-400 hover:text-yellow-500 dark:text-gray-400 dark:hover:text-yellow-400 transition-colors rounded-full hover:bg-gray-100 dark:hover:bg-slate-700"
                        title="Alternar Tema">
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

            <main
                class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-dbv-dark-bg p-4 md:p-6 transition-colors scroll-smooth">

                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
                        class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 flex items-start justify-between shadow-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="font-bold text-sm text-green-800 dark:text-green-300">Sucesso!</p>
                                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
                            </div>
                        </div>
                        <button @click="show = false"
                            class="text-green-500 hover:text-green-700 dark:hover:text-green-200 transition">&times;</button>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                        class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 flex items-start justify-between shadow-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="font-bold text-sm text-red-800 dark:text-red-300">Atenção!</p>
                                <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
                            </div>
                        </div>
                        <button @click="show = false"
                            class="text-red-500 hover:text-red-700 dark:hover:text-red-200 transition">&times;</button>
                    </div>
                @endif

                <div class="animate-fade-in">
                    {{ $slot }}
                </div>

            </main>
        </div>
    </div>
</body>

</html>
