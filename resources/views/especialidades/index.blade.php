<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">
                Biblioteca de Especialidades
            </h2>
        </div>
    </x-slot>

    <div class="ui-page space-y-8 max-w-[1400px] ui-animate-fade-up">

        {{-- Cabeçalho Titular & Busca --}}
        <div class="flex flex-col gap-6 px-4 sm:px-0">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Especialidades</h1>
                <p class="text-slate-500 font-medium">Catalogo completo de honras, conhecimentos e aptidoes dos membros.</p>
            </div>

            <div class="w-full">
                <form method="GET" action="{{ route('especialidades.index') }}" class="w-full grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-3 items-stretch">
                    <div class="relative group sm:col-span-2 xl:col-span-4">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-[#002F6C]">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-[#002F6C] dark:group-focus-within:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nome..." class="ui-input pl-11 h-12 w-full text-[14px] font-bold tracking-wide rounded-2xl border-2 hover:border-slate-300 focus:border-[#002F6C] dark:focus:border-blue-500 transition-all bg-white dark:bg-slate-800/80">
                    </div>

                    <select name="area" class="ui-input h-12 w-full text-[14px] font-bold rounded-2xl border-2 hover:border-slate-300 focus:border-[#002F6C] dark:focus:border-blue-500 transition-all bg-white dark:bg-slate-800/80 sm:col-span-1 xl:col-span-2">
                        <option value="">Todas as categorias</option>
                        @foreach($areas as $area)
                            <option value="{{ $area }}" {{ $selectedArea === $area ? 'selected' : '' }}>{{ $area }}</option>
                        @endforeach
                    </select>

                    <select name="investidos" class="ui-input h-12 w-full text-[14px] font-bold rounded-2xl border-2 hover:border-slate-300 focus:border-[#002F6C] dark:focus:border-blue-500 transition-all bg-white dark:bg-slate-800/80 sm:col-span-1 xl:col-span-2">
                        <option value="">Todos os niveis</option>
                        <option value="com" {{ $investidos === 'com' ? 'selected' : '' }}>Com investidos</option>
                        <option value="sem" {{ $investidos === 'sem' ? 'selected' : '' }}>Sem investidos</option>
                    </select>

                    <label class="inline-flex items-center gap-2 px-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 min-h-12 bg-white dark:bg-slate-800/80 text-sm font-bold text-slate-600 dark:text-slate-200 sm:col-span-1 xl:col-span-2">
                        <input type="checkbox" name="avancadas" value="1" {{ $somenteAvancadas ? 'checked' : '' }} class="rounded border-slate-300 text-[#002F6C] focus:ring-[#002F6C]">
                        Somente avancadas
                    </label>

                    <div class="grid grid-cols-2 gap-3 sm:col-span-2 xl:col-span-2">
                        <button type="submit" class="ui-btn-primary h-12 px-5 rounded-2xl">Filtrar</button>
                        <a href="{{ route('especialidades.index') }}" class="h-12 px-5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 font-bold text-slate-600 dark:text-slate-300 inline-flex items-center justify-center">Limpar</a>
                    </div>
                </form>

                <div class="mt-3 flex justify-stretch sm:justify-end">
                    <a href="{{ route('especialidades.create') }}" class="ui-btn-primary h-12 w-full sm:w-auto px-6 rounded-2xl flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                        <span>Nova <span class="hidden sm:inline">Especialidade</span></span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Grid Premium de Especialidades --}}
        @if ($especialidades->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 px-4 sm:px-0">
                @foreach ($especialidades as $especialidade)
                    @php
                        $area = \Illuminate\Support\Str::of($especialidade->area)->ascii()->lower()->value();
                        
                        // Sistema de Cores Premium Customizado para o Design System DBV
                        $colors = match (true) {
                            str_contains($area, 'natureza')
                                => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-700 dark:text-emerald-400', 'lightBg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'border' => 'border-emerald-200 dark:border-emerald-500/20'],
                            str_contains($area, 'adra') || str_contains($area, 'comunidade')
                                => ['bg' => 'bg-cyan-500', 'text' => 'text-cyan-700 dark:text-cyan-400', 'lightBg' => 'bg-cyan-50 dark:bg-cyan-500/10', 'border' => 'border-cyan-200 dark:border-cyan-500/20'],
                            str_contains($area, 'artes') || str_contains($area, 'habilidades')
                                => ['bg' => 'bg-purple-500', 'text' => 'text-purple-700 dark:text-purple-400', 'lightBg' => 'bg-purple-50 dark:bg-purple-500/10', 'border' => 'border-purple-200 dark:border-purple-500/20'],
                            str_contains($area, 'saude') || str_contains($area, 'ciencia')
                                => ['bg' => 'bg-rose-500', 'text' => 'text-rose-700 dark:text-rose-400', 'lightBg' => 'bg-rose-50 dark:bg-rose-500/10', 'border' => 'border-rose-200 dark:border-rose-500/20'],
                            str_contains($area, 'atividades') || str_contains($area, 'recreacao')
                                => ['bg' => 'bg-amber-500', 'text' => 'text-amber-700 dark:text-amber-400', 'lightBg' => 'bg-amber-50 dark:bg-amber-500/10', 'border' => 'border-amber-200 dark:border-amber-500/20'],
                            default
                                => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-700 dark:text-indigo-400', 'lightBg' => 'bg-indigo-50 dark:bg-indigo-500/10', 'border' => 'border-indigo-200 dark:border-indigo-500/20'],
                        };
                    @endphp

                    <div class="ui-card group flex flex-col h-full bg-white dark:bg-slate-800/80 rounded-3xl overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        
                        {{-- Top Color Bar --}}
                        <div class="h-1.5 w-full {{ $colors['bg'] }}"></div>

                        <div class="p-6 flex flex-col flex-1">
                            
                            {{-- Area Badge --}}
                            <div class="mb-4 flex items-center justify-between">
                                <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $colors['lightBg'] }} {{ $colors['text'] }} {{ $colors['border'] }}">
                                    {{ $especialidade->area }}
                                </span>
                                
                                <div class="w-8 h-8 rounded-full {{ $colors['lightBg'] }} flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 {{ $colors['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                            </div>

                            {{-- Tile / Nome --}}
                            <h3 class="text-lg font-black text-slate-800 dark:text-white leading-tight mb-2 group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transition-colors">
                                {{ $especialidade->nome }}
                            </h3>
                            <p class="text-xs font-black tracking-widest uppercase text-slate-400 mb-3">
                                {{ $especialidade->codigo ?? 'Sem código' }}
                            </p>

                            <div class="mt-auto pt-5 border-t border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 group-hover:bg-[#002F6C]/10 dark:group-hover:bg-blue-500/10 group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        <span class="text-slate-800 dark:text-white font-black">{{ $especialidade->desbravadores_count ?? 0 }}</span> Investidos
                                    </span>
                                </div>
                                <a href="{{ route('especialidades.show', $especialidade) }}" class="text-xs font-black uppercase tracking-wider text-[#002F6C] dark:text-blue-400 hover:underline">
                                    Detalhes
                                </a>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginação Customizada se Houver --}}
            @if($especialidades->hasPages())
                <div class="mt-8 px-4 sm:px-0">
                    {{ $especialidades->links() }}
                </div>
            @endif
            
        @else
            <div class="px-4 sm:px-0">
                <div class="ui-empty">
                    <div class="ui-empty-icon"><svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div>
                    <h3 class="ui-empty-title">Nenhuma especialidade encontrada</h3>
                    <p class="ui-empty-description">{{ $search ? 'A busca não retornou resultados. Tente usar outras palavras.' : 'A biblioteca de especialidades do seu clube ainda está vazia.' }}</p>
                    
                    @if(!$search)
                    <div class="mt-6">
                        <a href="{{ route('especialidades.create') }}" class="ui-btn-primary">Criar a Primeira Especialidade</a>
                    </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
