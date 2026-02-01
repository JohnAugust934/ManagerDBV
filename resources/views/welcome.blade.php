<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Desbravadores Manager') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

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
    class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-dbv-dark-bg selection:bg-dbv-red selection:text-white transition-colors duration-300">

    <nav
        class="fixed w-full z-50 top-0 left-0 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-gray-100 dark:border-slate-800 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-full bg-dbv-red flex items-center justify-center text-white font-bold border-2 border-dbv-yellow shadow-sm">
                        D
                    </div>
                    <span class="font-bold text-xl tracking-tight text-dbv-blue dark:text-white">Manager<span
                            class="text-dbv-red">DBV</span></span>
                </div>

                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="text-sm font-bold text-gray-700 dark:text-gray-300 hover:text-dbv-blue dark:hover:text-white transition">Painel</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="text-sm font-bold text-gray-700 dark:text-gray-300 hover:text-dbv-blue dark:hover:text-white transition">Entrar</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:outline-none transition shadow-lg shadow-blue-900/20">
                                    Cadastrar Clube
                                </a>
                            @endif
                        @endauth
                    @endif

                    <button id="theme-toggle"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-full transition">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z"
                                fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute top-0 left-1/2 w-full -translate-x-1/2 h-full z-0 pointer-events-none">
            <div
                class="absolute top-20 left-10 w-72 h-72 bg-blue-400/20 rounded-full blur-3xl mix-blend-multiply filter opacity-70 animate-blob">
            </div>
            <div
                class="absolute top-20 right-10 w-72 h-72 bg-yellow-400/20 rounded-full blur-3xl mix-blend-multiply filter opacity-70 animate-blob animation-delay-2000">
            </div>
            <div
                class="absolute -bottom-32 left-1/2 w-72 h-72 bg-red-400/20 rounded-full blur-3xl mix-blend-multiply filter opacity-70 animate-blob animation-delay-4000">
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span
                class="inline-block py-1 px-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-bold uppercase tracking-wide mb-6 border border-blue-200 dark:border-blue-800">
                Sistema de Gestão de Clubes
            </span>

            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-6">
                Organize seu clube com <br class="hidden md:block" />
                <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-dbv-blue via-blue-600 to-dbv-red">Excelência
                    e Paixão</span>
            </h1>

            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500 dark:text-gray-400">
                Secretaria, Financeiro, Unidades e Patrimônio em um só lugar. O sistema feito para diretores que querem
                menos papelada e mais aventura.
            </p>

            <div class="mt-10 flex justify-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="px-8 py-4 bg-dbv-blue text-white rounded-xl font-bold text-lg hover:bg-blue-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 flex items-center">
                        Acessar Painel
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="px-8 py-4 bg-dbv-blue text-white rounded-xl font-bold text-lg hover:bg-blue-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-200">
                        Entrar Agora
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="px-8 py-4 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-slate-700 rounded-xl font-bold text-lg hover:bg-gray-50 dark:hover:bg-slate-700 hover:border-gray-300 transition-all duration-200">
                            Criar Conta
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div class="py-20 bg-white dark:bg-slate-800 border-t border-gray-100 dark:border-slate-700/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    class="p-8 rounded-2xl bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-700 hover:border-dbv-blue/30 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-dbv-blue dark:text-blue-400 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Gestão de Membros</h3>
                    <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                        Controle completo de fichas médicas, unidades, cargos e especialidades. Tudo na palma da sua
                        mão.
                    </p>
                </div>

                <div
                    class="p-8 rounded-2xl bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-700 hover:border-green-500/30 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Financeiro Simples</h3>
                    <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                        Caixa do clube, controle de mensalidades e relatórios automáticos. Adeus planilhas confusas.
                    </p>
                </div>

                <div
                    class="p-8 rounded-2xl bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-700 hover:border-yellow-500/30 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600 dark:text-yellow-400 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Secretaria Digital</h3>
                    <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                        Atas, atos oficiais e documentos sempre organizados e acessíveis para a diretoria.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-white dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-400 dark:text-gray-500 text-sm">
                &copy; {{ date('Y') }} Desbravadores Manager. Feito para servir.
            </p>
        </div>
    </footer>

    <script>
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        });
    </script>
</body>

</html>
