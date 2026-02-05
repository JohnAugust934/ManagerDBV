<x-app-layout>
    {{-- CABEÇALHO (Desktop: Completo | Mobile: Apenas Título) --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
                {{ __('Eventos do Clube') }}
            </h2>

            {{-- AÇÕES DESKTOP (Oculto no Mobile) --}}
            <div class="hidden md:flex items-center gap-2">
                <a href="{{ route('eventos.index') }}"
                    class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition text-sm font-bold shadow-sm">
                    Voltar
                </a>

                @can('secretaria')
                    <a href="{{ route('eventos.edit', $evento->id) }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-bold shadow-md shadow-blue-500/20">
                        Editar
                    </a>

                    @if ($evento->desbravadores->count() == 0)
                        <form action="{{ route('eventos.destroy', $evento->id) }}" method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir este evento?');">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2 bg-red-100 text-red-600 border border-red-200 rounded-lg hover:bg-red-200 transition text-sm font-bold shadow-sm">
                                Excluir
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="py-6 bg-gray-50 dark:bg-dbv-dark-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- DADOS OCULTOS PARA JS --}}
            <input type="hidden" id="evento-valor" value="{{ $evento->valor }}">
            <input type="hidden" id="count-pagos"
                value="{{ $evento->desbravadores->where('pivot.pago', true)->count() }}">
            <input type="hidden" id="count-total" value="{{ $evento->desbravadores->count() }}">

            {{-- AÇÕES MOBILE (Visível apenas no celular) --}}
            <div class="md:hidden grid grid-cols-2 gap-2">
                <a href="{{ route('eventos.index') }}"
                    class="flex justify-center items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-bold text-gray-700 dark:text-gray-200 shadow-sm">
                    ← Voltar
                </a>
                @can('secretaria')
                    <a href="{{ route('eventos.edit', $evento->id) }}"
                        class="flex justify-center items-center px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-sm">
                        Editar
                    </a>
                    @if ($evento->desbravadores->count() == 0)
                        <form action="{{ route('eventos.destroy', $evento->id) }}" method="POST"
                            onsubmit="return confirm('Excluir evento?');" class="col-span-2">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-full flex justify-center items-center px-4 py-2.5 bg-red-100 text-red-600 border border-red-200 rounded-lg text-sm font-bold shadow-sm">
                                Excluir Evento
                            </button>
                        </form>
                    @endif
                @endcan
            </div>

            {{-- SEÇÃO 1: DETALHES DO EVENTO (TÍTULO + INFO) --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <svg class="w-32 h-32 text-dbv-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>

                <div class="relative z-10">
                    {{-- Badges de Status --}}
                    <div class="flex items-center gap-2 mb-3">
                        @if (\Carbon\Carbon::parse($evento->data_fim)->isPast())
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 uppercase tracking-wide border border-gray-200">
                                Encerrado
                            </span>
                        @else
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 uppercase tracking-wide border border-green-200 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Ativo
                            </span>
                        @endif
                    </div>

                    {{-- Nome do Evento --}}
                    <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white leading-tight mb-6">
                        {{ $evento->nome }}
                    </h1>

                    {{-- Grid de Informações --}}
                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-gray-100 dark:border-gray-700">

                        {{-- Data Completa --}}
                        <div class="flex items-start gap-3">
                            <div
                                class="p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-blue-600 border border-blue-100 dark:border-blue-800 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">
                                    Cronograma</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    Início: <span
                                        class="font-bold">{{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y H:i') }}</span>
                                </p>
                                @if ($evento->data_fim)
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        Fim: <span
                                            class="font-bold">{{ \Carbon\Carbon::parse($evento->data_fim)->format('d/m/Y H:i') }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Local Completo --}}
                        <div class="flex items-start gap-3">
                            <div
                                class="p-2.5 bg-red-50 dark:bg-red-900/20 rounded-xl text-red-600 border border-red-100 dark:border-red-800 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">
                                    Localização</p>
                                <p class="font-bold text-gray-800 dark:text-gray-100 text-sm">{{ $evento->local }}</p>
                            </div>
                        </div>

                        {{-- Status Financeiro Rápido --}}
                        <div class="flex items-start gap-3">
                            <div
                                class="p-2.5 bg-green-50 dark:bg-green-900/20 rounded-xl text-green-600 border border-green-100 dark:border-green-800 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Inscrição
                                </p>
                                <p class="font-bold text-gray-800 dark:text-gray-100 text-sm">
                                    {{ $evento->valor == 0 ? 'Gratuito' : 'R$ ' . number_format($evento->valor, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- SEÇÃO 2: CARDS DE ESTATÍSTICAS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">

                {{-- Card Inscritos --}}
                <div
                    class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Inscritos
                        </p>
                        <p class="text-3xl font-extrabold text-gray-800 dark:text-white" id="display-inscritos">
                            {{ $evento->desbravadores->count() }}</p>
                    </div>
                    <div class="absolute right-4 top-4 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                    </div>
                </div>

                {{-- Card Financeiro --}}
                <div
                    class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <div class="relative z-10 w-full">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">
                                    Arrecadado / Meta</p>
                                <div class="flex items-baseline gap-1">
                                    <p class="text-2xl font-extrabold text-green-600 dark:text-green-400"
                                        id="display-arrecadado">
                                        R$
                                        {{ number_format($evento->desbravadores->where('pivot.pago', true)->count() * $evento->valor, 2, ',', '.') }}
                                    </p>
                                    <span class="text-[10px] text-gray-400 font-medium hidden sm:inline">
                                        / <span id="display-meta">R$
                                            {{ number_format($evento->desbravadores->count() * $evento->valor, 2, ',', '.') }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg text-green-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        @php
                            $total = $evento->desbravadores->count();
                            $pagos = $evento->desbravadores->where('pivot.pago', true)->count();
                            $porcentagem = $total > 0 ? ($pagos / $total) * 100 : 0;
                        @endphp
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                            <div id="progress-bar" class="bg-green-500 h-1.5 rounded-full transition-all duration-500"
                                style="width: {{ $porcentagem }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Card Custo --}}
                <div
                    class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Custo Unitário
                        </p>
                        <p class="text-3xl font-extrabold text-gray-800 dark:text-white">R$
                            {{ number_format($evento->valor, 2, ',', '.') }}</p>
                    </div>
                    <div
                        class="absolute right-4 top-4 p-2 bg-orange-50 dark:bg-orange-900/20 rounded-lg text-orange-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- SEÇÃO 3: ÁREA PRINCIPAL (LISTA E INSCRIÇÃO) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ tab: 'lista' }">

                {{-- COLUNA DA ESQUERDA: LISTA --}}
                <div
                    class="lg:col-span-2 flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden min-h-[500px]">

                    {{-- Tabs Navigation --}}
                    <div class="flex border-b border-gray-200 dark:border-gray-700">
                        <button @click="tab = 'lista'"
                            :class="tab === 'lista' ? 'border-dbv-blue text-dbv-blue bg-blue-50/50 dark:bg-blue-900/10' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="flex-1 py-4 px-4 text-sm font-bold uppercase tracking-wider border-b-2 transition-all">
                            Lista de Inscritos
                        </button>
                        @if ($naoInscritos->isNotEmpty())
                            <button @click="tab = 'novo'"
                                :class="tab === 'novo' ?
                                    'border-dbv-blue text-dbv-blue bg-blue-50/50 dark:bg-blue-900/10' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                class="flex-1 py-4 px-4 text-sm font-bold uppercase tracking-wider border-b-2 transition-all lg:hidden">
                                Nova Inscrição
                            </button>
                        @endif
                    </div>

                    {{-- Conteúdo da Lista --}}
                    <div x-show="tab === 'lista'" class="flex-1 flex flex-col">
                        @if ($evento->desbravadores->isEmpty())
                            <div
                                class="flex-1 flex flex-col items-center justify-center p-10 text-center text-gray-400">
                                <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">Ninguém inscrito ainda.</p>
                                <p class="text-sm">Use o painel ao lado para adicionar membros.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead
                                        class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                                        <tr>
                                            <th class="px-6 py-4">Desbravador</th>
                                            <th class="px-6 py-4 text-center">Pagamento</th>
                                            <th class="px-6 py-4 text-center">Docs</th>
                                            <th class="px-6 py-4 text-right">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach ($evento->desbravadores as $dbv)
                                            <tr
                                                class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-400 to-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                                                            {{ substr($dbv->nome, 0, 1) }}
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p
                                                                class="font-bold text-gray-900 dark:text-white text-sm truncate">
                                                                {{ $dbv->nome }}</p>
                                                            <span
                                                                class="text-[10px] uppercase font-bold text-gray-400">{{ $dbv->unidade->nome ?? 'Sem Unidade' }}</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="px-6 py-4 text-center">
                                                    <button
                                                        onclick="toggleStatus(this, '{{ route('eventos.status', [$evento->id, $dbv->id]) }}', 'pago')"
                                                        data-active="{{ $dbv->pivot->pago ? 'true' : 'false' }}"
                                                        class="px-3 py-1 rounded-full text-[10px] font-extrabold border transition-all shadow-sm w-24 hover:scale-105 active:scale-95
                                                    {{ $dbv->pivot->pago ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-50 text-red-600 border-red-100' }}">
                                                        {{ $dbv->pivot->pago ? 'PAGO' : 'PENDENTE' }}
                                                    </button>
                                                </td>

                                                <td class="px-6 py-4 text-center">
                                                    <a href="{{ route('eventos.autorizacao', [$evento->id, $dbv->id]) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition"
                                                        title="Gerar Autorização">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                            </path>
                                                        </svg>
                                                    </a>
                                                </td>

                                                <td class="px-6 py-4 text-right">
                                                    <form
                                                        action="{{ route('eventos.remover-inscricao', [$evento->id, $dbv->id]) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Remover {{ $dbv->nome }} do evento?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="text-gray-400 hover:text-red-500 transition p-2 hover:bg-red-50 rounded-lg group-hover:opacity-100 opacity-50">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- COLUNA DA DIREITA: NOVA INSCRIÇÃO --}}
                <div class="lg:block" :class="tab === 'novo' ? 'block' : 'hidden lg:block'">
                    <div
                        class="bg-white dark:bg-gray-800 shadow-xl border border-gray-100 dark:border-gray-700 rounded-2xl p-6 sticky top-24">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="p-2 bg-dbv-blue/10 rounded-lg text-dbv-blue">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900 dark:text-white">Adicionar Inscritos</h3>
                                <p class="text-xs text-gray-500">Membros ativos não inscritos</p>
                            </div>
                        </div>

                        @if ($naoInscritos->isEmpty())
                            <div
                                class="p-4 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm text-center border border-green-100 dark:border-green-800">
                                <p class="font-bold mb-1">Tudo pronto! 🎉</p>
                                Todos os desbravadores ativos já estão inscritos.
                            </div>
                        @else
                            <div x-data="{ mode: 'single' }" class="space-y-5">
                                {{-- Seletor de Modo --}}
                                <div class="flex bg-gray-100 dark:bg-gray-700 p-1.5 rounded-xl">
                                    <button type="button" @click="mode = 'single'"
                                        :class="mode === 'single' ?
                                            'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white' :
                                            'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                        class="flex-1 py-2 text-xs font-bold rounded-lg transition-all">Individual</button>
                                    <button type="button" @click="mode = 'multiple'"
                                        :class="mode === 'multiple' ?
                                            'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white' :
                                            'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                        class="flex-1 py-2 text-xs font-bold rounded-lg transition-all">Em
                                        Lote</button>
                                </div>

                                {{-- MODO INDIVIDUAL --}}
                                <div x-show="mode === 'single'" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0">
                                    <form action="{{ route('eventos.inscrever', $evento->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label
                                                class="block text-xs font-bold text-gray-500 uppercase mb-2">Selecione
                                                o membro</label>
                                            <select name="desbravador_id"
                                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl shadow-sm focus:ring-dbv-blue focus:border-dbv-blue"
                                                required>
                                                <option value="">Clique para selecionar...</option>
                                                @foreach ($naoInscritos as $dbv)
                                                    <option value="{{ $dbv->id }}">{{ $dbv->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit"
                                            class="w-full bg-dbv-blue hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-500/20 transition transform active:scale-95 flex justify-center items-center gap-2">
                                            <span>Inscrever Agora</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                {{-- MODO EM LOTE --}}
                                <div x-show="mode === 'multiple'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0">
                                    <form action="{{ route('eventos.inscrever-lote', $evento->id) }}" method="POST">
                                        @csrf
                                        <div
                                            class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-xl p-1 space-y-0.5 mb-4 bg-gray-50 dark:bg-gray-900/50 custom-scrollbar">

                                            {{-- "Selecionar Todos" (Script Simples inline) --}}
                                            <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 mb-1">
                                                <label class="flex items-center space-x-3 cursor-pointer">
                                                    <input type="checkbox"
                                                        onclick="document.querySelectorAll('.check-item').forEach(el => el.checked = this.checked)"
                                                        class="rounded text-dbv-blue focus:ring-dbv-blue border-gray-300 w-4 h-4">
                                                    <span class="text-xs font-bold text-gray-500 uppercase">Selecionar
                                                        Todos</span>
                                                </label>
                                            </div>

                                            @foreach ($naoInscritos as $dbv)
                                                <label
                                                    class="flex items-center space-x-3 p-2.5 hover:bg-white dark:hover:bg-gray-700 rounded-lg cursor-pointer transition-colors group">
                                                    <input type="checkbox" name="desbravadores[]"
                                                        value="{{ $dbv->id }}"
                                                        class="check-item rounded text-dbv-blue focus:ring-dbv-blue border-gray-300 w-4 h-4">
                                                    <span
                                                        class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-dbv-blue transition-colors">{{ $dbv->nome }}</span>
                                                </label>
                                            @endforeach
                                        </div>

                                        <button type="submit"
                                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-green-500/20 transition transform active:scale-95 flex justify-center items-center gap-2">
                                            <span>Inscrever Selecionados</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT AJAX PARA PAGAMENTO E ATUALIZAÇÃO DINÂMICA --}}
    <script>
        async function toggleStatus(button, url, campo) {
            const originalText = button.innerText;
            const originalClass = button.className;

            // Estado visual de carregamento
            button.innerText = '...';
            button.classList.add('opacity-50', 'cursor-not-allowed');
            button.disabled = true;

            const wasActive = button.getAttribute('data-active') === 'true';
            const newState = !wasActive;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        _method: 'PATCH',
                        campo: campo,
                        valor: newState ? '1' : '0'
                    })
                });

                if (!response.ok) throw new Error('Erro na requisição');

                const data = await response.json();

                if (data.success) {
                    // 1. Atualizar botão
                    button.setAttribute('data-active', newState);
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                    button.disabled = false;

                    if (campo === 'pago') {
                        button.innerText = newState ? 'PAGO' : 'PENDENTE';
                        button.className = newState ?
                            'px-3 py-1 rounded-full text-[10px] font-extrabold border transition-all shadow-sm w-24 hover:scale-105 active:scale-95 bg-green-100 text-green-700 border-green-200' :
                            'px-3 py-1 rounded-full text-[10px] font-extrabold border transition-all shadow-sm w-24 hover:scale-105 active:scale-95 bg-red-50 text-red-600 border-red-100';

                        // 2. Atualizar Card Financeiro (Dinamicamente)
                        updateFinancialCards(newState);
                    }
                } else {
                    throw new Error('Falha ao atualizar');
                }

            } catch (error) {
                console.error(error);
                alert('Erro ao atualizar status. Verifique sua conexão.');
                button.innerText = originalText;
                button.className = originalClass;
                button.disabled = false;
            }
        }

        function updateFinancialCards(isPaying) {
            // Pegar valores atuais do DOM ou Inputs Hidden
            const eventValue = parseFloat(document.getElementById('evento-valor').value);
            let countPagos = parseInt(document.getElementById('count-pagos').value);
            const countTotal = parseInt(document.getElementById('count-total').value);

            // Atualizar contagem lógica
            if (isPaying) {
                countPagos++;
            } else {
                countPagos--;
            }

            // Atualizar Input Hidden para próxima operação
            document.getElementById('count-pagos').value = countPagos;

            // Calcular novos valores
            const totalArrecadado = countPagos * eventValue;
            const porcentagem = countTotal > 0 ? (countPagos / countTotal) * 100 : 0;

            // Formatar Moeda (BRL)
            const formatter = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });

            // Atualizar textos na tela
            document.getElementById('display-arrecadado').innerText = formatter.format(totalArrecadado);

            // Atualizar Barra de Progresso
            document.getElementById('progress-bar').style.width = porcentagem + '%';
        }
    </script>
</x-app-layout>
