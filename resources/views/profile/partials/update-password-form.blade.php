<section>
    <header class="mb-6">
        <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-[#FCD116]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Atualizar Senha
        </h2>
        <p class="mt-2 text-[14px] text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
            Certifique-se de usar uma senha longa e aleatória para manter sua conta segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="ui-input-label">Senha Atual</label>
            <input id="update_password_current_password" name="current_password" type="password" class="ui-input" autocomplete="current-password" />
            @if($errors->updatePassword->has('current_password'))
                <p class="ui-input-error">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="ui-input-label">Nova Senha</label>
            <input id="update_password_password" name="password" type="password" class="ui-input" autocomplete="new-password" />
            @if($errors->updatePassword->has('password'))
                <p class="ui-input-error">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="ui-input-label">Confirmar Nova Senha</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="ui-input" autocomplete="new-password" />
            @if($errors->updatePassword->has('password_confirmation'))
                <p class="ui-input-error">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-6 pt-2">
            <button type="submit" class="ui-btn-secondary px-8 border-2">
                Alterar Senha
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms x-init="setTimeout(() => show = false, 3000)" class="text-[14px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1.5 rounded-lg border border-emerald-100 dark:border-emerald-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Senha atualizada.
                </p>
            @endif
        </div>
    </form>
</section>
