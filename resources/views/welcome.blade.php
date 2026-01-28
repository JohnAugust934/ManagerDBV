<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Desbravadores Manager</title>
    <linkpreconnect href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-gray-50">
    <div class="relative min-h-screen flex flex-col justify-center overflow-hidden bg-dbv-blue">

        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
            <div class="absolute -top-[50%] -left-[10%] w-[80%] h-[80%] rounded-full bg-blue-800 opacity-20 blur-3xl"></div>
            <div class="absolute bottom-[10%] -right-[10%] w-[60%] h-[60%] rounded-full bg-dbv-red opacity-10 blur-3xl"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto w-full px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12">

                <div class="lg:w-1/2 text-center lg:text-left">
                    <div class="inline-block px-4 py-2 bg-blue-900 rounded-full border border-blue-700 text-dbv-yellow text-sm font-bold mb-6">
                        SISTEMA DE GESTÃO DE CLUBES v1.0
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-extrabold text-white tracking-tight leading-tight mb-6">
                        Organize seu clube com <span class="text-dbv-yellow">Excelência</span>.
                    </h1>
                    <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto lg:mx-0">
                        Controle financeiro, secretaria, patrimônio e gestão de unidades em um único lugar. Feito para diretores que querem ir além.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        @if (Route::has('login'))
                        @auth
                        <a href="{{ url('/dashboard') }}" class="px-8 py-4 bg-dbv-red hover:bg-red-700 text-white font-bold rounded-lg text-lg transition shadow-lg shadow-red-900/50">
                            Acessar Painel
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="px-8 py-4 bg-white text-dbv-blue hover:bg-gray-100 font-bold rounded-lg text-lg transition shadow-lg">
                            Entrar
                        </a>
                        @endauth
                        @endif
                    </div>
                </div>

                <div class="lg:w-1/2 relative hidden md:block">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-4 translate-y-12">
                            <div class="bg-white p-6 rounded-2xl shadow-xl border-l-4 border-dbv-yellow">
                                <div class="h-8 w-8 bg-yellow-100 rounded-full mb-3"></div>
                                <div class="h-2 w-24 bg-gray-200 rounded mb-2"></div>
                                <div class="h-2 w-16 bg-gray-200 rounded"></div>
                            </div>
                            <div class="bg-white p-6 rounded-2xl shadow-xl border-l-4 border-dbv-red">
                                <div class="h-8 w-8 bg-red-100 rounded-full mb-3"></div>
                                <div class="h-2 w-32 bg-gray-200 rounded mb-2"></div>
                                <div class="h-2 w-20 bg-gray-200 rounded"></div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-white p-6 rounded-2xl shadow-xl border-l-4 border-dbv-blue opacity-90">
                                <div class="h-8 w-8 bg-blue-100 rounded-full mb-3"></div>
                                <div class="h-2 w-28 bg-gray-200 rounded mb-2"></div>
                                <div class="h-2 w-12 bg-gray-200 rounded"></div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm p-6 rounded-2xl border border-white/20">
                                <div class="h-8 w-8 bg-white/20 rounded-full mb-3"></div>
                                <div class="h-2 w-20 bg-white/20 rounded mb-2"></div>
                                <div class="h-2 w-10 bg-white/20 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>