<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestão Master - Convites') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Gerar Novo Convite (Diretor)</h3>

                @if(session('success'))
                <div class="mb-4 p-4 text-green-700 bg-green-100 rounded-lg">
                    {{ session('success') }}
                </div>
                @endif

                <form action="{{ route('master.invites.store') }}" method="POST" class="flex gap-4 items-end">
                    @csrf
                    <div class="flex-grow">
                        <x-input-label for="email" value="E-mail do Diretor" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" required />
                    </div>
                    <x-primary-button class="mb-1">
                        Gerar Link
                    </x-primary-button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Histórico de Convites</h3>
                <table class="min-w-full text-left text-sm font-light text-gray-900 dark:text-gray-100">
                    <thead class="border-b font-medium dark:border-neutral-500">
                        <tr>
                            <th scope="col" class="px-6 py-4">Email</th>
                            <th scope="col" class="px-6 py-4">Link de Cadastro</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invites as $invite)
                        <tr class="border-b dark:border-neutral-500">
                            <td class="whitespace-nowrap px-6 py-4">{{ $invite->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if(!$invite->used_at)
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly value="{{ route('register', ['token' => $invite->token]) }}" class="text-xs bg-gray-100 border-0 rounded p-1 w-64 text-gray-600">
                                </div>
                                @else
                                <span class="text-gray-400 italic">Cadastrado</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($invite->used_at)
                                <span class="text-green-600 font-bold">Usado</span>
                                @else
                                <span class="text-yellow-600 font-bold">Pendente</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $invite->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>