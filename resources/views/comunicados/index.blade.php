<x-app-layout>
    <x-slot name="header">Comunicados</x-slot>

    <div class="ui-page space-y-6 max-w-5xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <p class="text-sm text-slate-500 dark:text-slate-400">Histórico de comunicados enviados aos responsáveis.</p>
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

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
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
        </div>

        {{ $comunicados->links() }}
    </div>
</x-app-layout>
