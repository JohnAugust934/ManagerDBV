<x-app-layout>

    <div class="ui-page space-y-6 ui-animate-fade-up">

        <x-page-title title="Atas de Reunião" subtitle="Registros administrativos oficiais das reuniões do clube.">
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-slot:icon>
            <x-slot:actions>
                <a href="{{ route('atas.create') }}" class="ui-btn-primary w-full sm:w-auto group">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Nova Ata
                </a>
            </x-slot:actions>
        </x-page-title>

        <div class="mt-8">
            @if ($atas->isEmpty())
                <x-empty-state title="Nenhuma ata registrada" description="Crie a primeira ata para iniciar o histórico oficial de reuniões do clube.">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </x-slot:icon>
                </x-empty-state>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($atas as $ata)
                        <div class="ui-card p-0 overflow-hidden relative group hover:border-[#002F6C] dark:hover:border-blue-500 transition-all flex flex-col h-full border-t-4 border-t-transparent hover:border-t-[#002F6C] dark:hover:border-t-blue-500">
                            
                            <div class="p-6 flex-1">
                                <div class="flex justify-between items-start gap-4 mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest rounded-md">
                                                {{ $ata->data_reuniao?->format('d/m/Y') }}
                                            </span>
                                            <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-[#002F6C] dark:text-blue-400 text-[10px] font-black tracking-widest rounded-md flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ optional($ata->hora_inicio)->format('H:i') }}
                                            </span>
                                        </div>
                                        <h3 class="text-lg font-black text-slate-800 dark:text-white leading-tight">
                                            {{ $ata->titulo ?? 'Reunião Administrativa' }}
                                        </h3>
                                    </div>
                                </div>

                                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-3 font-medium">
                                    {{ Str::limit(strip_tags($ata->conteudo), 120) }}
                                </p>
                            </div>

                            <div class="p-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                                <div class="flex gap-2">
                                    <a href="{{ route('atas.show', $ata) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 text-[#002F6C] dark:text-blue-400 border border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Visualizar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('atas.edit', $ata) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 border border-slate-200 dark:border-slate-700 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                                <form id="del-ata-{{ $ata->id }}" action="{{ route('atas.destroy', $ata) }}" method="POST" class="ui-row-actions">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmAction({ title: 'Excluir Documento', message: 'Confirma a exclusão deste documento oficial?', formId: 'del-ata-{{ $ata->id }}', confirmText: 'Excluir', variant: 'danger' })" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 text-red-600 dark:text-red-400 border border-slate-200 dark:border-slate-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Excluir">
                                        <span class="sr-only">Excluir</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- O Tailwind Paginator Customizado Agirá Aqui --}}
                <div class="mt-8">
                    {{ $atas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
