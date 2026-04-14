<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('desbravadores.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">
                Perfil do Desbravador
            </h2>
        </div>
    </x-slot>

    <div class="ui-page space-y-6 max-w-6xl ui-animate-fade-up">
        
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 px-2 sm:px-0 mb-2">
            <a href="{{ route('desbravadores.edit', $desbravador) }}" class="ui-btn-primary w-full sm:w-auto text-sm group">
                <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Editar Cadastro
            </a>
        </div>

        {{-- Cabeçalho do Perfil (Cover + Foto + Dados Rápidos) --}}
        <div class="ui-card overflow-visible">
            {{-- Banner Cover --}}
            <div class="h-32 sm:h-40 bg-gradient-to-r from-[#002F6C] to-blue-800 dark:from-slate-800 dark:to-slate-900 rounded-t-3xl border-b-4 border-[#FCD116] relative overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
            </div>

            <div class="px-6 sm:px-10 pb-8">
                <div class="flex flex-col sm:flex-row items-center sm:items-end -mt-16 sm:-mt-20 gap-6">
                    
                    {{-- Avatar Circular --}}
                    <div class="relative shrink-0 group">
                        <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full border-[6px] border-white dark:border-[#18181b] bg-slate-100 dark:bg-slate-800 flex items-center justify-center shadow-xl overflow-hidden relative z-10 transition-transform group-hover:scale-105">
                            @if ($desbravador->foto)
                                <img class="w-full h-full object-cover" src="{{ asset('storage/' . $desbravador->foto) }}" alt="Foto">
                            @else
                                <span class="text-4xl font-black text-slate-300 dark:text-slate-600">{{ mb_strtoupper(substr($desbravador->nome, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="absolute bottom-4 right-1 sm:right-3 w-6 h-6 rounded-full border-4 border-white dark:border-[#18181b] z-20 {{ $desbravador->ativo ? 'bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)]' : 'bg-red-500' }}" title="{{ $desbravador->ativo ? 'Ativo' : 'Inativo' }}"></div>
                    </div>

                    {{-- Info Principal --}}
                    <div class="flex-1 text-center sm:text-left mb-2 sm:mb-4">
                        <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight leading-none mb-2">{{ $desbravador->nome }}</h1>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-3">
                            <span class="ui-badge bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1">
                                {{ $desbravador->cargo ?? 'Desbravador' }}
                            </span>
                            <span class="font-bold text-slate-400">&bull;</span>
                            <span class="text-[13px] font-bold text-slate-500">
                                {{ \Carbon\Carbon::parse($desbravador->data_nascimento)->age }} ANOS
                            </span>
                        </div>
                    </div>

                    {{-- Unidade Widget --}}
                    @if ($desbravador->unidade)
                        <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-800/50 px-5 py-3 rounded-2xl border border-slate-100 dark:border-slate-700 mb-2 sm:mb-4 shadow-sm">
                            <div class="text-right">
                                <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Unidade</p>
                                <p class="font-black text-[#002F6C] dark:text-blue-400 text-lg leading-tight">{{ $desbravador->unidade->nome }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-[#002F6C] text-white flex items-center justify-center font-black text-xl shadow-inner">
                                {{ mb_strtoupper(substr($desbravador->unidade->nome, 0, 1)) }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Status Bar Rápido --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-8 border-t border-slate-100 dark:border-slate-700">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nascimento</p>
                            <p class="font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($desbravador->data_nascimento)->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-500 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2-2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Classe Atual</p>
                            <p class="font-bold text-slate-700 dark:text-slate-300 truncate">{{ $desbravador->classe->nome ?? 'Nenhuma' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Últimas Reuniões</p>
                            <div class="flex gap-1.5 mt-1">
                                @forelse($desbravador->frequencias as $freq)
                                    <div class="w-3 h-3 rounded-full {{ $freq->presente ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.4)]' : 'bg-red-400' }}" title="{{ \Carbon\Carbon::parse($freq->data)->format('d/m') }}: {{ $freq->presente ? 'Presente' : 'Falta' }}"></div>
                                @empty
                                    <span class="text-xs text-slate-400">-</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-500 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sangue</p>
                            <p class="font-black text-red-600 dark:text-red-400">{{ $desbravador->tipo_sanguineo ?? 'N/I' }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Alerta de Saúde (Caso Possua) --}}
        @if ($desbravador->alergias || $desbravador->medicamentos_continuos)
            <div class="ui-card border-l-8 border-l-red-500 bg-red-50/50 dark:bg-red-900/10 p-6 flex flex-col md:flex-row gap-6 ui-animate-fade-up" style="animation-delay: 100ms;">
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-500/20 text-red-500 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 flex-1">
                    @if ($desbravador->alergias)
                        <div>
                            <h4 class="text-[12px] font-black uppercase tracking-wider text-red-800 dark:text-red-400 mb-1">Alergias</h4>
                            <p class="font-bold text-red-700 dark:text-red-300">{{ $desbravador->alergias }}</p>
                        </div>
                    @endif
                    @if ($desbravador->medicamentos_continuos)
                        <div>
                            <h4 class="text-[12px] font-black uppercase tracking-wider text-red-800 dark:text-red-400 mb-1">Uso de Medicamentos</h4>
                            <p class="font-bold text-red-700 dark:text-red-300">{{ $desbravador->medicamentos_continuos }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Grade de Informações Detalhadas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Coluna 1: Documentos, Contato e Responsável --}}
            <div class="lg:col-span-1 space-y-6 ui-animate-fade-up" style="animation-delay: 150ms;">
                
                {{-- Documentos Administrativos --}}
                <div class="ui-card p-6 bg-slate-50/50 dark:bg-slate-900/30">
                    <h3 class="text-[15px] font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 mb-5 pb-3 border-b border-slate-200 dark:border-slate-800">
                        Documentos
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">CPF</p>
                            <p class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $desbravador->cpf }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">RG</p>
                            <p class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $desbravador->rg ?? 'N/I' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Contato --}}
                <div class="ui-card p-6 bg-slate-50/50 dark:bg-slate-900/30">
                    <h3 class="text-[15px] font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 mb-5 pb-3 border-b border-slate-200 dark:border-slate-800">
                        Contato
                    </h3>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-slate-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <div class="overflow-hidden">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Email Principal</p>
                                <p class="font-bold text-slate-700 dark:text-slate-300 truncate">{{ $desbravador->email ?? 'N/I' }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-slate-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Telefone</p>
                                <p class="font-bold text-slate-700 dark:text-slate-300">{{ $desbravador->telefone ?? 'N/I' }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-slate-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Endereço Residencial</p>
                                <p class="font-bold text-slate-700 dark:text-slate-300">{{ $desbravador->endereco }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Responsável --}}
                <div class="ui-card p-6 bg-amber-50/50 dark:bg-amber-900/10 border-amber-100 dark:border-amber-900/30">
                    <h3 class="text-[15px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-500 mb-5 pb-3 border-b border-amber-200 dark:border-amber-800/50">
                        Responsável Legal
                    </h3>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <div>
                                <p class="text-[11px] font-bold text-amber-600/70 dark:text-amber-500/70 uppercase tracking-widest">Nome</p>
                                <p class="font-black text-amber-900 dark:text-amber-200">{{ $desbravador->nome_responsavel }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <div>
                                <p class="text-[11px] font-bold text-amber-600/70 dark:text-amber-500/70 uppercase tracking-widest">Telefone de Emergência</p>
                                <p class="font-black text-amber-900 dark:text-amber-200">{{ $desbravador->telefone_responsavel }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Coluna 2: Dados de Saúde & Especialidades --}}
            <div class="lg:col-span-2 space-y-6 ui-animate-fade-up" style="animation-delay: 200ms;">
                
                {{-- Saúde --}}
                <div class="ui-card p-6 bg-slate-50/50 dark:bg-slate-900/30 border-l-4 border-l-red-500">
                    <h3 class="text-[15px] font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 mb-5 pb-3 border-b border-slate-200 dark:border-slate-800">
                        Dados Médicos Seguros
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Cartão SUS</p>
                            <p class="font-mono text-sm font-bold bg-white dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm inline-block">
                                {{ $desbravador->numero_sus }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Plano de Saúde</p>
                            <p class="font-bold text-slate-700 dark:text-slate-300">
                                {{ $desbravador->plano_saude ?? 'Atendimento Exclusivo pelo SUS' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Especialidades Re-estilizadas Premium --}}
                <div class="ui-card p-6">
                    <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-[15px] font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#FCD116]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" /></svg>
                            Mural de Especialidades
                        </h3>
                        <a href="{{ route('desbravadores.especialidades', $desbravador) }}" class="ui-btn-secondary py-1.5 px-3 text-xs">
                            Gerenciar Insígnias
                        </a>
                    </div>

                    @if ($desbravador->especialidades->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach ($desbravador->especialidades as $esp)
                                <div class="group relative flex flex-col items-center justify-center text-center p-4 rounded-2xl bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900/50 border border-slate-200 dark:border-slate-700 hover:border-blue-400 dark:hover:border-blue-500 transition-colors shadow-sm">
                                    {{-- Triângulo SVG estático premium --}}
                                    <div class="mb-3 transition-transform group-hover:scale-110 duration-300">
                                        <svg width="48" height="42" viewBox="0 0 48 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M24 0L48 41.5692L0 41.5692L24 0Z" fill="url(#paint0_linear_esp)" stroke="#1e293b" stroke-width="2" stroke-linejoin="round"/>
                                            <defs>
                                                <linearGradient id="paint0_linear_esp" x1="24" y1="0" x2="24" y2="41.5692" gradientUnits="userSpaceOnUse">
                                                    <stop stop-color="#FCD116"/>
                                                    <stop offset="1" stop-color="#EAB308"/>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                    <span class="text-[11px] font-black uppercase text-slate-800 dark:text-slate-100 leading-tight mb-1">{{ $esp->nome }}</span>
                                    <div class="w-8 h-0.5 bg-slate-200 dark:bg-slate-700 my-1 rounded-full"></div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($esp->pivot->data_conclusao)->format('d . m . Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="ui-empty mt-0 shadow-none bg-slate-50/50 dark:bg-slate-900/20 max-h-[250px] min-h-[200px]">
                            <h3 class="font-black text-slate-400 mb-2">Uniforme Vazio</h3>
                            <p class="text-sm text-slate-500">Nenhuma especialidade registrada até o momento.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
