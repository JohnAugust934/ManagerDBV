<x-app-layout>

    <div class="ui-page">
        <div class="max-w-3xl mx-auto space-y-6">
            <x-page-title title="Novo Convite" subtitle="Gere um link de cadastro exclusivo e vinculado a um e-mail." :back="route('invites.index')" />

            <div class="ui-card p-6 md:p-8">
                <form action="{{ route('invites.store') }}" method="POST" class="space-y-7">
                    @csrf

                    <div>
                        <x-input-label for="email" value="E-mail do Convidado" />
                        <x-text-input id="email" class="block w-full mt-1" type="email" name="email"
                            :value="old('email')" placeholder="Ex: conselheiro@email.com" required />
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">O link sera exclusivo e vinculado a este e-mail.</p>
                        @error('email')
                            <p class="text-red-500 text-sm mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                        @php $rolesList = $roles ?? ['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor']; @endphp
                        <h3 class="ui-title text-base mb-3">{{ ($isPlatformContext ?? false) ? 'Tipo de Convite' : 'Cargo Padrao do Convite' }}</h3>
                        @if ($isPlatformContext ?? false)
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">O convidado entrará como <strong>Admin da Plataforma</strong> (acesso cross-tenant, sem clube).</p>
                        @endif
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($rolesList as $role)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="{{ $role }}" class="peer sr-only" required {{ old('role', count($rolesList) === 1 ? $role : null) == $role ? 'checked' : '' }}>
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-3 text-center font-semibold uppercase text-xs bg-white dark:bg-slate-800 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20">{{ \App\Models\User::ROLES_LABEL[$role] ?? $role }}</div>
                                </label>
                            @endforeach
                        </div>
                        @error('role')
                            <p class="text-red-500 text-sm mt-2 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="ui-card-muted p-4">
                        <x-input-label for="expires_at" value="Data de Expiracao (Opcional)" />
                        <x-text-input id="expires_at" class="block mt-1 w-full md:w-1/2" type="date" name="expires_at" :value="old('expires_at')" />
                        <p class="text-xs text-slate-500 mt-2">O link para de funcionar apos o dia selecionado.</p>
                        @error('expires_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <a href="{{ route('invites.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto">Criar Convite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
