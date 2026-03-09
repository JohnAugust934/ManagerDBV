<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.index') }}"
                class="p-2 text-gray-500 hover:text-dbv-blue dark:text-gray-400 dark:hover:text-blue-400 bg-gray-100 dark:bg-gray-700 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-200 leading-tight">
                {{ __('Editar Acessos: ') }} <span
                    class="text-gray-600 dark:text-gray-400 font-medium">{{ $usuario->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="p-6 md:p-8">
                    @csrf
                    @method('PUT')

                    {{-- DADOS PESSOAIS --}}
                    <div class="mb-8">
                        <h3
                            class="text-base font-bold text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                            Dados do Usuário</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" value="Nome Completo"
                                    class="font-bold text-gray-700 dark:text-gray-300" />
                                <x-text-input id="name"
                                    class="block mt-1 w-full bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-600"
                                    type="text" name="name" :value="old('name', $usuario->name)" required />
                            </div>
                            <div>
                                <x-input-label for="email" value="E-mail de Acesso"
                                    class="font-bold text-gray-700 dark:text-gray-300" />
                                <x-text-input id="email"
                                    class="block mt-1 w-full bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-600"
                                    type="email" name="email" :value="old('email', $usuario->email)" required />
                            </div>
                        </div>
                    </div>

                    {{-- REDEFINIR SENHA --}}
                    <div
                        class="mb-8 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                            Redefinir Senha (Opcional)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-text-input id="password" class="block w-full text-sm" type="password"
                                    name="password" placeholder="Nova Senha" />
                            </div>
                            <div>
                                <x-text-input id="password_confirmation" class="block w-full text-sm" type="password"
                                    name="password_confirmation" placeholder="Confirme a Nova Senha" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 italic">Deixe em branco para manter a senha atual.</p>
                    </div>

                    {{-- CARGO OFICIAL (Cards) --}}
                    <div class="mb-8">
                        <h3
                            class="text-base font-bold text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                            Cargo Principal</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach (['master', 'diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="{{ $role }}"
                                        class="peer sr-only" {{ $usuario->role == $role ? 'checked' : '' }}>
                                    <div
                                        class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all peer-checked:border-dbv-blue peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 peer-checked:shadow-md text-center">
                                        <span
                                            class="block uppercase font-bold text-sm tracking-wider text-gray-600 dark:text-gray-300 peer-checked:text-dbv-blue dark:peer-checked:text-blue-400">
                                            {{ $role }}
                                        </span>
                                    </div>
                                    {{-- Ícone de Check quando selecionado --}}
                                    <div
                                        class="absolute top-2 right-2 hidden peer-checked:block text-dbv-blue dark:text-blue-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- PERMISSÕES EXTRAS --}}
                    <div
                        class="mb-8 bg-yellow-50 dark:bg-yellow-900/10 p-5 rounded-xl border border-yellow-200 dark:border-yellow-800/50">
                        <h3
                            class="text-sm font-bold text-yellow-800 dark:text-yellow-500 mb-1 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            Permissões Adicionais (Exceções)
                        </h3>
                        <p class="text-xs text-yellow-700 dark:text-yellow-600 mb-4">Só marque as caixas abaixo se
                            quiser dar um acesso que o cargo original da pessoa não possui.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach (\App\Models\User::PERMISSOES as $key => $label)
                                <label
                                    class="flex items-start space-x-3 p-3 bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-800/50 rounded-lg cursor-pointer hover:shadow-sm transition-shadow">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="extra_permissions[]" value="{{ $key }}"
                                            class="w-5 h-5 rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 dark:bg-gray-900 dark:border-gray-600 cursor-pointer"
                                            {{ in_array($key, $usuario->extra_permissions ?? []) ? 'checked' : '' }}>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-gray-800 dark:text-gray-200 capitalize">{{ $key }}</span>
                                        <span
                                            class="text-[10px] text-gray-500 leading-tight mt-0.5">{{ $label }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('usuarios.index') }}"
                            class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Cancelar</a>
                        <button type="submit"
                            class="bg-dbv-blue hover:bg-blue-800 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-blue-500/30 transform transition hover:-translate-y-0.5">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
