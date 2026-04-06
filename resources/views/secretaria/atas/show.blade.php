<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">Ata de Reuniao</h2>
    </x-slot>

    @php
        $clubeNome = auth()->user()?->club?->nome?? 'Clube de Desbravadores';
        $responsavel = auth()->user()?->name?? 'Sistema';
    @endphp

    <style>
        @media print {
            @page { margin: 18mm 14mm; }
            body { background: #fff !important; }
            .print-report-shell { padding: 0 !important; max-width: none !important; }
            .print-report-card { box-shadow: none !important; border: none !important; }
        }
    </style>

    <div class="ui-page print:py-0 space-y-6">
        <div class="px-4 sm:px-0 print:hidden flex flex-col sm:flex-row sm:justify-end gap-2">
            <a href="{{ route('atas.index') }}" class="ui-btn-secondary w-full sm:w-auto">Voltar</a>
            <a href="{{ route('atas.edit', $ata) }}" class="ui-btn-secondary w-full sm:w-auto">Editar</a>
            <a href="{{ route('atas.print', $ata) }}" target="_blank" rel="noopener" class="ui-btn-primary w-full sm:w-auto">Imprimir</a>
        </div>

        <div class="print-report-shell max-w-5xl mx-auto">
            <article class="print-report-card ui-card overflow-hidden">
                <header class="px-8 py-8 border-b border-gray-200 dark:border-slate-700">
                    <div class="text-xs font-bold uppercase tracking-[0.28em] text-teal-700 dark:text-teal-400">Desbravadores Manager</div>
                    <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Ata de Reuniao</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Documento oficial padronizado para secretaria</p>
                    <div class="mt-4 text-sm text-slate-600 dark:text-slate-300">
                        Clube: {{ $clubeNome }} | Emitido em {{ now()->format('d/m/Y H:i') }} | Responsavel: {{ $responsavel }}
                    </div>
                </header>

                <section class="px-8 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="ui-card-muted p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Data da reuniao</div>
                            <div class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ $ata->data_reuniao?->format('d/m/Y')?? 'Não informado' }}</div>
                        </div>
                        <div class="ui-card-muted p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Horario</div>
                            <div class="mt-2 text-lg font-bold text-slate-900 dark:text-white">
                                {{ optional($ata->hora_inicio)->format('H:i')?? 'Não informado' }}
                                @if ($ata->hora_fim)
                                    as {{ optional($ata->hora_fim)->format('H:i') }}
                                @endif
                            </div>
                        </div>
                        <div class="ui-card-muted p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Local</div>
                            <div class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ $ata->local?: 'Não informado' }}</div>
                        </div>
                    </div>
                </section>

                <section class="px-8 pb-6">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-5 py-4 bg-slate-900 text-white text-xs font-bold uppercase tracking-[0.18em]">Identificacao do documento</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200 dark:divide-slate-700">
                            <div class="p-5">
                                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Titulo</div>
                                <div class="mt-2 text-base font-bold text-slate-900 dark:text-white">{{ $ata->titulo }}</div>
                            </div>
                            <div class="p-5">
                                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Participantes</div>
                                <div class="mt-2 text-sm leading-7 text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $ata->participantes?: 'Não informado' }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="px-8 pb-8">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-5 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700 dark:text-teal-400">Conteudo registrado</div>
                        </div>
                        <div class="p-6 text-sm leading-7 text-slate-800 dark:text-slate-200 whitespace-pre-line">{{ $ata->conteudo }}</div>
                    </div>
                </section>
            </article>
        </div>
    </div>
</x-app-layout>

