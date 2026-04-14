<section>
    <header class="mb-6">
        <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Informações do Perfil
        </h2>
        <p class="mt-2 text-[14px] text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
            Atualize as informações de perfil da sua conta e o endereço de e-mail.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="ui-input-label">Nome Completo</label>
            <input id="name" name="name" type="text" class="ui-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @if($errors->has('name'))
                <p class="ui-input-error">{{ $errors->first('name') }}</p>
            @endif
        </div>

        <div>
            <label for="email" class="ui-input-label">Endereço de E-mail</label>
            <input id="email" name="email" type="email" class="ui-input" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @if($errors->has('email'))
                <p class="ui-input-error">{{ $errors->first('email') }}</p>
            @endif

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800/50">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-400">
                        Seu endereço de e-mail não foi verificado.
                        <button form="send-verification" class="mt-2 text-sm text-[#002F6C] dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 underline underline-offset-4 decoration-2 font-bold transition-colors">
                            Clique aqui para reenviar o e-mail de verificação.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2 bg-emerald-50 dark:bg-emerald-900/20 p-2.5 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Um novo link de verificação foi enviado para o seu e-mail.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-6 pt-2">
            <button type="submit" class="ui-btn-primary px-8">
                Salvar Alterações
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms x-init="setTimeout(() => show = false, 3000)" class="text-[14px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1.5 rounded-lg border border-emerald-100 dark:border-emerald-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Salvo com sucesso.
                </p>
            @endif
        </div>
    </form>
</section>
