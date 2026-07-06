<x-app-layout>

    <div class="ui-page max-w-2xl mx-auto space-y-6">
        <div class="ui-card p-6 space-y-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ $comunicado->titulo }}</h2>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">
                    Enviado em {{ optional($comunicado->enviado_em)->format('d/m/Y H:i') ?? '—' }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Destinatários</p>
                    <p class="font-black text-slate-800 dark:text-white capitalize mt-0.5">
                        {{ $comunicado->destinatarios === 'unidade' ? ('Unidade: '.($comunicado->unidade->nome ?? '—')) : $comunicado->destinatarios }}
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Enviado para</p>
                    <p class="font-black text-slate-800 dark:text-white mt-0.5">{{ $comunicado->total_enviados }} destinatário(s)</p>
                </div>
            </div>

            <div class="pt-2">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Mensagem</p>
                <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $comunicado->corpo }}</div>
            </div>
        </div>

        <a href="{{ route('comunicados.index') }}" class="ui-btn-secondary w-full sm:w-auto">← Voltar</a>
    </div>
</x-app-layout>
