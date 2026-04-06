<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Editar Acessos: <span class="text-gray-600 dark:text-gray-400">{{ $usuario->name }}</span>
        </h2>
    </x-slot>

    <div class="ui-page space-y-6">
        <div class="px-4 sm:px-0 flex justify-start">
            <a href="{{ route('usuarios.index') }}" class="ui-btn-secondary w-full sm:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="ui-card p-6 md:p-8">
                <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-7">
                    @csrf
                    @method('PUT')

                    <div>
                        <h3 class="ui-title text-base mb-4">Dados do Usuario</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" value="Nome Completo" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $usuario->name)" required />
                            </div>
                            <div>
                                <x-input-label for="email" value="E-mail de Acesso" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $usuario->email)" required />
                            </div>
                        </div>
                    </div>

                    <div class="ui-card-muted p-4">
                        <h3 class="ui-title text-sm mb-3">Redefinir Senha (Opcional)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-text-input id="password" class="block w-full" type="password" name="password" placeholder="Nova Senha" />
                            <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" placeholder="Confirme a Nova Senha" />
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Deixe em branco para manter a senha atual.</p>
                    </div>

                    <div>
                        <h3 class="ui-title text-base mb-3">Cargo Principal</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach (['master', 'diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="{{ $role }}" class="peer sr-only" {{ $usuario->role == $role? 'checked' : '' }}>
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-3 text-center font-semibold uppercase text-xs bg-white dark:bg-gray-800 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20">
                                        {{ $role }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="ui-card-muted p-4">
                        <h3 class="text-sm font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wide mb-1">Permissões Adicionais</h3>
                        <p class="text-xs text-amber-700/80 dark:text-amber-500 mb-3">Use apenas para liberar excecoes ao cargo principal.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach (\App\Models\User::PERMISSOES as $key => $label)
                                <label class="flex items-start space-x-2 rounded-lg border border-amber-100 dark:border-amber-800/30 bg-white/80 dark:bg-slate-900/30 px-3 py-2">
                                    <input type="checkbox" name="extra_permissions[]" value="{{ $key }}"
                                        class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                        {{ in_array($key, $usuario->extra_permissions?? [])? 'checked' : '' }}>
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('usuarios.index') }}" class="ui-btn-secondary w-full sm:w-auto">Cancelar</a>
                        <button type="submit" class="ui-btn-primary w-full sm:w-auto">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

