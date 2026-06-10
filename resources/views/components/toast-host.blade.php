{{--
    Container global de toasts. Disparado por: window.notify(message, type)
    type: 'success' | 'error' | 'warning' | 'info'
    Reaproveita o estilo .ui-alert e as cores/ícones do <x-flash-messages />.
--}}
@php
    $styles = [
        'success' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300',
        'error'   => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40 text-red-800 dark:text-red-300',
        'warning' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800/40 text-amber-800 dark:text-amber-300',
        'info'    => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800/40 text-blue-800 dark:text-blue-300',
    ];
    $iconBg = [
        'success' => 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400',
        'error'   => 'bg-red-100 dark:bg-red-500/20 text-red-500 dark:text-red-400',
        'warning' => 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400',
        'info'    => 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400',
    ];
    $icons = [
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>',
        'error'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
        'info'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];
@endphp

<div
    x-data="{
        toasts: [],
        seq: 0,
        styles: @js($styles),
        iconBg: @js($iconBg),
        icons: @js($icons),
        push(detail) {
            const type = ['success','error','warning','info'].includes(detail.type) ? detail.type : 'info';
            const id = ++this.seq;
            this.toasts.push({ id, type, message: detail.message || '' });
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-on:app-notify.window="push($event.detail || {})"
    class="fixed top-4 right-4 z-[70] w-[calc(100%-2rem)] sm:w-96 space-y-3 pointer-events-none"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-4"
            class="ui-alert pointer-events-auto"
            :class="styles[toast.type]"
            :role="toast.type === 'error' ? 'alert' : 'status'"
        >
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" :class="iconBg[toast.type]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-html="icons[toast.type]"></svg>
                </div>
                <p class="flex-1 text-sm font-semibold" x-text="toast.message"></p>
                <button type="button" @click="remove(toast.id)" class="w-7 h-7 rounded-lg flex items-center justify-center text-current/50 hover:text-current hover:bg-current/10 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </template>
</div>
