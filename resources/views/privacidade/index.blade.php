<x-app-layout>
    <div class="ui-page space-y-6 max-w-4xl ui-animate-fade-up" x-data="{ revogar: false, verTermo: false }">

        {{-- Ações --}}
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 px-2 sm:px-0">
            <a href="{{ route('desbravadores.show', $desbravador) }}" class="ui-btn-secondary w-full sm:w-auto text-sm">
                Voltar ao perfil
            </a>
            <a href="{{ route('relatorios.termo-privacidade', $desbravador) }}" class="ui-btn-secondary w-full sm:w-auto text-sm" target="_blank">
                Imprimir termo (PDF)
            </a>
        </div>

        {{-- Cabeçalho --}}
        <div class="ui-card p-6">
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Termo de Privacidade (LGPD)</p>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $desbravador->nome }}</h1>

            <div class="mt-4">
                @if ($ativo)
                    <span class="ui-badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-3 py-1">
                        Consentimento ativo desde {{ $ativo->aceito_em?->format('d/m/Y') }}
                    </span>
                @else
                    <span class="ui-badge bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-3 py-1">
                        Consentimento pendente / revogado
                    </span>
                @endif
            </div>
        </div>

        {{-- Ações de consentimento --}}
        <div class="ui-card p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Registrar</h2>

            <div class="flex flex-wrap gap-3">
                @if (! $ativo)
                    <form method="POST" action="{{ route('privacidade.aceitar', $desbravador) }}">
                        @csrf
                        <input type="hidden" name="versao_termo" value="{{ config('privacidade.versao_termo') }}">
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto text-sm">
                            Registrar consentimento (versão {{ config('privacidade.versao_termo') }})
                        </button>
                    </form>
                @else
                    <button type="button" @click="revogar = true" class="ui-btn-danger w-full sm:w-auto text-sm">
                        Revogar consentimento atual
                    </button>
                @endif

                <button type="button" @click="verTermo = !verTermo" class="ui-btn-secondary w-full sm:w-auto text-sm">
                    Ver texto do termo
                </button>
            </div>

            {{-- Via física --}}
            <form method="POST" action="{{ route('privacidade.via-fisica', $desbravador) }}" enctype="multipart/form-data"
                  class="pt-4 border-t border-slate-100 dark:border-slate-700 space-y-3">
                @csrf
                <p class="text-sm font-bold text-slate-600 dark:text-slate-300">Via física assinada</p>
                <input type="file" name="arquivo" accept=".pdf,.jpg,.jpeg,.png"
                       class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 dark:file:bg-slate-700 dark:file:text-slate-200">
                <button type="submit" class="ui-btn-secondary w-full sm:w-auto text-sm">
                    Marcar via física recebida
                </button>
            </form>

            {{-- Texto do termo (colapsável) --}}
            <div x-show="verTermo" x-cloak class="pt-4 border-t border-slate-100 dark:border-slate-700 prose prose-sm dark:prose-invert max-w-none">
                @include('privacidade.termo', ['versao' => config('privacidade.versao_termo')])
            </div>
        </div>

        {{-- Linha do tempo / histórico --}}
        <div class="ui-card p-6">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Histórico</h2>

            @forelse ($consentimentos as $c)
                <div class="relative pl-6 pb-6 border-l-2 border-slate-200 dark:border-slate-700 last:pb-0">
                    <span class="absolute -left-[7px] top-1 w-3 h-3 rounded-full {{ $c->estaAtivo() ? 'bg-emerald-500' : ($c->revogado_em ? 'bg-red-500' : 'bg-slate-400') }}"></span>

                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                        Aceito em {{ $c->aceito_em?->format('d/m/Y H:i') ?? '—' }}
                        <span class="font-normal text-slate-400">· versão {{ $c->versao_termo }}</span>
                    </p>
                    @if ($c->responsavel_nome)
                        <p class="text-xs text-slate-500">Responsável: {{ $c->responsavel_nome }}</p>
                    @endif
                    @if ($c->via_fisica_recebida_em)
                        <p class="text-xs text-slate-500">Via física recebida em {{ $c->via_fisica_recebida_em->format('d/m/Y') }}</p>
                    @endif
                    @if ($c->revogado_em)
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                            Revogado em {{ $c->revogado_em->format('d/m/Y H:i') }} por {{ $c->revogado_por }}.
                            Motivo: {{ $c->motivo_revogacao }}
                        </p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400">Nenhum registro de consentimento ainda.</p>
            @endforelse
        </div>

        {{-- Modal de revogação --}}
        @if ($ativo)
            <div x-show="revogar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="revogar = false">
                <div class="ui-card p-6 w-full max-w-md space-y-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Revogar consentimento</h3>
                    <p class="text-sm text-slate-500">O registro atual será encerrado; o histórico é mantido.</p>
                    <form method="POST" action="{{ route('privacidade.revogar', [$desbravador, $ativo]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-slate-600 dark:text-slate-300 mb-1">Motivo da revogação</label>
                            <textarea name="motivo_revogacao" rows="3" required maxlength="1000"
                                      class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="revogar = false" class="ui-btn-secondary text-sm">Cancelar</button>
                            <button type="submit" class="ui-btn-danger text-sm">Confirmar revogação</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
