<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                Unidade {{ $unidade->nome }}
            </h2>

            <div class="hidden md:flex items-center gap-2 shrink-0">
                <a href="{{ route('unidades.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none transition shadow-sm">
                    Voltar
                </a>
                <a href="{{ route('unidades.edit', $unidade) }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-dbv-yellow border border-transparent rounded-lg font-bold text-xs text-yellow-900 uppercase tracking-widest hover:bg-yellow-500 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        <div class="grid grid-cols-2 gap-3 md:hidden px-4">
            <a href="{{ route('unidades.index') }}"
                class="flex items-center justify-center py-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl font-bold text-sm text-gray-700 dark:text-gray-300 shadow-sm">
                Voltar
            </a>
            <a href="{{ route('unidades.edit', $unidade) }}"
                class="flex items-center justify-center py-3 bg-dbv-yellow border border-transparent rounded-xl font-bold text-sm text-yellow-900 shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden mx-4 md:mx-0">
            <div class="h-3 w-full bg-gradient-to-r from-dbv-blue via-blue-600 to-blue-400"></div>

            <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-center md:items-start text-center md:text-left gap-6">

                    <div class="shrink-0">
                        <div
                            class="w-24 h-24 rounded-full bg-blue-50 dark:bg-slate-700 flex items-center justify-center border-4 border-blue-100 dark:border-slate-600 text-dbv-blue dark:text-blue-400 text-4xl font-black shadow-inner">
                            {{ substr($unidade->nome, 0, 1) }}
                        </div>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">{{ $unidade->nome }}</h3>

                        <div
                            class="flex flex-col md:flex-row items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-4">
                            <span class="flex items-center gap-1 bg-gray-100 dark:bg-slate-700 px-3 py-1 rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Conselheiro: <strong>{{ $unidade->conselheiro }}</strong>
                            </span>
                            <span
                                class="flex items-center gap-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                                    </path>
                                </svg>
                                {{ $unidade->desbravadores->count() }} Membros
                            </span>
                        </div>

                        @if ($unidade->grito_guerra)
                            <div
                                class="relative bg-yellow-50 dark:bg-yellow-900/10 p-4 rounded-xl border-l-4 border-dbv-yellow italic text-gray-700 dark:text-gray-300">
                                <svg class="absolute top-2 left-2 w-4 h-4 text-yellow-500 opacity-50"
                                    fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M14.017 21L14.017 18C14.017 16.8954 13.1216 16 12.017 16H9.01705C7.91248 16 7.01705 16.8954 7.01705 18L7.01705 21H14.017ZM21.017 6.00005C21.017 11.4395 16.666 16.0354 11.3912 16.148L11.4589 16.148C10.3543 16.148 9.45889 17.0435 9.45889 18.148V21.0001H4.45889C3.90661 21.0001 3.45889 20.5523 3.45889 20.0001V4.00005C3.45889 3.44776 3.90661 3.00005 4.45889 3.00005H21.017V6.00005Z">
                                    </path>
                                </svg>
                                <p class="pl-4">"{{ $unidade->grito_guerra }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden mx-4 md:mx-0">
            <div
                class="p-6 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center bg-gray-50 dark:bg-slate-700/30">
                <h3 class="font-bold text-lg text-gray-800 dark:text-gray-100">Membros da Unidade</h3>
            </div>

            @if ($unidade->desbravadores->count() > 0)

                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Nome</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Cargo</th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Idade</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                            @foreach ($unidade->desbravadores as $dbv)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
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
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $dbv->cargo ?? 'Desbravador' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('desbravadores.show', $dbv) }}"
                                            class="text-dbv-blue dark:text-blue-400 hover:underline font-bold">Ver
                                            Perfil</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($unidade->desbravadores as $dbv)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if ($dbv->foto)
                                    <img class="h-12 w-12 rounded-full object-cover border border-gray-200 dark:border-slate-600"
                                        src="{{ asset('storage/' . $dbv->foto) }}" alt="">
                                @else
                                    <div
                                        class="h-12 w-12 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center text-gray-500 dark:text-gray-300 font-bold">
                                        {{ substr($dbv->nome, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">{{ $dbv->nome }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $dbv->cargo ?? 'Desbravador' }} &bull;
                                        {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos</p>
                                </div>
                            </div>
                            <a href="{{ route('desbravadores.show', $dbv) }}"
                                class="p-2 text-gray-400 hover:text-dbv-blue dark:hover:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">Esta unidade ainda não possui membros.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
