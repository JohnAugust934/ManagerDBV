{{-- Convite pós-login para cadastrar uma passkey. Aparece só para quem ainda
     não tem passkey e não dispensou o convite; a dispensa é persistida por
     usuário (não reaparece). Mobile-first, aditivo à senha. --}}
@auth
    @if (auth()->user()->deveVerBannerPasskey())
        <div
            x-data="{
                visivel: true,
                dispensar() {
                    this.visivel = false;
                    if (window.Passkeys) {
                        window.Passkeys.dispensarBanner('{{ route('passkeys.banner.dismiss') }}');
                    }
                },
            }"
            x-show="visivel" x-cloak x-transition.opacity
            class="mb-6 rounded-2xl border border-blue-200 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/10 px-4 py-3 sm:px-5 sm:py-4"
        >
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <div class="flex items-start gap-3 min-w-0 flex-1">
                    <svg class="w-6 h-6 shrink-0 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800 dark:text-white">Entre sem senha com uma passkey</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Use a biometria ou o PIN do seu dispositivo para um login mais rápido e seguro.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('profile.edit') }}#passkeys" class="ui-btn-primary w-full sm:w-auto text-sm">
                        Cadastrar agora
                    </a>
                    <button type="button" @click="dispensar()"
                        class="ui-btn-secondary w-full sm:w-auto text-sm">
                        Agora não
                    </button>
                </div>
            </div>
        </div>
    @endif
@endauth
