<x-app-layout>

    <div class="ui-page ui-page--narrow space-y-6">
        <x-page-title title="Comunicados" subtitle="Histórico de comunicados enviados aos responsáveis.">
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </x-slot:icon>
            <x-slot:actions>
                <a href="{{ route('comunicados.create') }}" class="ui-btn-primary w-full sm:w-auto group">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Novo Comunicado</span>
                </a>
            </x-slot:actions>
        </x-page-title>

        {{-- Mobile: lista de cartões --}}
        <div class="space-y-3 sm:hidden">
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
        <div class="ui-table-wrapper hidden sm:block">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Destinatários</th>
                        <th>Enviado para</th>
                        <th>Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comunicados as $comunicado)
                        <tr class="group">
                            <td class="font-bold text-slate-800 dark:text-white">{{ $comunicado->titulo }}</td>
                            <td class="text-slate-500 capitalize">
                                {{ $comunicado->destinatarios === 'unidade' ? ('Unidade: '.($comunicado->unidade->nome ?? '—')) : $comunicado->destinatarios }}
                            </td>
                            <td class="text-slate-500">{{ $comunicado->total_enviados }}</td>
                            <td class="text-slate-500">{{ optional($comunicado->enviado_em)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('comunicados.show', $comunicado) }}" class="text-[#002F6C] dark:text-blue-400 font-black text-xs uppercase tracking-widest">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400">Nenhum comunicado enviado ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $comunicados->links() }}
    </div>
</x-app-layout>
