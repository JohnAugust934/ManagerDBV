<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Gestao Master - Convites') }}
        </h2>
    </x-slot>

    <div class="ui-page space-y-6">
        <div class="ui-card p-6">
            <h3 class="ui-title text-lg mb-4">Gerar Novo Convite (Diretor)</h3>

            @if(session('success'))
                <div class="mb-4 p-4 rounded-xl bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('master.invites.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4 items-end">
                @csrf
                <div>
                    <x-input-label for="email" value="E-mail do Diretor" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" required />
                </div>
                <button type="submit" class="ui-btn-primary w-full sm:w-auto">Gerar Link</button>
            </form>
        </div>

        <div class="ui-card overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="ui-title text-lg">Historico de Convites</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Link de Cadastro</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Criado em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($invites as $invite)
                            <tr>
                                <td class="px-4 py-3">{{ $invite->email }}</td>
                                <td class="px-4 py-3">
                                    @if(!$invite->used_at)
                                        <input type="text" readonly value="{{ route('register', ['token' => $invite->token]) }}" class="ui-input text-xs" />
                                    @else
                                        <span class="text-gray-400 italic">Cadastrado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($invite->used_at)
                                        <span class="font-bold text-green-600 dark:text-green-400">Usado</span>
                                    @else
                                        <span class="font-bold text-amber-600 dark:text-amber-400">Pendente</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $invite->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
