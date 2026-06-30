<x-app-layout>

    <div class="ui-page space-y-6 max-w-5xl mx-auto">
        <x-page-title title="Comunicados" subtitle="Histórico de comunicados enviados aos responsáveis." />

        <div class="px-4 sm:px-0 flex justify-end">
            <a href="{{ route('comunicados.create') }}" class="ui-btn-primary w-full sm:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Novo Comunicado</span>
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 text-sm font-bold text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- Mobile: lista de cartões --}}
        <div class="space-y-3 sm:hidden px-4">
            @forelse ($comunicados as $comunicado)
                <a href="{{ route('comunicados.show', $comunicado) }}"
                   class="ui-card p-4 block hover:border-[#002F6C]/40 dark:hover:border-blue-500/40 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-black text-slate-800 dark:text-white leading-tight">{{ $comunicado->titulo }}</h3>
                        <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 px-2.5 py-1 text-[11px] font-black">
                            {{ $comunicado->total_enviados }}
                            <span class="font-bold normal-case">env.</span>
                        </span>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <span class="capitalize">{{ $comunicado->destinatarios === 'unidade' ? ('Unidade: '.($comunicado->unidade->nome ?? '—')) : $comunicado->destinatarios }}</span>
                        <span>{{ optional($comunicado->enviado_em)->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>
                </a>
            @empty
                <div class="ui-card p-8 text-center text-sm text-slate-400">Nenhum comunicado enviado ainda.</div>
            @endforelse
        </div>

        {{-- Desktop: tabela --}}
        <div class="ui-card overflow-hidden hidden sm:block">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-[11px] font-black text-slate-400 uppercase tracking-widest">
                    <tr>
                        <th class="text-left px-4 py-3">Título</th>
                        <th class="text-left px-4 py-3">Destinatários</th>
                        <th class="text-left px-4 py-3">Enviado para</th>
                        <th class="text-left px-4 py-3">Data</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($comunicados as $comunicado)
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-800 dark:text-white">{{ $comunicado->titulo }}</td>
                            <td class="px-4 py-3 text-slate-500 capitalize">
                                {{ $comunicado->destinatarios === 'unidade' ? ('Unidade: '.($comunicado->unidade->nome ?? '—')) : $comunicado->destinatarios }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $comunicado->total_enviados }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ optional($comunicado->enviado_em)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('comunicados.show', $comunicado) }}" class="text-[#002F6C] dark:text-blue-400 font-black text-xs uppercase tracking-widest">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Nenhum comunicado enviado ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $comunicados->links() }}
    </div>
</x-app-layout>
