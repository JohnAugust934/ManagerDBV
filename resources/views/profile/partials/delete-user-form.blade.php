<section class="space-y-6" x-data="{ confirmingUserDeletion: false }">
    <header class="mb-6 border-b border-red-200 dark:border-red-900/50 pb-6">
        <h2 class="text-xl font-black text-red-600 dark:text-red-500 tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Excluir Conta
        </h2>
        <p class="mt-2 text-[14px] text-red-800 dark:text-red-400 font-medium leading-relaxed">
            Uma vez que sua conta for excluída, todos os seus recursos e dados serão apagados permanentemente. Antes de excluir, por favor, faça o download de qualquer dado que deseja manter.
        </p>
    </header>

    <button type="button" class="ui-btn-danger px-6" @click="confirmingUserDeletion = true">
        Excluir Minha Conta
    </button>

    <!-- Custom Alpine Modal -->
    <div x-show="confirmingUserDeletion" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="confirmingUserDeletion" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto w-full">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!-- Modal Panel -->
                <div x-show="confirmingUserDeletion" @click.away="confirmingUserDeletion = false" @keydown.escape.window="confirmingUserDeletion = false"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative transform overflow-hidden rounded-[32px] ui-glass shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <form method="post" action="{{ route('profile.destroy') }}" class="p-8">
                        @csrf
                        @method('delete')

                        <div class="flex items-center gap-4 mb-4 text-red-600 dark:text-red-400">
                            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h2 class="text-xl font-black text-slate-800 dark:text-white text-left tracking-tight">
                                Tem certeza que quer excluir?
                            </h2>
                        </div>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-left font-medium leading-relaxed">
                            Uma vez que a conta for excluída, tudo será removido. Por favor, insira sua senha para confirmar.
                        </p>

                        <div class="mt-6 text-left">
                            <label for="password" class="ui-input-label sr-only">Senha</label>
                            <input id="password" name="password" type="password" class="ui-input" placeholder="Sua Senha" x-ref="password" />
                            @if($errors->userDeletion->has('password'))
                                <p class="ui-input-error mt-2">{{ $errors->userDeletion->first('password') }}</p>
                            @endif
                        </div>

                        <div class="mt-8 flex justify-end gap-3">
                            <button type="button" @click="confirmingUserDeletion = false" class="ui-btn-secondary px-6">
                                Cancelar
                            </button>
                            <button type="submit" class="ui-btn-danger px-8">
                                Excluir Conta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
