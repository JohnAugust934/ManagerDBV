<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">{{ __('Gerar Novo Convite') }}</h2>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-3xl mx-auto">
            <div class="ui-card p-6 md:p-8">
                <form action="{{ route('invites.store') }}" method="POST" class="space-y-7">
                    @csrf

                    <div>
                        <x-input-label for="email" value="E-mail do Convidado" />
                        <x-text-input id="email" class="block w-full mt-1" type="email" name="email"
                            :value="old('email')" placeholder="Ex: conselheiro@email.com" required />
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">O link sera exclusivo e vinculado a este e-mail.</p>
                        @error('email')
                            <p class="text-red-500 text-sm mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <h3 class="ui-title text-base mb-3">Cargo Padrao do Convite</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach (['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="{{ $role }}" class="peer sr-only" required {{ old('role') == $role? 'checked' : '' }}>
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-3 text-center font-semibold uppercase text-xs bg-white dark:bg-gray-800 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20">{{ $role }}</div>
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
                        <p class="text-xs text-gray-500 mt-2">O link para de funcionar apos o dia selecionado.</p>
                        @error('expires_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('invites.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto">Criar Convite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
