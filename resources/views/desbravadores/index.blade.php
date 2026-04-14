<x-app-layout>
    <x-slot name="header">
        Secretaria | Desbravadores
    </x-slot>

    <div class="ui-page space-y-8 max-w-[1400px]">

        {{-- Cabeçalho da Página --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 ui-animate-fade-up">
            <div>
                <h2 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#002F6C] to-blue-600 text-white flex items-center justify-center shadow-inner">
                        <svg class="w-5 h-5 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    Gestão de Desbravadores
                </h2>
                <p class="text-[15px] text-slate-500 font-medium mt-1">Gerencie os membros, dados de contato e classes.</p>
            </div>
            
            <a href="{{ route('desbravadores.create') }}" class="ui-btn-primary w-full sm:w-auto shadow-xl shadow-blue-900/20 group">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Novo Cadastro</span>
            </a>
        </div>

        {{-- Filtros e Busca --}}
        <div class="ui-card p-6 border-b-4 border-b-[#002F6C] dark:border-b-blue-600 ui-animate-fade-up" style="animation-delay: 100ms;">
            <form method="GET" action="{{ route('desbravadores.index') }}" id="filter-form" class="flex flex-col md:flex-row gap-4 lg:gap-6 items-end">
                <input type="hidden" name="status" value="{{ $status }}">

                <div class="w-full md:flex-1 relative">
                    <label class="ui-input-label">Busca Rápida</label>
                    <div class="absolute bottom-0 left-0 pl-4 mb-[14px] flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" id="search-input" value="{{ request('search') }}" autocomplete="off"
                        placeholder="Nome, e-mail ou documento..." 
                        class="ui-input pl-12 h-12">
                </div>

                <div class="w-full md:w-64">
                    <label class="ui-input-label">Filtrar por Unidade</label>
                    <div class="relative">
                        <select name="unidade_id" id="unidade-filter" class="ui-input h-12 appearance-none pr-10">
                            <option value="">Todas as Unidades</option>
                            @foreach (\App\Models\Unidade::orderBy('nome')->get() as $unidade)
                                <option value="{{ $unidade->id }}" {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                    {{ $unidade->nome }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 w-full md:w-auto h-12">
                    <button type="submit" class="ui-btn-secondary flex-1 md:flex-none" title="Aplicar Filtros">
                        Filtrar
                    </button>
                    @if (request()->hasAny(['search', 'unidade_id', 'status']) && (request('search') != '' || request('unidade_id') != '' || request('status') != 'ativos'))
                        <a href="{{ route('desbravadores.index') }}" class="ui-btn-danger flex-1 md:flex-none !px-4" title="Limpar Filtros">
                            <svg class="w-5 h-5 !m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div id="desbravadores-results" class="ui-animate-fade-up" style="animation-delay: 200ms;">
            
            {{-- Tabs de Status (Ativos, Inativos, Todos) --}}
            <div class="flex gap-2 mb-6 ml-2 overflow-x-auto pb-2 scrollbar-hide">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'ativos']) }}" data-async="true"
                    class="ui-badge px-4 py-2 text-[12px] transition-all whitespace-nowrap border-2 {{ $status === 'ativos' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border-emerald-500/30' : 'bg-transparent text-slate-500 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'ativos' ? 'bg-emerald-500' : 'bg-slate-400' }} mr-2 inline-block"></span> Membros Ativos
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'inativos']) }}" data-async="true"
                    class="ui-badge px-4 py-2 text-[12px] transition-all whitespace-nowrap border-2 {{ $status === 'inativos' ? 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 border-red-500/30' : 'bg-transparent text-slate-500 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'inativos' ? 'bg-red-500' : 'bg-slate-400' }} mr-2 inline-block"></span> Inativos
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'todos']) }}" data-async="true"
                    class="ui-badge px-4 py-2 text-[12px] transition-all whitespace-nowrap border-2 {{ $status === 'todos' ? 'bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 border-[#002F6C]/30' : 'bg-transparent text-slate-500 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                    Toda a Base
                </a>
            </div>

            {{-- Tabela Premium Desktop --}}
            <div class="hidden md:block ui-table-wrapper shadow-sm">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>Desbravador</th>
                            <th>Unidade / Classe</th>
                            <th>Contato</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($desbravadores as $dbv)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden shrink-0">
                                            @if ($dbv->foto)
                                                <img class="w-full h-full object-cover" src="{{ asset('storage/' . $dbv->foto) }}" alt="Foto">
                                            @else
                                                <span class="text-slate-400 font-black text-sm">{{ mb_strtoupper(substr($dbv->nome, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-800 dark:text-white text-[15px] leading-tight mb-1">{{ $dbv->nome }}</div>
                                            <div class="text-[12px] font-semibold text-slate-500 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col gap-2">
                                        @if ($dbv->unidade)
                                            <span class="ui-badge bg-[#002F6C]/10 text-[#002F6C] dark:bg-blue-500/20 dark:text-blue-300 w-max ring-1 ring-inset ring-blue-500/20">
                                                {{ $dbv->unidade->nome }}
                                            </span>
                                        @else
                                            <span class="text-xs font-semibold text-slate-400">- Sem Unidade -</span>
                                        @endif
                                        <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                                            {{ $dbv->classe->nome ?? 'Sem Classe' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ $dbv->telefone ?? 'N/A' }}
                                    </div>
                                    <div class="text-[12px] font-medium text-slate-500 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ $dbv->email }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($dbv->ativo)
                                        <div class="ui-badge bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 gap-1.5 py-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Ativo
                                        </div>
                                    @else
                                        <div class="ui-badge bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 gap-1.5 py-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inativo
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('desbravadores.show', $dbv) }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-dbv-blue hover:text-white dark:bg-slate-800 dark:hover:bg-blue-600 text-slate-500 transition-colors shadow-sm ring-1 ring-inset ring-slate-200 dark:ring-slate-700" title="Ver Detalhes">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('desbravadores.edit', $dbv) }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-amber-500 hover:text-white dark:bg-slate-800 dark:hover:bg-amber-600 text-slate-500 transition-colors shadow-sm ring-1 ring-inset ring-slate-200 dark:ring-slate-700" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="ui-empty border-0 m-4 shadow-none">
                                        <div class="ui-empty-icon"><svg class="w-8 h-8 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                                        <h3 class="ui-empty-title text-lg">Nenhum membro listado</h3>
                                        <p class="ui-empty-description text-xs">Realize uma nova busca ou cadastre os desbravadores na base.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Lista Mobile --}}
            <div class="md:hidden flex flex-col gap-4">
                @forelse($desbravadores as $dbv)
                    <div class="ui-card p-5">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex flex-row items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                                    @if ($dbv->foto)
                                        <img class="w-full h-full object-cover rounded-xl" src="{{ asset('storage/' . $dbv->foto) }}" alt="">
                                    @else
                                        <span class="text-slate-400 font-black">{{ mb_strtoupper(substr($dbv->nome, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="font-black text-slate-800 dark:text-white leading-tight mb-1">{{ $dbv->nome }}</div>
                                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">{{ $dbv->unidade->nome ?? 'S/ Unidade' }}</div>
                                </div>
                            </div>
                            @if ($dbv->ativo)
                                <div class="w-3 h-3 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)] shrink-0 mt-1"></div>
                            @else
                                <div class="w-3 h-3 rounded-full bg-red-500 shrink-0 mt-1 opacity-50"></div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">Idade</p>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">Classe</p>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 truncate">{{ $dbv->classe->nome ?? 'Nenhuma' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('desbravadores.show', $dbv) }}" class="ui-btn-secondary py-2 border-2 text-sm justify-center">Detalhes</a>
                            <a href="{{ route('desbravadores.edit', $dbv) }}" class="ui-btn-secondary py-2 border-2 text-sm justify-center border-amber-200 dark:border-amber-900/40 text-amber-600 dark:text-amber-400 hover:bg-amber-50">Editar</a>
                        </div>
                    </div>
                @empty
                    <div class="ui-empty mt-0">
                        <div class="ui-empty-icon"><svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                        <h3 class="ui-empty-title text-base">Nenhum registro</h3>
                    </div>
                @endforelse
            </div>

            {{-- Paginação Customizada se necessário --}}
            @if ($desbravadores->hasPages())
                <div class="mt-6 p-4 ui-card bg-slate-50/50 dark:bg-slate-800/30">
                    {{ $desbravadores->withQueryString()->links() }}
                </div>
            @endif

        </div>

    </div>

    {{-- Script AJAX Responsivo Mantido Intacto --}}
    <script>
        (() => {
            const form = document.getElementById('filter-form');
            const input = document.getElementById('search-input');
            const unidadeFilter = document.getElementById('unidade-filter');
            const resultsContainer = document.getElementById('desbravadores-results');

            if (!form || !input || !resultsContainer) return;

            let debounceTimer = null;
            let lastSubmittedValue = input.value.trim();
            let activeRequest = null;

            const setLoading = (loading) => {
                resultsContainer.style.opacity = loading ? '0.5' : '1';
                resultsContainer.style.pointerEvents = loading ? 'none' : 'auto';
                resultsContainer.style.transition = 'opacity 150ms ease';
            };

            const syncFiltersFromUrl = (url) => {
                const params = new URL(url, window.location.origin).searchParams;
                input.value = params.get('search') ?? '';
                if (unidadeFilter) {
                    unidadeFilter.value = params.get('unidade_id') ?? '';
                }
            };

            const requestAndRender = async (url) => {
                if (activeRequest) {
                    activeRequest.abort();
                }

                activeRequest = new AbortController();
                setLoading(true);

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: activeRequest.signal,
                    });

                    if (!response.ok) throw new Error('Falha HTTP');

                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const nextContainer = doc.getElementById('desbravadores-results');

                    if (nextContainer) {
                        resultsContainer.innerHTML = nextContainer.innerHTML;
                        window.history.replaceState({}, '', url);
                        syncFiltersFromUrl(url);
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') window.location.href = url;
                } finally {
                    setLoading(false);
                }
            };

            const submitFilters = () => {
                const params = new URLSearchParams(new FormData(form));
                const url = `${form.action}?${params.toString()}`;
                requestAndRender(url);
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                submitFilters();
            });

            if (unidadeFilter) {
                unidadeFilter.addEventListener('change', submitFilters);
            }

            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (input.value.trim() === lastSubmittedValue) return;
                    lastSubmittedValue = input.value.trim();
                    submitFilters();
                }, 700); // 700ms debounce
            });

            resultsContainer.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (!link) return;

                if (link.hasAttribute('data-async') || link.closest('nav[role="navigation"]')) {
                    event.preventDefault();
                    requestAndRender(link.href);
                }
            });
        })();
    </script>
</x-app-layout>
