<x-app-layout>

    <div class="ui-page space-y-6 ui-animate-fade-up">

        <x-page-title title="Publicações e Atos" subtitle="Atos oficiais da diretoria — mudanças de cargo e diretrizes.">
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </x-slot:icon>
        </x-page-title>

        <div class="flex sm:justify-end">
            <a href="{{ route('atos.create') }}" class="ui-btn-primary w-full sm:w-auto group">
                <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Registrar Ato
            </a>
        </div>

        <div class="mt-8">
            @if ($atos->isEmpty())
                <x-empty-state title="Sem atos publicados" description="Os atos oficiais de mudanças de cargo e diretrizes ficam arquivados aqui.">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </x-slot:icon>
                </x-empty-state>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($atos as $ato)
                        <div class="ui-card p-0 overflow-hidden relative group hover:border-[#002F6C] dark:hover:border-blue-500 transition-all flex flex-col h-full border-t-4 border-t-transparent hover:border-t-[#002F6C] dark:hover:border-t-blue-500">

                            <div class="p-6 flex-1">
                                <div class="flex items-center gap-2 mb-3 flex-wrap">
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-[#002F6C] dark:text-blue-400 text-[10px] font-black tracking-widest rounded-md font-mono">
                                        #{{ str_pad($ato->numero, 3, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-[10px] font-black uppercase tracking-widest rounded-md border border-blue-100 dark:border-blue-800/30">
                                        {{ $ato->tipo }}
                                    </span>
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest rounded-md ml-auto">
                                        {{ $ato->data?->format('d/m/Y') }}
                                    </span>
                                </div>

                                <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-4 font-medium leading-relaxed">
                                    {{ Str::limit(strip_tags($ato->descricao), 160) }}
                                </p>
                            </div>

                            <div class="p-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                                <a href="{{ route('atos.edit', $ato) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 text-amber-500 dark:text-amber-400 border border-slate-200 dark:border-slate-700 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form id="del-ato-{{ $ato->id }}" action="{{ route('atos.destroy', $ato) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmAction({ title: 'Revogar Ato', message: 'Confirma a exclusão (Revogação) deste ato?', formId: 'del-ato-{{ $ato->id }}', confirmText: 'Excluir', variant: 'danger' })" class="h-10 px-4 flex items-center justify-center gap-2 rounded-xl bg-white dark:bg-slate-800 text-red-500 font-bold text-xs uppercase tracking-widest border border-slate-200 dark:border-slate-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(method_exists($atos, 'links'))
                    <div class="mt-8">
                        {{ $atos->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
