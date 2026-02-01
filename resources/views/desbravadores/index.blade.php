<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            {{ __('Gerenciar Desbravadores') }}
        </h2>
    </x-slot>

    <div class="py-6 space-y-6">

        <div class="md:hidden px-4">
            <a href="{{ route('desbravadores.create') }}"
                class="w-full flex items-center justify-center px-4 py-3 bg-dbv-red border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-red-700 shadow-md transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Novo Desbravador
            </a>
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-4 mx-4 md:mx-0">
            <div class="flex flex-col md:flex-row gap-3 justify-between">

                <form method="GET" action="{{ route('desbravadores.index') }}"
                    class="flex flex-col md:flex-row gap-3 flex-1">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar por nome..."
                            class="pl-10 block w-full border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg focus:ring-dbv-blue focus:border-dbv-blue sm:text-sm h-10 transition-colors">
                    </div>

                    <div class="flex gap-2">
                        <select name="unidade_id"
                            class="border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-lg focus:ring-dbv-blue focus:border-dbv-blue sm:text-sm h-10">
                            <option value="">Todas as Unidades</option>
                            @foreach (\App\Models\Unidade::all() as $unidade)
                                <option value="{{ $unidade->id }}"
                                    {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>{{ $unidade->nome }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-slate-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-slate-600 focus:bg-gray-700 active:bg-gray-900 focus:outline-none transition ease-in-out duration-150 h-10">
                            Filtrar
                        </button>
                    </div>
                </form>

                <a href="{{ route('desbravadores.create') }}"
                    class="hidden md:inline-flex items-center justify-center px-6 py-2 bg-dbv-red border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150 shadow-sm h-10 whitespace-nowrap">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Novo Desbravador
                </a>

            </div>
        </div>

        @if ($desbravadores->count() > 0)

            <div
                class="hidden md:block bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Desbravador</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Unidade</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Cargo/Função</th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Idade</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                            @foreach ($desbravadores as $dbv)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if ($dbv->foto)
                                                    <img class="h-10 w-10 rounded-full object-cover border-2 border-white dark:border-slate-600 shadow-sm"
                                                        src="{{ asset('storage/' . $dbv->foto) }}" alt="">
                                                @else
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-gray-200 dark:bg-slate-600 flex items-center justify-center text-gray-500 dark:text-gray-300 font-bold">
                                                        {{ substr($dbv->nome, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                    {{ $dbv->nome }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $dbv->email ?? 'Sem email' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($dbv->unidade)
                                            <span
                                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                                                {{ $dbv->unidade->nome }}
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-gray-300">
                                                Sem Unidade
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $dbv->cargo ?? 'Desbravador' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div
                                            class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('desbravadores.show', $dbv) }}"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900"
                                                title="Ver Perfil">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('desbravadores.edit', $dbv) }}"
                                                class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900"
                                                title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                    {{ $desbravadores->appends(request()->query())->links() }}
                </div>
            </div>

            <div class="md:hidden space-y-4 px-4">
                @foreach ($desbravadores as $dbv)
                    <div
                        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden relative">
                        <div
                            class="absolute left-0 top-0 bottom-0 w-1.5 {{ $dbv->unidade ? 'bg-blue-500' : 'bg-gray-300' }}">
                        </div>

                        <div class="p-4 pl-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    @if ($dbv->foto)
                                        <img class="h-12 w-12 rounded-full object-cover border border-gray-200 dark:border-slate-600"
                                            src="{{ asset('storage/' . $dbv->foto) }}" alt="">
                                    @else
                                        <div
                                            class="h-12 w-12 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center text-gray-500 dark:text-gray-300 font-bold text-lg">
                                            {{ substr($dbv->nome, 0, 1) }}
                                        </div>
                                    @endif

                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white leading-tight">
                                            {{ $dbv->nome }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $dbv->cargo ?? 'Desbravador' }} &bull;
                                            {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos</p>
                                    </div>
                                </div>

                                @if ($dbv->unidade)
                                    <span
                                        class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wide bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-md border border-blue-100 dark:border-blue-800">
                                        {{ $dbv->unidade->nome }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-2 border-t border-gray-100 dark:border-slate-700 divide-x divide-gray-100 dark:divide-slate-700">
                            <a href="{{ route('desbravadores.show', $dbv) }}"
                                class="flex items-center justify-center py-3 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                                <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver Perfil
                            </a>
                            <a href="{{ route('desbravadores.edit', $dbv) }}"
                                class="flex items-center justify-center py-3 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                                <svg class="w-4 h-4 mr-1.5 text-yellow-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Editar
                            </a>
                        </div>
                    </div>
                @endforeach

                <div class="mt-4">
                    {{ $desbravadores->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div
                class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700 mx-4 md:mx-0">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-slate-700 mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Nenhum desbravador encontrado</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tente ajustar sua busca ou cadastre um novo
                    membro.</p>
                <div class="mt-6">
                    <a href="{{ route('desbravadores.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-dbv-blue border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:outline-none transition">
                        Cadastrar Novo
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
