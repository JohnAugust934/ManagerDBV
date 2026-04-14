@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="mt-8 mb-4">
        
        {{-- MOBILE VIEW --}}
        <div class="flex gap-2 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-5 py-3 text-[11px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50 cursor-not-allowed rounded-2xl">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-5 py-3 text-[11px] font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all shadow-sm active:scale-95">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-5 py-3 text-[11px] font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all shadow-sm active:scale-95">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-5 py-3 text-[11px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50 cursor-not-allowed rounded-2xl">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- DESKTOP VIEW --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">
                    Mostrando
                    @if ($paginator->firstItem())
                        <span class="font-black text-slate-800 dark:text-slate-200">{{ $paginator->firstItem() }}</span>
                        a
                        <span class="font-black text-slate-800 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    de
                    <span class="font-black text-slate-800 dark:text-slate-200">{{ $paginator->total() }}</span>
                    resultados
                </p>
            </div>

            <div>
                <span class="inline-flex items-center gap-1.5 shadow-sm bg-white dark:bg-slate-800/50 p-1.5 rounded-[20px] border border-slate-100 dark:border-slate-700/50">
                    
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="inline-flex items-center justify-center w-10 h-10 text-slate-300 dark:text-slate-600 cursor-not-allowed rounded-full bg-slate-50 dark:bg-slate-800" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-10 h-10 text-slate-500 hover:text-[#002F6C] dark:text-slate-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full transition-colors" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-black text-slate-400 dark:text-slate-500 cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-black text-white bg-gradient-to-br from-[#002F6C] to-blue-600 dark:from-blue-600 dark:to-blue-500 rounded-full shadow-md shadow-blue-900/20 cursor-default">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center justify-center w-10 h-10 text-sm font-black text-slate-600 hover:text-[#002F6C] dark:text-slate-300 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full transition-colors" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-10 h-10 text-slate-500 hover:text-[#002F6C] dark:text-slate-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full transition-colors" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="inline-flex items-center justify-center w-10 h-10 text-slate-300 dark:text-slate-600 cursor-not-allowed rounded-full bg-slate-50 dark:bg-slate-800" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
