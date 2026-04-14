<x-app-layout>
    <x-slot name="header">
        Atos Oficiais da Diretoria
    </x-slot>

    <div class="ui-page space-y-6 max-w-7xl mx-auto ui-animate-fade-up">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#002F6C] dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    Publicações e Atos
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Secretaria Executiva</p>
            </div>
            
            <a href="{{ route('atos.create') }}" class="ui-btn-primary w-full md:w-auto flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Registrar Ato
            </a>
        </div>

        <div class="mt-8">
            @if ($atos->isEmpty())
                <div class="ui-card p-12 flex flex-col items-center justify-center text-center border-dashed border-2 border-slate-200 dark:border-slate-800 bg-transparent shadow-none">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4 text-[#002F6C] dark:text-blue-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">Sem atos publicados</h3>
                    <p class="text-sm font-bold text-slate-400 mb-6 max-w-md">Os atos oficiais de mudanças de cargo e diretrizes ficam arquivados aqui.</p>
                </div>
            @else
                <div class="ui-card overflow-hidden p-0 border border-slate-100 dark:border-slate-800">
                    <div class="overflow-x-auto">
                        <table class="ui-table w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800">
                                    <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest w-24">Protocolo</th>
                                    <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest w-32">Classificação</th>
                                    <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest">Resumo da Decisão</th>
                                    <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest w-32">Publicação</th>
                                    <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-widest text-right w-24">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50 bg-white dark:bg-slate-800">
                                @foreach ($atos as $ato)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors group">
                                        <td class="px-6 py-4">
                                            <span class="font-mono font-black text-[#002F6C] dark:text-blue-400">#{{ str_pad($ato->numero, 3, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">
                                                {{ $ato->tipo }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                                {{ Str::limit($ato->descricao, 90) }}
                                            </p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                                {{ $ato->data?->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                                <a href="{{ route('atos.edit', $ato) }}" class="p-2 rounded-xl bg-slate-50 hover:bg-amber-50 dark:bg-slate-700 dark:hover:bg-amber-900/30 text-slate-400 hover:text-amber-500 transition-colors" title="Editar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                                <form action="{{ route('atos.destroy', $ato) }}" method="POST" onsubmit="return confirm('Confirma a exclusão (Revogação) deste ato?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 rounded-xl bg-slate-50 hover:bg-red-50 dark:bg-slate-700 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-500 transition-colors" title="Excluir">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- O "Atos" não estava usando Paginação originalmente pois os registros costumam ser parcos, mas caso passe a usar, a base é a mesma. --}}
                @if(method_exists($atos, 'links'))
                    <div class="mt-8">
                        {{ $atos->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
