<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                Ata de Reunião
            </h2>

            <div class="hidden md:flex items-center gap-2 print:hidden">
                <a href="{{ route('atas.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">
                    Voltar
                </a>
                <a href="{{ route('atas.edit', $ata) }}"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-amber-500 text-white text-xs font-bold uppercase tracking-widest hover:bg-amber-600">
                    Editar
                </a>
                <a href="{{ route('atas.print', $ata) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-dbv-blue text-white text-xs font-bold uppercase tracking-widest hover:bg-blue-800">
                    Imprimir
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $clubeNome = auth()->user()?->club?->nome ?? 'Clube de Desbravadores';
        $responsavel = auth()->user()?->name ?? 'Sistema';
    @endphp

    <style>
        @media print {
            @page {
                margin: 18mm 14mm;
            }

            body {
                background: #fff !important;
            }

            .print-report-shell {
                padding: 0 !important;
                max-width: none !important;
            }

            .print-report-card {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>

    <div class="py-6 print:py-0">
        <div class="print-report-shell max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="md:hidden grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4 print:hidden">
                <a href="{{ route('atas.index') }}"
                    class="inline-flex items-center justify-center px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold text-gray-700 dark:text-gray-300">
                    Voltar
                </a>
                <a href="{{ route('atas.edit', $ata) }}"
                    class="inline-flex items-center justify-center px-4 py-3 rounded-xl bg-amber-500 text-white text-sm font-bold">
                    Editar
                </a>
                <a href="{{ route('atas.print', $ata) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center justify-center px-4 py-3 rounded-xl bg-dbv-blue text-white text-sm font-bold">
                    Imprimir
                </a>
            </div>

            <article
                class="print-report-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-3xl shadow-sm overflow-hidden">
                <header class="px-8 py-8 border-b border-slate-200 dark:border-slate-700">
                    <div class="text-xs font-bold uppercase tracking-[0.28em] text-teal-700 dark:text-teal-400">
                        Desbravadores Manager
                    </div>
                    <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                        Ata de Reunião
                    </h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Documento oficial padronizado para secretaria
                    </p>
                    <div class="mt-4 text-sm text-slate-600 dark:text-slate-300">
                        Clube: {{ $clubeNome }} |
                        Emitido em {{ now()->format('d/m/Y H:i') }} |
                        Responsável: {{ $responsavel }}
                    </div>
                </header>

                <section class="px-8 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Data da reunião
                            </div>
                            <div class="mt-2 text-lg font-bold text-slate-900 dark:text-white">
                                {{ $ata->data_reuniao?->format('d/m/Y') ?? 'Não informado' }}
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Horário
                            </div>
                            <div class="mt-2 text-lg font-bold text-slate-900 dark:text-white">
                                {{ optional($ata->hora_inicio)->format('H:i') ?? 'Não informado' }}
                                @if ($ata->hora_fim)
                                    às {{ optional($ata->hora_fim)->format('H:i') }}
                                @endif
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Local
                            </div>
                            <div class="mt-2 text-lg font-bold text-slate-900 dark:text-white">
                                {{ $ata->local ?: 'Não informado' }}
                            </div>
                        </div>
                    </div>
                </section>

                <section class="px-8 pb-6">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-5 py-4 bg-slate-900 text-white">
                            <div class="text-xs font-bold uppercase tracking-[0.18em]">
                                Identificação do documento
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-0 divide-y md:divide-y-0 md:divide-x divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                            <div class="p-5">
                                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                    Título
                                </div>
                                <div class="mt-2 text-base font-bold text-slate-900 dark:text-white">
                                    {{ $ata->titulo }}
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                    Participantes
                                </div>
                                <div class="mt-2 text-sm leading-7 text-slate-700 dark:text-slate-300 whitespace-pre-line">
                                    {{ $ata->participantes ?: 'Não informado' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="px-8 pb-8">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-5 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700 dark:text-teal-400">
                                Conteúdo registrado
                            </div>
                        </div>
                        <div class="p-6 text-sm leading-7 text-slate-800 dark:text-slate-200 whitespace-pre-line">
                            {{ $ata->conteudo }}
                        </div>
                    </div>
                </section>

                <footer class="px-8 pb-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 pt-8">
                        <div class="pt-8 border-t border-slate-400 text-center">
                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Secretário(a)
                            </div>
                        </div>
                        <div class="pt-8 border-t border-slate-400 text-center">
                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Diretor(a)
                            </div>
                        </div>
                    </div>
                </footer>
            </article>
        </div>
    </div>
</x-app-layout>
