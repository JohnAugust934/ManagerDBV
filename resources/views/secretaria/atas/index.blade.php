<x-app-layout>
    <x-slot name="header">
        Termos Oficiais (Atas)
    </x-slot>

    <div class="ui-page space-y-6 max-w-7xl mx-auto ui-animate-fade-up">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#002F6C] dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Atas de Reunião
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Registros Administrativos</p>
            </div>
            
            <a href="{{ route('atas.create') }}" class="ui-btn-primary w-full md:w-auto flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Nova Ata
            </a>
        </div>

        <div class="mt-8">
            @if ($atas->isEmpty())
                <div class="ui-card p-12 flex flex-col items-center justify-center text-center border-dashed border-2 border-slate-200 dark:border-slate-800 bg-transparent shadow-none">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">Nenhuma ata registrada</h3>
                    <p class="text-sm font-bold text-slate-400 mb-6 max-w-md">Crie a primeira ata para iniciar o histórico oficial de reuniões do clube.</p>
                </div>
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
                                    <a href="{{ route('atas.edit', $ata) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 text-amber-500 dark:text-amber-400 border border-slate-200 dark:border-slate-700 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                                <form action="{{ route('atas.destroy', $ata) }}" method="POST" onsubmit="return confirm('Confirma a exclusão deste documento oficial?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-10 px-4 flex items-center justify-center gap-2 rounded-xl bg-white dark:bg-slate-800 text-red-500 font-bold text-xs uppercase tracking-widest border border-slate-200 dark:border-slate-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        Excluir
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
