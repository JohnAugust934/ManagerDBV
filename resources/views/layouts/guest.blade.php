<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Desbravadores Manager') }}</title>

    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center overflow-hidden">

    <!-- Background Elements -->
    <div class="fixed inset-0 z-0">
        <!-- Deep rich DBV blue base -->
        <div class="absolute inset-0 bg-[#001D42]"></div>
        
        <!-- Abstract gradient orbs -->
        <div class="absolute top-[-10%] right-[-5%] w-[800px] h-[800px] rounded-full bg-[#002F6C] blur-[120px] opacity-60 animate-pulse" style="animation-duration: 8s;"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] rounded-full bg-[#D9222A] blur-[120px] opacity-30 animate-pulse" style="animation-duration: 10s; animation-delay: 2s;"></div>
        <div class="absolute top-[40%] left-[20%] w-[400px] h-[400px] rounded-full bg-[#FCD116] blur-[150px] opacity-20"></div>
        
        <!-- Grid overlay -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0wIDBoNDB2NDBIMHoiIGZpbGw9Im5vbmUiLz4KPHBhdGggZD0iTTAgNDBoNDBNNDAgMHY0MCIgc3Ryb2tlPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDMpIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiLz4KPC9zdmc+')] opacity-50"></div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="relative z-10 w-full max-w-[1200px] mx-auto p-4 sm:p-6 lg:p-8 flex flex-col lg:flex-row items-center gap-12 lg:gap-24 h-full min-h-screen lg:min-h-[800px]">
        
        <!-- Left Side: Branding & Copy -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center text-center lg:text-left pt-12 lg:pt-0 ui-animate-fade-up">
            <div class="inline-flex items-center justify-center lg:justify-start gap-4 mb-8">
                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md p-3 border border-white/20 shadow-2xl">
                    <img src="{{ asset('favicon.svg') }}" alt="Logo DBV" class="w-full h-full object-contain filter drop-shadow">
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-widest text-[#FCD116] uppercase">Manager</h1>
                    <h2 class="text-xl font-bold tracking-tight text-white leading-none">Desbravadores</h2>
                </div>
            </div>

            <h3 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-[1.1] tracking-tight mb-6 drop-shadow-lg">
                Salvar do Pecado<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-[#D9222A]">e Guiar no Serviço.</span>
            </h3>
            
            <p class="text-lg text-slate-300 font-medium max-w-xl mx-auto lg:mx-0 leading-relaxed mb-10">
                O sistema de gestão definitivo para Clubes de Desbravadores. Organize unidades, membros, finanças e especialidades em um só lugar.
            </p>

            <div class="hidden lg:flex items-center gap-6 text-sm font-bold text-slate-400">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Seguro
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    Rápido
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    Mobile-First
                </div>
            </div>
        </div>

        <!-- Right Side: Auth Form Container (Glassmorphism) -->
        <div class="w-full lg:w-1/2 max-w-[480px] mx-auto ui-animate-fade-up" style="animation-delay: 150ms;">
            <div class="relative rounded-[32px] overflow-hidden p-8 sm:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-white/10 backdrop-blur-2xl bg-white/5">
                <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50 pointer-events-none"></div>
                
                <div class="relative z-10">
                    {{ $slot }}
                </div>
            </div>
            
            <!-- Footer text below login box -->
            <p class="text-center text-xs text-slate-500 font-medium mt-8 mb-8 lg:mb-0">
                &copy; {{ date('Y') }} ManagerDBV. Todos os direitos reservados.
            </p>
        </div>

    </div>

</body>
</html>
