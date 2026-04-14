<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('unidades.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">
                Painel da Unidade
            </h2>
        </div>
    </x-slot>

    <div class="ui-page space-y-6 max-w-6xl ui-animate-fade-up">

        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 px-2 sm:px-0 mb-2">
            <a href="{{ route('unidades.edit', $unidade) }}" class="ui-btn-primary w-full sm:w-auto text-sm group">
                <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Editar Unidade
            </a>
        </div>

        {{-- Banner Principal --}}
        <div class="ui-card overflow-hidden bg-white dark:bg-slate-800">
            <div class="h-4 sm:h-6 w-full bg-gradient-to-r from-[#002F6C] via-blue-600 to-blue-400"></div>

            <div class="p-6 md:p-10">
                <div class="flex flex-col md:flex-row items-center md:items-start text-center md:text-left gap-8">
                    
                    <div class="shrink-0 relative">
                        <div class="w-32 h-32 rounded-full bg-slate-50 dark:bg-slate-900/50 flex items-center justify-center border-8 border-slate-100 dark:border-slate-800 text-[#002F6C] dark:text-blue-400 text-5xl font-black shadow-inner">
                            {{ mb_strtoupper(substr($unidade->nome, 0, 1)) }}
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-[#FCD116] rounded-full border-4 border-white dark:border-slate-800 flex items-center justify-center text-[#002F6C] shadow-sm transform -rotate-12">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>

                    <div class="flex-1 w-full">
                        <h3 class="text-4xl font-black text-slate-800 dark:text-white mb-4 tracking-tight uppercase">{{ $unidade->nome }}</h3>

                        <div class="flex flex-col sm:flex-row items-center gap-4 text-sm mb-6">
                            <span class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700/50 px-4 py-2 rounded-xl text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-600 w-full sm:w-auto justify-center">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Liderança: <span class="text-[#002F6C] dark:text-blue-400 uppercase tracking-widest text-[12px]">{{ $unidade->conselheiro ?? 'Não definido' }}</span>
                            </span>
                            <span class="flex items-center gap-2 bg-blue-50 dark:bg-blue-900/20 text-[#002F6C] dark:text-blue-300 px-4 py-2 rounded-xl font-bold border border-blue-100 dark:border-blue-900/50 w-full sm:w-auto justify-center">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $unidade->desbravadores->count() }} Desbravadores
                            </span>
                        </div>

                        @if ($unidade->grito_guerra)
                            <div class="relative bg-amber-50/50 dark:bg-amber-900/10 p-5 rounded-2xl border border-amber-100 dark:border-amber-900/30">
                                <svg class="absolute top-3 left-3 w-6 h-6 text-amber-500 opacity-20" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.8954 13.1216 16 12.017 16H9.01705C7.91248 16 7.01705 16.8954 7.01705 18L7.01705 21H14.017ZM21.017 6.00005C21.017 11.4395 16.666 16.0354 11.3912 16.148L11.4589 16.148C10.3543 16.148 9.45889 17.0435 9.45889 18.148V21.0001H4.45889C3.90661 21.0001 3.45889 20.5523 3.45889 20.0001V4.00005C3.45889 3.44776 3.90661 3.00005 4.45889 3.00005H21.017V6.00005Z"/></svg>
                                <p class="pl-8 font-black italic text-slate-600 dark:text-slate-300 text-lg leading-relaxed">
                                    "{{ $unidade->grito_guerra }}"
                                </p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- Membros da Unidade --}}
        <div class="ui-card bg-white dark:bg-slate-800 ui-animate-fade-up" style="animation-delay: 150ms;">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-900/30">
                <h3 class="font-black text-[15px] uppercase tracking-widest text-[#002F6C] dark:text-blue-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Desbravadores da Unidade
                </h3>
            </div>

            @if ($unidade->desbravadores->count() > 0)
                
                <div class="hidden md:block ui-table-wrapper rounded-none border-0 shadow-none">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>Nome do Desbravador</th>
                                <th>Cargo</th>
                                <th class="text-center">Idade</th>
                                <th class="text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unidade->desbravadores as $dbv)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm">
                                                @if ($dbv->foto)
                                                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $dbv->foto) }}" alt="Foto">
                                                @else
                                                    <span class="text-slate-400 font-black text-xs">{{ mb_strtoupper(substr($dbv->nome, 0, 2)) }}</span>
                                                @endif
                                            </div>
                                            <div class="font-black text-slate-800 dark:text-white leading-tight">
                                                {{ $dbv->nome }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-[12px] font-bold uppercase tracking-widest text-[#002F6C] dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-3 py-1 rounded-lg">
                                            {{ $dbv->cargo ?? 'Membro' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-sm font-bold text-slate-600 dark:text-slate-400">
                                            {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('desbravadores.show', $dbv) }}" class="inline-flex items-center justify-center p-2 rounded-xl bg-slate-100 hover:bg-[#002F6C] hover:text-white dark:bg-slate-800 dark:hover:bg-blue-600 text-slate-500 transition-colors shadow-sm ring-1 ring-inset ring-slate-200 dark:ring-slate-700" title="Ver Perfil">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden flex flex-col pt-2">
                    @foreach ($unidade->desbravadores as $dbv)
                        <div class="p-4 flex items-center justify-between border-b border-dashed border-slate-100 dark:border-slate-800 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm shrink-0">
                                    @if ($dbv->foto)
                                        <img class="w-full h-full object-cover" src="{{ asset('storage/' . $dbv->foto) }}" alt="">
                                    @else
                                        <span class="text-slate-400 font-black">{{ mb_strtoupper(substr($dbv->nome, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 dark:text-white leading-tight mb-1">{{ $dbv->nome }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        {{ $dbv->cargo ?? 'Membro' }} &bull; {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('desbravadores.show', $dbv) }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-[#002F6C] hover:text-white dark:bg-slate-800 dark:hover:bg-blue-600 text-slate-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    @endforeach
                </div>

            @else
                <div class="ui-empty m-6 border-0 bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="ui-empty-icon"><svg class="w-8 h-8 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                    <h3 class="ui-empty-title">Unidade Vazia</h3>
                    <p class="ui-empty-description">Nenhum desbravador foi alocado nesta unidade ainda. Acesse a secretaria para vincular os membros.</p>
                </div>
            @endif
        </div>
        
    </div>
</x-app-layout>
