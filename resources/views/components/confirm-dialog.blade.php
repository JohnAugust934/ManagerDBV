{{--
    Diálogo de confirmação global e único do sistema.
    Disparado por: window.confirmAction({ message, formId, url, method, title, confirmText, cancelText, variant, payload })
    - formId  : id de um <form> existente que será submetido ao confirmar.
    - url      : ao invés de formId, envia um form montado na hora para esta URL (usa method e CSRF).
    - method   : verbo HTTP quando usar 'url' (ex.: 'DELETE', 'POST'). Padrão 'POST'.
    - payload  : qualquer dado; se não houver formId nem url, dispara o evento 'confirm-accepted' com { payload }.
    - variant  : 'danger' (padrão) | 'primary'
--}}
<div
    x-data="{
        open: false,
        title: '',
        message: '',
        confirmText: 'Confirmar',
        cancelText: 'Cancelar',
        variant: 'danger',
        formId: null,
        url: null,
        method: 'POST',
        payload: null,
        show(detail) {
            this.title = detail.title || (detail.variant === 'primary' ? 'Confirmar ação' : 'Confirmar exclusão');
            this.message = detail.message || 'Tem certeza que deseja continuar?';
            this.confirmText = detail.confirmText || (detail.variant === 'primary' ? 'Confirmar' : 'Excluir');
            this.cancelText = detail.cancelText || 'Cancelar';
            this.variant = detail.variant || 'danger';
            this.formId = detail.formId || null;
            this.url = detail.url || null;
            this.method = (detail.method || 'POST').toUpperCase();
            this.payload = detail.payload ?? null;
            this.open = true;
            document.body.classList.add('overflow-y-hidden');
            this.$nextTick(() => this.$refs.confirmBtn && this.$refs.confirmBtn.focus());
        },
        close() {
            this.open = false;
            document.body.classList.remove('overflow-y-hidden');
        },
        accept() {
            const formId = this.formId;
            const url = this.url;
            const method = this.method;
            const payload = this.payload;
            this.close();
            if (formId) {
                const form = document.getElementById(formId);
                if (form) form.submit();
            } else if (url) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = document.querySelector('meta[name=csrf-token]').content;
                form.appendChild(token);
                if (method !== 'POST') {
                    const verb = document.createElement('input');
                    verb.type = 'hidden';
                    verb.name = '_method';
                    verb.value = method;
                    form.appendChild(verb);
                }
                document.body.appendChild(form);
                form.submit();
            } else {
                window.dispatchEvent(new CustomEvent('confirm-accepted', { detail: { payload } }));
            }
        }
    }"
    x-on:open-confirm.window="show($event.detail || {})"
    x-on:keydown.escape.window="open && close()"
    x-show="open"
    class="fixed inset-0 z-[60] flex items-center justify-center px-4 py-6"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    :aria-label="title"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="absolute inset-0 bg-gray-900/75 dark:bg-black/80 backdrop-blur-sm"
    ></div>

    {{-- Card --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full sm:max-w-md bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-slate-700 overflow-hidden"
    >
        <div class="p-6 sm:p-7">
            <div class="flex items-start gap-4">
                {{-- Ícone --}}
                <div
                    class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                    :class="variant === 'primary'
                        ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400'
                        : 'bg-red-100 dark:bg-red-500/20 text-red-500 dark:text-red-400'"
                >
                    <svg x-show="variant !== 'primary'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <svg x-show="variant === 'primary'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="flex-1 min-w-0 pt-0.5">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight" x-text="title"></h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400" x-text="message"></p>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-7">
                <button type="button" @click="close()" class="ui-btn-secondary px-6 w-full sm:w-auto text-sm" x-text="cancelText"></button>
                <button
                    type="button"
                    x-ref="confirmBtn"
                    @click="accept()"
                    class="px-6 w-full sm:w-auto text-sm"
                    :class="variant === 'primary' ? 'ui-btn-primary' : 'ui-btn-danger'"
                    x-text="confirmText"
                ></button>
            </div>
        </div>
    </div>
</div>
