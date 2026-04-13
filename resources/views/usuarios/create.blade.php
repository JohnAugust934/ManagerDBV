<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Novo Usuario
        </h2>
    </x-slot>

    <div class="ui-page">
        <div class="max-w-4xl mx-auto">
            <div class="ui-card p-6 md:p-8">
                <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-7">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" value="Nome Completo" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="E-mail de Acesso" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password" value="Senha" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" value="Confirmar Senha" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <h3 class="ui-title text-base mb-3">Definicao de Cargo</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach(['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="{{ $role }}" class="peer sr-only" {{ $role == 'conselheiro'? 'checked' : '' }}>
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-3 text-center font-semibold capitalize bg-white dark:bg-gray-800 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20">
                                        {{ ucfirst($role) }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="ui-card-muted p-4">
                        <h3 class="text-sm font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wide mb-1">Permissões Adicionais (Opcional)</h3>
                        <p class="text-xs text-amber-700/80 dark:text-amber-500 mb-3">Marque apenas para liberar modulos fora do cargo principal.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach(\App\Models\User::PERMISSOES as $key => $label)
                                @if ($key === 'gestao_acessos' && !($canGrantAccessManagement ?? false))
                                    @continue
                                @endif
                                <label class="flex items-center space-x-2 rounded-lg border border-amber-100 dark:border-amber-800/30 bg-white/80 dark:bg-slate-900/30 px-3 py-2">
                                    <input type="checkbox" name="extra_permissions[]" value="{{ $key }}" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('usuarios.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto">Criar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

