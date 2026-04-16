<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('especialidades.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">Detalhes da Especialidade</h2>
        </div>
    </x-slot>

    <div class="ui-page max-w-5xl mx-auto ui-animate-fade-up pb-20 space-y-6">
        <div class="ui-card overflow-hidden">
            <div class="h-1.5 w-full" style="background: {{ $especialidade->cor_fundo ?? '#9CA3AF' }}"></div>
            <div class="p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-2">{{ $especialidade->codigo ?? 'Sem código oficial' }}</p>
                        <h1 class="text-3xl font-black text-slate-800 dark:text-white leading-tight">{{ $especialidade->nome }}</h1>
                        <p class="mt-2 text-slate-500 font-semibold">{{ $especialidade->area }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 md:w-[320px]">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-900/40">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Investidos</p>
                            <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $especialidade->desbravadores_count }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-900/40">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Tipo</p>
                            <p class="text-sm font-black text-slate-800 dark:text-white mt-1">{{ $especialidade->is_avancada ? 'Avançada' : 'Regular' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-900/40 col-span-2">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">Origem</p>
                            @if($especialidade->url_oficial)
                                <a href="{{ $especialidade->url_oficial }}" target="_blank" rel="noopener" class="text-sm font-bold text-[#002F6C] dark:text-blue-400 hover:underline break-all">
                                    {{ $especialidade->url_oficial }}
                                </a>
                            @else
                                <p class="text-sm font-semibold text-slate-500">Sem URL oficial cadastrada.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('especialidades.edit', $especialidade) }}" class="ui-btn-primary px-5 py-2 rounded-xl">Editar Especialidade</a>
                </div>
            </div>
        </div>

        <div class="ui-card p-6 sm:p-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">Requisitos Oficiais</h3>
                <span class="text-xs font-black uppercase tracking-widest text-slate-400">{{ $especialidade->requisitosOficiais->count() }} itens</span>
            </div>

            @if($especialidade->requisitosOficiais->isEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm font-semibold">
                    Esta especialidade ainda não possui requisitos sincronizados localmente. Use o comando
                    <code>php artisan especialidades:sync-oficiais --requirements</code>
                    (ou <code>--dry-run</code> para prévia).
                </div>
            @else
                <ol class="space-y-3 list-decimal pl-5">
                    @foreach($especialidade->requisitosOficiais as $req)
                        <li class="text-sm font-semibold text-slate-700 dark:text-slate-200 leading-relaxed">
                            {{ $req->descricao }}
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>
</x-app-layout>
