<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestão de Convites
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('usuarios.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow text-sm">
                    Ver Usuários Ativos
                </a>
                <a href="{{ route('invites.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow text-sm">
                    + Novo Convite
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if($invites->isEmpty())
                    <p class="text-center text-gray-500">Nenhum convite gerado.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-100 dark:bg-gray-700 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Cargo</th>
                                    <th class="px-6 py-3">Link de Convite</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($invites as $invite)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4">{{ $invite->email }}</td>
                                    <td class="px-6 py-4 uppercase font-bold text-xs">{{ $invite->role }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <input type="text" readonly value="{{ route('register.invite', ['token' => $invite->token]) }}"
                                                class="text-xs w-64 bg-gray-50 border border-gray-300 rounded p-1 dark:bg-gray-900 dark:border-gray-600">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($invite->registered_at)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Aceito</span>
                                        @else
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pendente</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(!$invite->registered_at)
                                        <form action="{{ route('invites.destroy', $invite->id) }}" method="POST" onsubmit="return confirm('Revogar este convite?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-bold uppercase">Revogar</button>
                                        </form>
                                        @else
                                        <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>