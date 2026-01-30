<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Usuário: {{ $usuario->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <x-input-label for="name" value="Nome Completo" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $usuario->name)" required />
                        </div>
                        <div>
                            <x-input-label for="email" value="E-mail" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $usuario->email)" required />
                        </div>
                        <div class="col-span-2">
                            <x-input-label for="password" value="Nova Senha (Deixe em branco para manter a atual)" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                        </div>
                        <div class="col-span-2">
                            <x-input-label for="password_confirmation" value="Confirmar Nova Senha" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                        </div>
                    </div>

                    <hr class="mb-6">

                    <div class="mb-6">
                        <h3 class="text-lg font-bold mb-4">Cargo</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach(['master', 'diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                            <label class="flex items-center space-x-2 p-3 border rounded cursor-pointer hover:bg-gray-50 {{ $usuario->role == $role ? 'bg-indigo-50 border-indigo-500' : '' }}">
                                <input type="radio" name="role" value="{{ $role }}" class="text-indigo-600 focus:ring-indigo-500" {{ $usuario->role == $role ? 'checked' : '' }}>
                                <span class="capitalize font-bold">{{ ucfirst($role) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-6 bg-yellow-50 p-4 rounded-md border border-yellow-200">
                        <h3 class="text-sm font-bold text-yellow-800 mb-2 uppercase">Permissões Adicionais</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach(\App\Models\User::PERMISSOES as $key => $label)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="extra_permissions[]" value="{{ $key }}" class="rounded text-yellow-600 focus:ring-yellow-500"
                                    {{ in_array($key, $usuario->extra_permissions ?? []) ? 'checked' : '' }}>
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('usuarios.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</a>
                        <x-primary-button>Salvar Alterações</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>