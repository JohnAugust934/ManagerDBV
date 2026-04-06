<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">
                Central de Relatórios
            </h2>
        </div>
    </x-slot>

    <div class="ui-page" x-data="{ tipo: '' }">
        <div class="mx-auto flex max-w-7xl flex-col gap-8">
            <section class="grid gap-6 lg:grid-cols-[1.45fr_1fr]">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="bg-gradient-to-r from-slate-900 via-sky-900 to-teal-800 px-8 py-8 text-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-200">Central de gestao</p>
                        <h3 class="mt-3 max-w-2xl text-3xl font-black leading-tight">
                            Uma tela de relatorios pensada para quem usa o sistema toda semana.
                        </h3>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-sky-100/90">
                            Gere listas, fichas completas, frequencia, inadimplencia, contatos de emergencia,
                            rankings e relatorios financeiros com filtros praticos e PDFs organizados.
                        </p>
                    </div>

                    <div class="grid gap-4 p-6 md:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cadastros</p>
                            <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">
                                Listas resumidas, fichas completas, fichas medicas e contatos de emergencia.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Operacao</p>
                            <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">
                                Frequencia mensal, aniversariantes, unidades e acompanhamento da equipe.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Gestao</p>
                            <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">
                                Financeiro, patrimonio, ranking e inadimplencia para decisao rapida.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Relatórios Rápidos</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Atalhos para os PDFs mais usados.</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3">
                        <a href="{{ route('relatorios.financeiro') }}" target="_blank" class="rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-700 dark:hover:border-emerald-700 dark:hover:bg-emerald-900/10">
                            <p class="font-semibold text-slate-900 dark:text-white">Relatório Financeiro Completo</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fluxo total de entradas e saidas.</p>
                        </a>

                        <a href="{{ route('relatorios.patrimonio') }}" target="_blank" class="rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-sky-300 hover:bg-sky-50 dark:border-slate-700 dark:hover:border-sky-700 dark:hover:bg-sky-900/10">
                            <p class="font-semibold text-slate-900 dark:text-white">Inventário Patrimonial</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Bens, quantidades e valor estimado.</p>
                        </a>

                        <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                            <input type="hidden" name="tipo" value="fichas_completas">
                            <input type="hidden" name="status" value="ativos">
                            <button type="submit" class="w-full rounded-2xl border border-slate-200 px-4 py-4 text-left transition hover:border-amber-300 hover:bg-amber-50 dark:border-slate-700 dark:hover:border-amber-700 dark:hover:bg-amber-900/10">
                                <p class="font-semibold text-slate-900 dark:text-white">Fichas Completas dos Ativos</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pacote pronto para secretaria e diretoria.</p>
                            </button>
                        </form>

                        <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank">
                            <input type="hidden" name="tipo" value="contatos_emergencia">
                            <input type="hidden" name="status" value="ativos">
                            <button type="submit" class="w-full rounded-2xl border border-slate-200 px-4 py-4 text-left transition hover:border-rose-300 hover:bg-rose-50 dark:border-slate-700 dark:hover:border-rose-700 dark:hover:bg-rose-900/10">
                                <p class="font-semibold text-slate-900 dark:text-white">Contatos de Emergencia</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lista rapida para saidas e eventos externos.</p>
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white">Gerador de Relatorio Personalizado</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Escolha um modelo, aplique filtros e abra o PDF em nova aba.</p>
                        </div>
                        <div class="rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-900/40 dark:text-slate-300">
                            Nenhum relatorio pre-selecionado
                        </div>
                    </div>
                </div>

                <form action="{{ route('relatorios.custom') }}" method="GET" target="_blank" class="p-6">

                    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.95fr]">
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="tipo" :value="__('Tipo de Relatório')" />
                                <select
                                    name="tipo"
                                    id="tipo"
                                    x-model="tipo"
                                    class="ui-input mt-2">
                                    <option value="">Selecione um relatorio</option>
                                    <option value="desbravadores">Lista de Desbravadores</option>
                                    <option value="fichas_completas">Fichas Completas dos Desbravadores</option>
                                    <option value="fichas_medicas">Fichas Medicas em Lote</option>
                                    <option value="contatos_emergencia">Contatos de Emergencia</option>
                                    <option value="frequencia">Frequencia Consolidada</option>
                                    <option value="inadimplencia">Inadimplencia</option>
                                    <option value="aniversariantes">Aniversariantes</option>
                                    <option value="unidades">Estrutura das Unidades</option>
                                    <option value="ranking_unidades">Ranking das Unidades</option>
                                    <option value="ranking_desbravadores">Ranking Individual</option>
                                    <option value="financeiro">Financeiro por Filtro</option>
                                    <option value="patrimonio">Inventário Patrimonial</option>
                                </select>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    Escolha o tipo primeiro para liberar os filtros corretos.
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pessoas e cadastro</p>
                                    <ul class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                                        <li>Lista resumida de desbravadores</li>
                                        <li>Ficha completa com vinculos e historico</li>
                                        <li>Fichas medicas e contatos de emergencia</li>
                                        <li>Aniversariantes do mes</li>
                                    </ul>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Visão gerencial</p>
                                    <ul class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                                        <li>Frequencia mensal consolidada</li>
                                        <li>Inadimplencia de mensalidades</li>
                                        <li>Estrutura, ranking e patrimonio</li>
                                        <li>Financeiro filtrado por periodo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-900/40">
                            <h4 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Filtros</h4>

                            <div x-show="!tipo" class="mt-6">
                                <x-empty-state
                                    title="Nenhum relatorio selecionado"
                                    description="Escolha um modelo para habilitar os filtros especificos." />
                            </div>

                            <div x-show="['desbravadores','fichas_completas','fichas_medicas','contatos_emergencia','frequencia','inadimplencia','aniversariantes'].includes(tipo)" x-cloak class="mt-4 space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Status</label>
                                    <select name="status" class="ui-input mt-2">
                                        <option value="ativos">Somente ativos</option>
                                        <option value="todos">Todos os cadastrados</option>
                                        <option value="inativos">Somente inativos</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Unidade</label>
                                    <select name="unidade_id" class="ui-input mt-2">
                                        <option value="">Todas as unidades</option>
                                        @foreach ($unidades as $unidade)
                                            <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div x-show="tipo === 'frequencia'" x-cloak class="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Mes</label>
                                    <input type="number" min="1" max="12" name="mes" value="{{ now()->month }}" class="ui-input mt-2">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Ano</label>
                                    <input type="number" min="2020" max="2100" name="ano" value="{{ now()->year }}" class="ui-input mt-2">
                                </div>
                            </div>

                            <div x-show="tipo === 'aniversariantes'" x-cloak class="mt-4">
                                <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Mes de aniversario</label>
                                <input type="number" min="1" max="12" name="mes_aniversario" value="{{ now()->month }}" class="ui-input mt-2">
                            </div>

                            <div x-show="tipo === 'financeiro'" x-cloak class="mt-4 space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">De</label>
                                        <input type="date" name="data_inicio" class="ui-input mt-2">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Ate</label>
                                        <input type="date" name="data_fim" class="ui-input mt-2">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de movimentacao</label>
                                    <select name="tipo_movimentacao" class="ui-input mt-2">
                                        <option value="todos">Entradas e saidas</option>
                                        <option value="entrada">Somente entradas</option>
                                        <option value="saida">Somente saidas</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="['ranking_unidades','ranking_desbravadores','unidades','patrimonio'].includes(tipo)" x-cloak class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-900/10 dark:text-amber-200">
                                Esse relatorio usa a base consolidada do sistema para uma visao geral de gestao.
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Os relatorios sao gerados em PDF e abertos em nova aba para impressao ou download.
                        </p>
                        <button
                            type="submit"
                            :disabled="!tipo"
                            class="ui-btn-primary disabled:cursor-not-allowed disabled:opacity-40">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v10m0 0l-3-3m3 3l3-3M4 19h16" />
                            </svg>
                            Gerar Relatório em PDF
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
