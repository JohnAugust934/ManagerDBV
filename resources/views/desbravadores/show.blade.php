<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full h-full gap-4">
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight truncate">
                {{ __('Perfil do Desbravador') }}
            </h2>

            <div class="hidden md:flex items-center gap-2 shrink-0">
                <a href="{{ route('desbravadores.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none transition shadow-sm">
                    Voltar
                </a>
                <a href="{{ route('desbravadores.edit', $desbravador) }}"
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
            <a href="{{ route('desbravadores.index') }}"
                class="flex items-center justify-center py-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl font-bold text-sm text-gray-700 dark:text-gray-300 shadow-sm">
                Voltar
            </a>
            <a href="{{ route('desbravadores.edit', $desbravador) }}"
                class="flex items-center justify-center py-3 bg-dbv-yellow border border-transparent rounded-xl font-bold text-sm text-yellow-900 shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden relative mx-4 md:mx-0">
            <div class="h-32 bg-gradient-to-r from-dbv-blue to-blue-800 dark:from-slate-900 dark:to-slate-800"></div>

            <div class="px-6 pb-6">
                <div class="flex flex-col md:flex-row items-start md:items-end -mt-12 mb-4 gap-6">

                    <div class="relative shrink-0">
                        @if ($desbravador->foto)
                            <img class="h-32 w-32 rounded-full object-cover border-4 border-white dark:border-slate-800 shadow-md bg-white"
                                src="{{ asset('storage/' . $desbravador->foto) }}" alt="">
                        @else
                            <div
                                class="h-32 w-32 rounded-full border-4 border-white dark:border-slate-800 shadow-md bg-gray-200 dark:bg-slate-700 flex items-center justify-center text-gray-400 dark:text-gray-300">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif

                        <div class="absolute bottom-2 right-2 w-5 h-5 rounded-full border-2 border-white dark:border-slate-800 {{ $desbravador->ativo ? 'bg-green-500' : 'bg-red-500' }}"
                            title="{{ $desbravador->ativo ? 'Ativo' : 'Inativo' }}"></div>
                    </div>

                    <div class="flex-1 min-w-0 pb-2">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">
                                    {{ $desbravador->nome }}</h1>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2 mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                                        {{ $desbravador->cargo ?? 'Desbravador' }}
                                    </span>
                                    <span>&bull;</span>
                                    <span>{{ \Carbon\Carbon::parse($desbravador->data_nascimento)->age }} anos</span>
                                </p>
                            </div>

                            @if ($desbravador->unidade)
                                <div
                                    class="flex items-center gap-3 bg-gray-50 dark:bg-slate-700/50 px-4 py-2 rounded-xl border border-gray-100 dark:border-slate-600 mt-4 md:mt-0">
                                    <div class="text-right">
                                        <p
                                            class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-bold">
                                            Unidade</p>
                                        <p class="font-bold text-dbv-blue dark:text-blue-300">
                                            {{ $desbravador->unidade->nome }}</p>
                                    </div>
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-lg">
                                        {{ substr($desbravador->unidade->nome, 0, 1) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6 border-t border-gray-100 dark:border-slate-700 pt-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 dark:bg-slate-700 rounded-lg text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nascimento</p>
                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($desbravador->data_nascimento)->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-50 dark:bg-slate-700 rounded-lg text-purple-600 dark:text-purple-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Classe Atual</p>
                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $desbravador->classe_atual ?? 'Não informada' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-50 dark:bg-slate-700 rounded-lg text-green-600 dark:text-green-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Presença (Últimas 5)</p>
                            <div class="flex gap-1 mt-1">
                                @forelse($desbravador->frequencias as $freq)
                                    <div class="w-2.5 h-2.5 rounded-full {{ $freq->presente ? 'bg-green-500' : 'bg-red-400' }}"
                                        title="{{ \Carbon\Carbon::parse($freq->data)->format('d/m') }}: {{ $freq->presente ? 'Presente' : 'Falta' }}">
                                    </div>
                                @empty
                                    <span class="text-xs text-gray-400">-</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-50 dark:bg-slate-700 rounded-lg text-red-600 dark:text-red-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tipo Sanguíneo</p>
                            <p class="font-bold text-gray-800 dark:text-gray-200">
                                {{ $desbravador->tipo_sanguineo ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($desbravador->alergias || $desbravador->medicamentos_continuos)
            <div
                class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm mx-4 md:mx-0">
                <h3 class="font-bold text-red-800 dark:text-red-300 flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Atenção: Informações de Saúde
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if ($desbravador->alergias)
                        <div>
                            <span class="text-xs font-bold text-red-600 dark:text-red-400 uppercase">Alergias:</span>
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $desbravador->alergias }}</p>
                        </div>
                    @endif
                    @if ($desbravador->medicamentos_continuos)
                        <div>
                            <span
                                class="text-xs font-bold text-red-600 dark:text-red-400 uppercase">Medicamentos:</span>
                            <p class="text-sm text-gray-800 dark:text-gray-200">
                                {{ $desbravador->medicamentos_continuos }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mx-4 md:mx-0">

            <div class="lg:col-span-1 space-y-6">
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
                    <h3
                        class="font-bold text-lg text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-100 dark:border-slate-700 pb-2">
                        Contato</h3>

                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="mt-0.5 text-gray-400"><svg class="w-5 h-5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg></div>
                            <div class="break-all">
                                <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Email</span>
                                <span
                                    class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $desbravador->email ?? 'Não informado' }}</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="mt-0.5 text-gray-400"><svg class="w-5 h-5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg></div>
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Telefone</span>
                                <span
                                    class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $desbravador->telefone ?? 'Não informado' }}</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="mt-0.5 text-gray-400"><svg class="w-5 h-5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg></div>
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Endereço</span>
                                <span
                                    class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $desbravador->endereco }}</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
                    <h3
                        class="font-bold text-lg text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-100 dark:border-slate-700 pb-2">
                        Responsável</h3>

                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="mt-0.5 text-gray-400"><svg class="w-5 h-5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg></div>
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Nome</span>
                                <span
                                    class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $desbravador->nome_responsavel }}</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="mt-0.5 text-gray-400"><svg class="w-5 h-5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg></div>
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Telefone</span>
                                <span
                                    class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $desbravador->telefone_responsavel }}</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">

                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
                    <h3
                        class="font-bold text-lg text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-100 dark:border-slate-700 pb-2">
                        Dados de Saúde</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Cartão SUS</span>
                            <span
                                class="text-sm font-mono font-medium text-gray-800 dark:text-gray-200">{{ $desbravador->numero_sus }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Plano de
                                Saúde</span>
                            <span
                                class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $desbravador->plano_saude ?? 'Particular / SUS' }}</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
                    <div
                        class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-slate-700 pb-2">
                        <h3 class="font-bold text-lg text-gray-800 dark:text-gray-100">Especialidades</h3>
                        <a href="{{ route('desbravadores.especialidades', $desbravador) }}"
                            class="text-xs font-bold text-dbv-blue dark:text-blue-400 hover:underline">
                            Gerenciar
                        </a>
                    </div>

                    @if ($desbravador->especialidades->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach ($desbravador->especialidades as $esp)
                                <div
                                    class="flex flex-col items-center text-center p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                                    <div
                                        class="w-0 h-0 border-l-[20px] border-l-transparent border-r-[20px] border-r-transparent border-t-[35px] border-t-dbv-yellow dark:border-t-yellow-600 mb-2 drop-shadow-sm">
                                    </div>
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-200 leading-tight">{{ $esp->nome }}</span>
                                    <span
                                        class="text-[10px] text-gray-400 mt-1">{{ \Carbon\Carbon::parse($esp->pivot->data_conclusao)->format('d/m/Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <p class="text-sm text-gray-400">Nenhuma especialidade registrada.</p>
                            <a href="{{ route('desbravadores.especialidades', $desbravador) }}"
                                class="mt-2 inline-block text-xs font-bold text-dbv-blue hover:underline">Adicionar
                                Especialidade</a>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
