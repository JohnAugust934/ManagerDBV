<x-app-layout>
    <x-slot name="header">Gestão de Usuários</x-slot>

    <div class="ui-page max-w-6xl mx-auto space-y-6 ui-animate-fade-up">

        {{-- Cabeçalho da Tela --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-4 sm:px-0">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-3">
                    <svg class="w-8 h-8 text-[#002F6C] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5V10l-12-8L0 10v10h5m7-12h4m-4 4h4m-4 4h4M7 10h.01M7 14h.01M7 18h.01" /></svg>
                    Equipe do Clube
                </h1>
                <p class="text-slate-500 font-medium mt-1">Gerencie os acessos, cargos e permissões dos membros da diretoria.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                {{-- Verifica se a rota de convites existe para renderizar este link. Normalmente é pra existir. --}}
                <a href="{{ route('invites.index') }}" class="ui-btn-secondary w-full sm:w-auto px-6 h-12 flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Gerenciar Convites
                </a>
            </div>
        </div>

        {{-- Tabela de Usuários --}}
        <div class="ui-card p-0 overflow-hidden mx-4 sm:mx-0">
            @if ($users->isEmpty())
                <div class="ui-empty py-16 border-none shadow-none">
                    <div class="ui-empty-icon"><svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                    <h3 class="ui-empty-title">Nenhum Usuário Encontrado</h3>
                    <p class="ui-empty-description">A base do seu clube ainda não tem membros. Crie convites para montar sua equipe de diretoria.</p>
                    <div class="mt-6"><a href="{{ route('invites.index') }}" class="ui-btn-primary">Criar Convites</a></div>
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                                <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 whitespace-nowrap">Usuário</th>
                                <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 text-center">Cargo Hierárquico</th>
                                <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500">Permissões Especiais</th>
                                <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-500 text-right">Controles</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($users as $user)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors group">
                                    {{-- Nome / Foto --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#002F6C] to-blue-500 shadow-lg shadow-blue-500/20 text-white flex items-center justify-center font-black text-xl shrink-0">
                                                {{ mb_substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-800 dark:text-white uppercase tracking-tight">{{ $user->name }}</p>
                                                <p class="text-xs font-semibold text-slate-500 mt-0.5 lowercase">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Cargo --}}
                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        @php
                                            $roleColors = [
                                                'master' => 'bg-red-50 border-red-200 text-red-600 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-400',
                                                'diretor' => 'bg-purple-50 border-purple-200 text-purple-600 dark:bg-purple-500/10 dark:border-purple-500/30 dark:text-purple-400',
                                                'secretario' => 'bg-blue-50 border-blue-200 text-blue-600 dark:bg-blue-500/10 dark:border-blue-500/30 dark:text-blue-400',
                                                'tesoureiro' => 'bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400',
                                            ];
                                            $colorClass = $roleColors[$user->role] ?? 'bg-slate-50 border-slate-200 text-slate-600 dark:bg-slate-800/50 dark:text-slate-400';
                                        @endphp
                                        <span class="inline-block px-3 py-1 text-[11px] font-black uppercase tracking-widest rounded border shadow-sm {{ $colorClass }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>

                                    {{-- Permissões --}}
                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap gap-1.5">
                                            @if ($user->extra_permissions && count($user->extra_permissions) > 0)
                                                @foreach ($user->extra_permissions as $perm)
                                                    <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 text-amber-700 dark:text-amber-500 text-[10px] rounded uppercase font-black tracking-widest shadow-sm">
                                                        {{ $perm }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-xs font-bold text-slate-300 dark:text-slate-600 italic">Sem permissões adicionais</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Ações --}}
                                    <td class="px-6 py-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-40 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('usuarios.edit', $user->id) }}" class="p-2 rounded-xl text-slate-400 hover:text-[#002F6C] hover:bg-[#002F6C]/10 dark:hover:text-blue-400 dark:hover:bg-blue-500/20 transition-colors" title="Editar Permissões">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </a>
                                            
                                            @if ($user->id !== auth()->id() && $user->role !== 'master')
                                                <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Deseja descadastrar e revogar o acesso deste usuário?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" title="Revogar Usuário">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
