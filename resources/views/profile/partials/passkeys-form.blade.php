<section
    id="passkeys"
    x-data="painelPasskeys({
        indexUrl: '{{ route('passkeys.index') }}',
        optionsUrl: '{{ route('passkeys.options') }}',
        storeUrl: '{{ route('passkeys.store') }}',
        destroyBaseUrl: '{{ url('passkeys') }}',
    })"
    x-init="carregar()"
>
    <header class="mb-6">
        <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-[#FCD116]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Passkeys
        </h2>
        <p class="mt-2 text-[14px] text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
            Entre sem digitar senha usando a biometria ou o PIN do seu dispositivo. As passkeys
            são um método adicional e seguro — a sua senha continua válida.
        </p>
    </header>

    {{-- Aviso quando o navegador não suporta WebAuthn --}}
    <div x-show="!suportado" x-cloak
        class="mb-6 rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 px-4 py-3 text-sm font-medium text-amber-800 dark:text-amber-300">
        Este navegador não suporta passkeys. Tente um navegador mais recente ou outro dispositivo.
    </div>

    {{-- Feedback --}}
    <div x-show="erro" x-cloak x-text="erro"
        class="mb-4 rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm font-medium text-red-700 dark:text-red-300"></div>
    <div x-show="sucesso" x-cloak x-text="sucesso"
        class="mb-4 rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300"></div>

    {{-- Cadastro de nova passkey --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end gap-3" x-show="suportado">
        <div class="flex-1">
            <label for="passkey_nome" class="ui-input-label">Nome da passkey (opcional)</label>
            <input id="passkey_nome" type="text" x-model="nome" maxlength="255"
                class="ui-input" placeholder="Ex.: Meu celular, Notebook do trabalho">
        </div>
        <button type="button" @click="cadastrar()" :disabled="carregando"
            class="ui-btn-primary w-full sm:w-auto disabled:opacity-60 disabled:cursor-not-allowed">
            <span x-show="!carregando">Cadastrar passkey</span>
            <span x-show="carregando" x-cloak>Aguarde…</span>
        </button>
    </div>

    {{-- Lista de passkeys --}}
    <div class="space-y-3">
        <template x-if="carregandoLista">
            <p class="text-sm text-slate-500 dark:text-slate-400">Carregando passkeys…</p>
        </template>

        <template x-if="!carregandoLista && passkeys.length === 0">
            <div class="ui-card-muted p-5 text-center text-sm text-slate-500 dark:text-slate-400">
                Você ainda não cadastrou nenhuma passkey.
            </div>
        </template>

        <template x-for="pk in passkeys" :key="pk.id">
            <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/40 px-4 py-3">
                <div class="min-w-0">
                    <p class="font-bold text-slate-800 dark:text-white truncate" x-text="pk.name || 'Passkey'"></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Criada em ' + formatarData(pk.created_at)"></p>
                </div>
                <button type="button" @click="remover(pk)" :disabled="carregando"
                    class="ui-btn-danger w-auto text-sm px-4 py-2 disabled:opacity-60 disabled:cursor-not-allowed">
                    Remover
                </button>
            </div>
        </template>
    </div>
</section>

@once
    <script>
        function painelPasskeys(config) {
                return {
                    suportado: window.Passkeys ? window.Passkeys.suportado() : false,
                    passkeys: [],
                    nome: '',
                    erro: '',
                    sucesso: '',
                    carregando: false,
                    carregandoLista: true,

                    async carregar() {
                        this.carregandoLista = true;
                        try {
                            const resp = await fetch(config.indexUrl, {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });
                            if (resp.ok) {
                                const dados = await resp.json();
                                this.passkeys = dados.passkeys ?? [];
                            }
                        } catch (_) {
                            /* silencioso: lista vazia */
                        } finally {
                            this.carregandoLista = false;
                        }
                    },

                    async cadastrar() {
                        this.erro = '';
                        this.sucesso = '';
                        this.carregando = true;
                        try {
                            await window.Passkeys.registrar({
                                optionsUrl: config.optionsUrl,
                                storeUrl: config.storeUrl,
                                nome: this.nome,
                            });
                            this.sucesso = 'Passkey cadastrada com sucesso.';
                            this.nome = '';
                            await this.carregar();
                        } catch (e) {
                            this.erro = e.message;
                        } finally {
                            this.carregando = false;
                        }
                    },

                    async remover(pk) {
                        this.erro = '';
                        this.sucesso = '';
                        this.carregando = true;
                        try {
                            const resp = await fetch(config.destroyBaseUrl + '/' + pk.id, {
                                method: 'DELETE',
                                headers: {
                                    Accept: 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });
                            if (resp.ok) {
                                this.passkeys = this.passkeys.filter((p) => p.id !== pk.id);
                                this.sucesso = 'Passkey removida.';
                            } else {
                                this.erro = 'Não foi possível remover a passkey.';
                            }
                        } catch (_) {
                            this.erro = 'Não foi possível remover a passkey.';
                        } finally {
                            this.carregando = false;
                        }
                    },

                    formatarData(iso) {
                        if (!iso) return '—';
                        try {
                            return new Date(iso).toLocaleString('pt-BR', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                            });
                        } catch (_) {
                            return iso;
                        }
                    },
                };
            }
    </script>
@endonce
