@props(['title', 'subtitle' => null, 'back' => null])

{{-- Título de página padrão. Garante um <h1> visível e na hierarquia de headings.
     Use no início do conteúdo (dentro do .ui-page). `back` adiciona seta de voltar. --}}
<div class="flex items-center gap-3 mb-6 ui-animate-fade-up">
    @if ($back)
        <a href="{{ $back }}"
           class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors shrink-0"
           aria-label="Voltar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
    @endif
    <div class="min-w-0">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white tracking-tight leading-tight">
            {{ $title }}
        </h1>
        @isset($subtitle)
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">{{ $subtitle }}</p>
        @endisset
    </div>
</div>
