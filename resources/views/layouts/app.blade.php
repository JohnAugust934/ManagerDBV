<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ Auth::user()->club->nome ?? config('app.name', 'Desbravadores Manager') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }"
    style="opacity: 0; transition: opacity 0.3s ease-in-out;" onload="this.style.opacity='1'">

    <div class="flex h-screen overflow-hidden">
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-dbv-blue text-white transition-transform duration-300 transform shadow-xl flex flex-col"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

            <div class="flex items-center px-4 h-20 border-b border-blue-800 bg-blue-900 shadow-md gap-3 shrink-0">
                @if(Auth::user()->club && Auth::user()->club->logo)
                <img src="{{ asset('storage/' . Auth::user()->club->logo) }}"
                    class="h-10 w-10 rounded-full object-cover border-2 border-dbv-yellow"
                    alt="Logo">
                @endif

                <a href="{{ route('dashboard') }}" class="flex flex-col justify-center overflow-hidden">
                    <span class="text-lg font-bold tracking-wider uppercase text-dbv-yellow hover:text-white transition leading-tight truncate">
                        {{ Auth::user()->club->nome ?? 'DBV MANAGER' }}
                    </span>
                    @if(Auth::user()->club)
                    <span class="text-[9px] text-gray-400 font-bold tracking-widest uppercase">
                        Sistema de Gestão
                    </span>
                    @endif
                </a>
            </div>

            <nav class="mt-5 px-4 space-y-2 flex-1 overflow-y-auto custom-scrollbar">

                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Principal</p>

                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-dbv-red text-white shadow-lg translate-x-1' : 'text-gray-300 hover:bg-blue-800 hover:text-white hover:translate-x-1' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Painel Geral
                </a>

                <a href="{{ route('eventos.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('eventos*') ? 'bg-dbv-red text-white shadow-lg translate-x-1' : 'text-gray-300 hover:bg-blue-800 hover:text-white hover:translate-x-1' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Eventos & Acamp.
                </a>

                <p class="px-4 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider">Secretaria</p>

                <a href="{{ route('club.edit') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('club.edit') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Meu Clube
                </a>

                <a href="{{ route('desbravadores.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('desbravadores*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Desbravadores
                </a>
                <a href="{{ route('unidades.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('unidades*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Unidades
                </a>
                <a href="{{ route('atas.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('atas*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Livro de Atas
                </a>
                <a href="{{ route('atos.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('atos*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Atos Oficiais
                </a>

                <p class="px-4 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestão</p>

                <a href="{{ route('caixa.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('caixa*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Fluxo de Caixa
                </a>
                <a href="{{ route('mensalidades.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('mensalidades*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Mensalidades
                </a>
                <a href="{{ route('patrimonio.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('patrimonio*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Patrimônio
                </a>
                <a href="{{ route('especialidades.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('especialidades*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:bg-blue-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    Especialidades
                </a>

                @if(Auth::user()->is_master)
                <p class="px-4 mt-6 text-xs font-semibold text-red-400 uppercase tracking-wider">Sistema</p>
                <a href="{{ route('master.invites') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('master*') ? 'bg-red-800 text-white' : 'text-gray-300 hover:bg-red-900' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Gerar Convites
                </a>
                @endif

            </nav>

            <div class="border-t border-blue-800 bg-blue-900 p-4 shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-dbv-red to-red-500 text-white flex items-center justify-center font-bold text-sm shadow-md border border-red-200 shrink-0">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="text-sm font-bold text-white truncate" title="{{ Auth::user()->name }}">
                                {{ Str::limit(Auth::user()->name, 15) }}
                            </span>
                            <div class="flex gap-2 text-[10px] text-gray-400 mt-0.5">
                                <a href="{{ route('profile.edit') }}" class="hover:text-white transition">Perfil</a>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-400 transition p-1 rounded-full hover:bg-blue-800" title="Sair do Sistema">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col md:pl-64 transition-all duration-300 min-h-screen">

            <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200 shadow-sm z-40 sticky top-0">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none md:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div class="text-xl font-bold text-dbv-blue">
                        {{ $header ?? 'Painel de Controle' }}
                    </div>
                </div>

            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">

                @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                    class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center justify-between" role="alert">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-bold text-sm">Sucesso!</p>
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-green-500 hover:text-green-700 font-bold">&times;</button>
                </div>
                @endif

                @if (session('error'))
                <div x-data="{ show: true }" x-show="show"
                    class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm flex items-center justify-between" role="alert">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-bold text-sm">Atenção!</p>
                            <p class="text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <div x-show="sidebarOpen" @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black bg-opacity-50 md:hidden" style="display: none;"></div>
    </div>
</body>

</html>