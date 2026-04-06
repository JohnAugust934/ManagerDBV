<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-dbv-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            {{ __('Desbravadores') }}
        </h2>
    </x-slot>

    <div class="ui-page space-y-6">

        <div class="px-4 sm:px-0 flex justify-end">
            <a href="{{ route('desbravadores.create') }}"
                class="ui-btn-primary w-full sm:w-auto">
                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2 sm:mr-1.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Novo Desbravador
            </a>
        </div>

        <div
            class="ui-card overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-100 dark:border-slate-700">
                <form method="GET" action="{{ route('desbravadores.index') }}" class="flex flex-col md:flex-row gap-4"
                    id="filter-form">
                    <input type="hidden" name="status" value="{{ $status }}">

                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar por nome, email ou CPF..."
                            class="ui-input pl-10">
                    </div>
                    <div class="w-full md:w-48">
                        <select name="unidade_id"
                            class="ui-input"
                            onchange="this.form.submit()">
                            <option value="">Todas as Unidades</option>
                            @foreach (\App\Models\Unidade::orderBy('nome')->get() as $unidade)
                                <option value="{{ $unidade->id }}"
                                    {{ request('unidade_id') == $unidade->id? 'selected' : '' }}>
                                    {{ $unidade->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="ui-btn-primary w-full sm:w-auto">
                            Filtrar
                        </button>
                        @if (request()->hasAny(['search', 'unidade_id', 'status']) &&
                                (request('search') != '' || request('unidade_id') != '' || request('status') != 'ativos'))
                            <a href="{{ route('desbravadores.index') }}"
                                class="ui-btn-secondary w-full sm:w-auto">
                                Limpar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div
                class="bg-gray-50 dark:bg-slate-800/50 px-4 md:px-6 py-3 border-b border-gray-100 dark:border-slate-700 flex flex-wrap gap-2">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'ativos']) }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ $status === 'ativos'? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 border border-green-200 dark:border-green-800' : 'bg-white dark:bg-slate-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-slate-600 hover:bg-gray-100' }}">
                    Ativos
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'inativos']) }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ $status === 'inativos'? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 border border-red-200 dark:border-red-800' : 'bg-white dark:bg-slate-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-slate-600 hover:bg-gray-100' }}">
                    Inativos
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'todos']) }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ $status === 'todos'? 'bg-dbv-blue text-white dark:bg-blue-600 border border-blue-700' : 'bg-white dark:bg-slate-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-slate-600 hover:bg-gray-100' }}">
                    Todos
                </a>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead
                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-900 dark:text-gray-300 border-b border-gray-100 dark:border-slate-700">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-bold">Desbravador</th>
                            <th scope="col" class="px-6 py-4 font-bold">Unidade/Classe</th>
                            <th scope="col" class="px-6 py-4 font-bold">Contato</th>
                            <th scope="col" class="px-6 py-4 font-bold text-center">Status</th>
                            <th scope="col" class="px-6 py-4 font-bold text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($desbravadores as $dbv)
                            <tr
                                class="bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700/50 border-b border-gray-50 dark:border-slate-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center border-2 border-white dark:border-slate-600 shadow-sm overflow-hidden">
                                            @if ($dbv->foto)
                                                <img class="h-10 w-10 object-cover"
                                                    src="{{ asset('storage/' . $dbv->foto) }}" alt="">
                                            @else
                                                <span
                                                    class="text-gray-500 dark:text-gray-300 font-bold text-sm">{{ substr($dbv->nome, 0, 2) }}</span>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $dbv->nome }}</div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        @if ($dbv->unidade)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800 w-max">
                                                {{ $dbv->unidade->nome }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                            {{ $dbv->classe->nome?? 'Sem Classe' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-300">
                                        {{ $dbv->telefone?? 'Não informado' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $dbv->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($dbv->ativo)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            Ativo
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            Inativo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('desbravadores.show', $dbv) }}"
                                        class="inline-flex items-center p-2 text-dbv-blue bg-blue-50 dark:text-blue-400 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg transition-colors"
                                        title="Ver Detalhes">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('desbravadores.edit', $dbv) }}"
                                        class="inline-flex items-center p-2 text-amber-600 bg-amber-50 dark:text-amber-400 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 rounded-lg ml-1 transition-colors"
                                        title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                    <div class="px-4 py-6">
                        <x-empty-state
                            title="Nenhum desbravador encontrado"
                            description="Ajuste os filtros ou cadastre novos desbravadores." />
                    </div>
                @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden flex flex-col divide-y divide-gray-100 dark:divide-slate-700">
                @forelse($desbravadores as $dbv)
                    <div class="p-4 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center border-2 border-white dark:border-slate-600 shadow-sm overflow-hidden">
                                    @if ($dbv->foto)
                                        <img class="h-12 w-12 object-cover"
                                            src="{{ asset('storage/' . $dbv->foto) }}" alt="">
                                    @else
                                        <span
                                            class="text-gray-500 dark:text-gray-300 font-bold text-sm">{{ substr($dbv->nome, 0, 2) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                        {{ $dbv->nome }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-1">
                                        {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos -
                                        {{ $dbv->unidade->nome?? 'Sem Unidade' }}
                                    </div>
                                    <div class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 mt-0.5">
                                        {{ $dbv->classe->nome?? 'Sem Classe' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if ($dbv->ativo)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Ativo
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                        Inativo
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <a href="{{ route('desbravadores.show', $dbv) }}"
                                class="inline-flex items-center justify-center py-2 px-4 text-xs font-bold text-dbv-blue bg-blue-50 dark:text-blue-400 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg transition-colors border border-blue-100 dark:border-blue-900/30">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Detalhes
                            </a>
                            <a href="{{ route('desbravadores.edit', $dbv) }}"
                                class="inline-flex items-center justify-center py-2 px-4 text-xs font-bold text-amber-600 bg-amber-50 dark:text-amber-400 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 rounded-lg transition-colors border border-amber-100 dark:border-amber-900/30">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Editar
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6">
                        <x-empty-state
                            title="Nenhum desbravador encontrado"
                            description="Ajuste os filtros ou cadastre novos desbravadores." />
                    </div>
                @endforelse
            </div>

            @if ($desbravadores->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50">
                    {{ $desbravadores->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>



