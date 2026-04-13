<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="margin:0; padding:0;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DBV Manager') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-100 dark:bg-slate-900 overflow-x-hidden"
    style="margin:0; padding:0; opacity: 0; transition: opacity 0.25s ease-in-out;">

    <div class="min-h-screen flex flex-col lg:flex-row">

        <div class="hidden lg:flex lg:w-1/2 bg-dbv-blue items-center justify-center relative overflow-hidden">
            <div
                class="absolute -top-24 -left-24 w-96 h-96 bg-dbv-yellow rounded-full opacity-20 blur-3xl mix-blend-overlay">
            </div>
            <div
                class="absolute bottom-0 right-0 w-80 h-80 bg-dbv-red rounded-full opacity-20 blur-3xl mix-blend-overlay">
            </div>

            <div class="relative z-10 text-center text-white px-12">
                <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">Salvar do Pecado</h1>
                <p class="text-xl text-gray-200 tracking-wide font-light">e Guiar no Serviço.</p>
                <div class="mt-8 border-t border-blue-700/50 pt-8">
                    <p class="text-sm font-bold tracking-widest uppercase text-dbv-yellow">Sistema de Gestao de Clube
                    </p>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-8 bg-white dark:bg-slate-950 shadow-2xl relative z-10">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:text-left">
                    <div class="lg:hidden flex justify-center mb-6">
                        <div
                            class="w-16 h-16 bg-dbv-blue rounded-full flex items-center justify-center text-dbv-yellow font-bold text-2xl shadow-lg">
                            DBV</div>
                    </div>
                    <h2 class="text-3xl font-bold text-dbv-blue dark:text-blue-200">DBV Manager</h2>
                    <p class="text-gray-500 dark:text-slate-400 mt-2">Acesse sua conta para continuar.</p>
                </div>
                <div class="mt-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>
