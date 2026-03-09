<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-200 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                </path>
            </svg>
            {{ __('Gestão de Usuários') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">

                {{-- CABEÇALHO DO CARD COM O BOTÃO DE CONVITES --}}
                <div
                    class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50/50 dark:bg-gray-800/50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Equipe do Clube</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Gerencie os acessos e cargos dos membros da
                            diretoria.</p>
                    </div>
                    <div class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('invites.index') }}"
                            class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-md shadow-green-500/20 transition-all flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                </path>
                            </svg>
                            Gerenciar Convites
                        </a>
                    </div>
                </div>

                {{-- LISTAGEM DE USUÁRIOS --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300 hidden md:table-header-group">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Usuário</th>
                                <th scope="col" class="px-6 py-4 font-bold">Cargo Oficial</th>
                                <th scope="col" class="px-6 py-4 font-bold">Permissões Extras</th>
                                <th scope="col" class="px-6 py-4 font-bold text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 flex flex-col md:table-row-group">
                            @foreach ($users as $user)
                                <tr
                                    class="flex flex-col md:table-row bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors p-4 md:p-0 gap-3 md:gap-0">

                                    {{-- Nome e Email --}}
                                    <td class="px-2 md:px-6 py-2 md:py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-dbv-blue text-white flex items-center justify-center font-bold text-lg shadow-inner shrink-0">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white text-base">
                                                    {{ $user->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Cargo --}}
                                    <td class="px-2 md:px-6 py-1 md:py-4">
                                        <span
                                            class="md:hidden text-xs font-semibold text-gray-400 uppercase mr-2">Cargo:</span>
                                        @php
                                            $roleColors = [
                                                'master' =>
                                                    'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
                                                'diretor' =>
                                                    'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
                                                'secretario' =>
                                                    'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800',
                                                'tesoureiro' =>
                                                    'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800',
                                            ];
                                            $colorClass =
                                                $roleColors[$user->role] ??
                                                'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
                                        @endphp
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $colorClass }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>

                                    {{-- Permissões Extras --}}
                                    <td class="px-2 md:px-6 py-1 md:py-4">
                                        <span
                                            class="md:hidden text-xs font-semibold text-gray-400 uppercase mr-2">Extras:</span>
                                        <div class="flex flex-wrap gap-1">
                                            @if ($user->extra_permissions && count($user->extra_permissions) > 0)
                                                @foreach ($user->extra_permissions as $perm)
                                                    <span
                                                        class="px-2 py-0.5 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-500 text-[10px] rounded font-semibold uppercase">
                                                        {{ $perm }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-xs text-gray-400 italic">Nenhuma</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Ações --}}
                                    <td
                                        class="px-2 md:px-6 py-3 md:py-4 md:text-center mt-2 md:mt-0 border-t border-gray-100 md:border-none flex items-center justify-end gap-2">
                                        <a href="{{ route('usuarios.edit', $user->id) }}"
                                            class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 rounded-lg transition-colors"
                                            title="Editar Acessos">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST"
                                                class="inline-block"
                                                onsubmit="return confirm('Tem certeza que deseja remover o acesso deste usuário?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 rounded-lg transition-colors"
                                                    title="Remover Usuário">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
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
