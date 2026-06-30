<div x-data="pwaInstall()" x-show="mostrar" x-cloak
     class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-sm z-50">
    <div class="ui-card p-4 shadow-xl border border-[#002F6C]/20 dark:border-blue-800/50 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-[#002F6C] flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-black text-slate-800 dark:text-white leading-none">Instalar o App</p>
            <p class="text-xs text-slate-500 mt-1">Acesse mais rápido e use offline em reuniões</p>
        </div>
        <div class="flex flex-col gap-2 shrink-0">
            <button @click="instalar()" class="text-xs font-black text-[#002F6C] dark:text-blue-400 uppercase tracking-widest">
                Instalar
            </button>
            <button @click="dispensar()" class="text-xs text-slate-400 uppercase tracking-widest">
                Agora não
            </button>
        </div>
    </div>
</div>

<script>
    function pwaInstall() {
        return {
            mostrar: false,
            promptEvento: null,
            init() {
                if (localStorage.getItem('pwa-dispensado')) return;
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.promptEvento = e;
                    this.mostrar = true;
                });
            },
            instalar() {
                if (!this.promptEvento) return;
                this.promptEvento.prompt();
                this.promptEvento.userChoice.then(() => { this.mostrar = false; });
            },
            dispensar() {
                this.mostrar = false;
                localStorage.setItem('pwa-dispensado', '1');
            }
        };
    }
</script>
