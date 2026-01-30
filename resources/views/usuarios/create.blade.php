<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Novo Usuário
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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

                    <hr class="mb-6">

                    <div class="mb-6">
                        <h3 class="text-lg font-bold mb-4">Definição de Cargo</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach(['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                            <label class="flex items-center space-x-2 p-3 border rounded cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="role" value="{{ $role }}" class="text-indigo-600 focus:ring-indigo-500" {{ $role == 'conselheiro' ? 'checked' : '' }}>
                                <span class="capitalize font-bold">{{ ucfirst($role) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-6 bg-yellow-50 p-4 rounded-md border border-yellow-200">
                        <h3 class="text-sm font-bold text-yellow-800 mb-2 uppercase">Permissões Adicionais (Opcional)</h3>
                        <p class="text-xs text-yellow-600 mb-4">Marque apenas se quiser liberar um módulo que o cargo acima NÃO tem acesso por padrão.</p>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach(\App\Models\User::PERMISSOES as $key => $label)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="extra_permissions[]" value="{{ $key }}" class="rounded text-yellow-600 focus:ring-yellow-500">
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('usuarios.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</a>
                        <x-primary-button>Criar Usuário</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>