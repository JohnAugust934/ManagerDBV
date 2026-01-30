<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestão de Acessos e Usuários
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col md:flex-row gap-4 justify-between bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-100 text-indigo-700 p-2 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-white">Usuários Ativos</h3>
                        <p class="text-xs text-gray-500">Total: {{ $users->count() }}</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('invites.index') }}" class="flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition text-sm font-bold">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Ver Convites Pendentes
                    </a>
                    <a href="{{ route('invites.create') }}" class="flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition text-sm font-bold shadow">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Convidar Novo Usuário
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-6 py-3">Nome</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Cargo (Role)</th>
                                <th class="px-6 py-3">Permissões Extras</th>
                                <th class="px-6 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $user->name }}</td>
                                <td class="px-6 py-4">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                        {{ $user->role == 'master' ? 'bg-red-100 text-red-800' : 
                                          ($user->role == 'diretor' ? 'bg-blue-100 text-blue-800' : 
                                          ($user->role == 'tesoureiro' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($user->extra_permissions)
                                    {{ implode(', ', $user->extra_permissions) }}
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <a href="{{ route('usuarios.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Editar</a>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este usuário?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium ml-2">Excluir</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>