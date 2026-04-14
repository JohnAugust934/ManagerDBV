<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">
                Classes Regulares e Avançadas
            </h2>
        </div>
    </x-slot>

    <div class="ui-page space-y-8 max-w-7xl ui-animate-fade-up">

        {{-- Cabeçalho / Título --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-4 sm:px-0">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Evolução de Classes</h1>
                <p class="text-slate-500 font-medium">Acompanhe e gerencie os requisitos para investidura do clube.</p>
            </div>
            
            <span class="ui-badge bg-[#002F6C]/10 text-[#002F6C] dark:bg-blue-500/20 dark:text-blue-400 self-start md:self-end px-4 py-2 hidden sm:flex">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Pedagógico
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4 sm:px-0">
            @foreach ($classes as $classe)
                <a href="{{ route('classes.show', $classe->id) }}" class="group relative flex flex-col h-full bg-white dark:bg-slate-800/80 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700/60 overflow-hidden hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-black/50 transition-all duration-300 transform hover:-translate-y-1">

                    {{-- Faixa superior curva / Top Banner Gradient --}}
                    <div class="absolute top-0 inset-x-0 h-24" style="background: linear-gradient(to bottom right, {{ $classe->cor }}, {{ $classe->cor }}44);">
                        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white to-transparent"></div>
                    </div>

                    {{-- Bolha Icone Curvada --}}
                    <div class="relative pt-12 px-6 pb-2 text-center">
                        <div class="relative w-20 h-20 mx-auto rounded-full bg-white dark:bg-slate-900 shadow-lg border-[6px] border-white dark:border-[#18181b] flex items-center justify-center transform group-hover:scale-110 transition-transform duration-500 ease-out z-10">
                            <!-- Icon / Letter with Class Color -->
                            <span class="text-3xl font-black drop-shadow-sm" style="color: {{ $classe->cor }}">{{ mb_strtoupper(substr($classe->nome, 0, 1)) }}</span>
                        </div>
                    </div>

                    {{-- Conteúdo do Cartão --}}
                    <div class="p-6 text-center flex flex-col flex-1 relative z-10">
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight leading-tight mb-2 uppercase group-hover:text-blue-500 transition-colors">
                            {{ $classe->nome }}
                        </h3>
                        
                        <p class="text-[13px] text-slate-500 dark:text-slate-400 font-medium leading-relaxed mb-6">
                            Analise os cartões, cadastre as assinaturas e organize a investidura desta classe.
                        </p>
                        
                        <div class="mt-auto">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-[11px] font-black uppercase tracking-widest text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                {{ $classe->requisitos->count() }} Requisitos Base
                            </span>
                        </div>
                    </div>

                    {{-- Action Row Bottom --}}
                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center transition-colors group-hover:bg-slate-100 dark:group-hover:bg-slate-800">
                        <span class="text-[12px] font-black text-slate-500 uppercase tracking-widest group-hover:text-amber-500 transition-colors">Entrar na Sala</span>
                        <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-center transform group-hover:translate-x-2 transition-transform duration-300">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</x-app-layout>
