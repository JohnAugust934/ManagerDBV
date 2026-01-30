<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Enviar Novo Convite
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <form action="{{ route('invites.store') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <x-input-label for="email" value="E-mail do Convidado" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus
                            placeholder="exemplo@email.com" />
                        <p class="text-xs text-gray-500 mt-1">O link de cadastro será enviado para este e-mail.</p>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <hr class="mb-6 border-gray-200 dark:border-gray-700">

                    <div class="mb-6">
                        @if($existingClub)
                        <div class="bg-green-50 border-l-4 border-green-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        Este usuário será vinculado automaticamente ao clube: <br>
                                        <span class="font-bold uppercase">{{ $existingClub->nome }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Nenhum clube encontrado. <br>
                                        <strong>Este usuário deverá cadastrar o Clube no primeiro acesso.</strong>
                                        (Recomendado convidar um Diretor).
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">Cargo do Usuário</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach(['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                            <label class="flex items-center space-x-2 p-3 border rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 dark:border-gray-600 transition">
                                <input type="radio" name="role" value="{{ $role }}" class="text-indigo-600 focus:ring-indigo-500"
                                    {{ $role == 'conselheiro' ? 'checked' : '' }}>
                                <span class="capitalize font-bold text-gray-700 dark:text-gray-300">{{ ucfirst($role) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-md border border-blue-200 dark:border-blue-800">
                        <h3 class="text-sm font-bold text-blue-800 dark:text-blue-500 mb-2 uppercase">Permissões Adicionais (Opcional)</h3>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-4">Marque apenas se quiser liberar um módulo que o cargo acima NÃO tem acesso por padrão.</p>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach(\App\Models\User::PERMISSOES as $key => $label)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="extra_permissions[]" value="{{ $key }}" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('invites.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white">Cancelar</a>
                        <x-primary-button>Gerar Convite</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>