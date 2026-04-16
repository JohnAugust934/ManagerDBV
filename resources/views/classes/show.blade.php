<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('classes.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="flex items-center gap-4">
                <div class="w-2.5 h-8 rounded-full shadow-sm" style="background-color: {{ $classe->cor }}"></div>
                <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight uppercase tracking-tight">
                    {{ $classe->nome }}
                </h2>
            </div>
        </div>
    </x-slot>

    {{-- Setup do Alpine --}}
    <div x-data="classManager({{ $classe->id }}, {{ $desbravadores->toJson() }})" class="ui-page space-y-6 max-w-7xl pb-20 ui-animate-fade-up">

        {{-- Estatísticas Rápidas e Controles --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4">
            <div class="ui-card flex items-center gap-4 px-6 py-3 w-full sm:w-auto">
                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total de Inscritos</p>
                    <p class="text-xl font-black text-slate-800 dark:text-white leading-none">{{ $desbravadores->count() }} Alunos</p>
                </div>
            </div>
        </div>

        {{-- UI Tabs Premium --}}
        <div class="ui-card p-2 sm:p-3 overflow-x-auto custom-scrollbar flex gap-2 w-full sm:w-max">
            <button @click="activeTab = 'alunos'" 
                :class="activeTab === 'alunos' ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all relative">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Controle por Aluno
            </button>
            
            <button @click="activeTab = 'lote'" 
                :class="activeTab === 'lote' ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all relative">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Aula em Lote
            </button>
        </div>

        {{-- TAB VIEW: ALUNOS --}}
        <div x-show="activeTab === 'alunos'" class="ui-animate-fade-up">
            @if ($desbravadores->isEmpty())
                <div class="ui-empty mt-0">
                    <div class="ui-empty-icon"><svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                    <h3 class="ui-empty-title">Nenhum aluno nesta classe</h3>
                    <p class="ui-empty-description">Para que os desbravadores apareçam aqui, vincule a classe correspondente ao perfil de cada membro na secretaria.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($desbravadores as $dbv)
                        <div @click="openStudentDrawer({{ $dbv->id }})" class="ui-card p-5 group cursor-pointer hover:border-[#002F6C]/30 dark:hover:border-blue-500/50 hover:shadow-lg transition-all duration-300">
                            
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full border-2 border-slate-100 dark:border-slate-700 overflow-hidden bg-slate-50 dark:bg-slate-900 flex items-center justify-center shrink-0 shadow-inner group-hover:scale-105 transition-transform" style="border-color: {{ $classe->cor }}44">
                                    @if ($dbv->foto)
                                        <img class="w-full h-full object-cover" src="{{ asset('storage/' . $dbv->foto) }}" alt="Avatar">
                                    @else
                                        <span class="text-xl font-black text-slate-400" style="color: {{ $classe->cor }}">{{ mb_strtoupper(substr($dbv->nome, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-black text-slate-800 dark:text-white truncate text-[15px] group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transition-colors uppercase leading-tight">{{ $dbv->nome }}</h3>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $dbv->cargo ?? 'Membro' }}</p>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 group-hover:bg-[#002F6C]/10 dark:group-hover:bg-blue-500/20 group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transition-colors">
                                    <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>

                            {{-- Barra de Progresso --}}
                            <div>
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none">Progresso atual</span>
                                    <span class="text-xs font-black leading-none" style="color: {{ $classe->cor }}">{{ $dbv->progresso_percentual }}%</span>
                                </div>
                                <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden shadow-inner">
                                    <div class="h-full rounded-full transition-all duration-1000 ease-out" style="width: {{ $dbv->progresso_percentual }}%; background-color: {{ $classe->cor }}"></div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB VIEW: AULA EM LOTE --}}
        <div x-show="activeTab === 'lote'" x-cloak class="ui-animate-fade-up">
            <div class="ui-card p-6 sm:p-8">
                
                <h3 class="text-[15px] font-black text-[#002F6C] dark:text-blue-400 uppercase tracking-wider mb-2">Selecione o Requisito</h3>
                <p class="text-sm font-medium text-slate-500 mb-6">Escolha o requisito ensinado na aula para assinar o cartão de todos os alunos selecionados simultaneamente.</p>

                <div class="mb-8 relative">
                    <select x-model="selectedRequisitoId" class="ui-input appearance-none pr-10 bg-slate-50 dark:bg-slate-900/50 font-bold border-2 focus:border-[#002F6C]">
                        <option value="">-- Clique aqui para escolher um requisito --</option>
                        @foreach ($classe->requisitos as $req)
                            <option value="{{ $req->id }}">[{{ $req->codigo }}] - {{ Str::limit($req->descricao, 90) }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <div x-show="selectedRequisitoId" x-collapse.duration.300ms>
                    <div class="flex justify-between items-center pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#D9222A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Lista de Assinatura
                        </h3>
                        <span class="ui-badge bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 px-3 py-1">Marcação Automática</span>
                    </div>

                    @if ($desbravadores->isEmpty())
                        <div class="ui-empty mt-0 py-6 border-none shadow-none">
                            <h3 class="ui-empty-title">Classe Vazia</h3>
                            <p class="ui-empty-description">Sem alunos na classe para aplicar em lote.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <template x-for="student in students" :key="student.id">
                                <label class="ui-card p-4 flex items-center border-[2px] border-slate-100 dark:border-slate-800 cursor-pointer hover:border-[#002F6C]/40 dark:hover:border-blue-500/40 transition-colors shadow-none hover:shadow-sm">
                                    <div class="relative flex items-center mr-4">
                                        <input type="checkbox" :checked="student.ids_cumpridos.includes(parseInt(selectedRequisitoId))" @change="toggleRequirement(student.id, selectedRequisitoId, $event.target.checked)" class="w-6 h-6 border-2 border-slate-300 dark:border-slate-600 rounded drop-shadow-sm text-emerald-500 focus:ring-emerald-500 bg-white dark:bg-slate-900 cursor-pointer">
                                    </div>
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                                            <span class="text-xs font-black text-slate-400" x-text="student.nome.substr(0,1).toUpperCase()"></span>
                                        </div>
                                        <span class="text-sm font-black text-slate-700 dark:text-slate-200 uppercase truncate w-full" x-text="student.nome"></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    @endif
                </div>

                <div x-show="!selectedRequisitoId">
                    <div class="ui-empty mt-0 py-10 bg-slate-50/50 dark:bg-slate-900/20 border-dashed">
                        <div class="ui-empty-icon"><svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg></div>
                        <h3 class="ui-empty-title">Aguardando Seleção</h3>
                        <p class="ui-empty-description">Escolha um requisito na lista acima para exibir a turma.</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- DRAWER LATERAL (SISTEMA DE ASINATURA INDIVIDUAL) --}}
        <template x-teleport="body">
            <div x-show="drawerOpen"
                x-cloak
                @keydown.escape.window="drawerOpen = false"
                class="fixed inset-0 z-[120] overflow-hidden"
                aria-labelledby="slide-over-title"
                role="dialog"
                aria-modal="true"
                x-transition:enter="transition-opacity ease-out duration-250"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">

                {{-- Backdrop Escuro --}}
                <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-[2px]"
                    @click="drawerOpen = false"
                    x-transition:enter="transition-opacity ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-250"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"></div>

                <div class="absolute inset-y-0 right-0 max-w-full flex">
                    {{-- Painel Drawer --}}
                    <div class="w-screen max-w-xl sm:max-w-lg h-full will-change-transform origin-right"
                        x-transition:enter="transform transition duration-520 ease-[cubic-bezier(0.22,1,0.36,1)]"
                        x-transition:enter-start="translate-x-full scale-[0.985]"
                        x-transition:enter-end="translate-x-0 scale-100"
                        x-transition:leave="transform transition duration-320 ease-in"
                        x-transition:leave-start="translate-x-0 scale-100"
                        x-transition:leave-end="translate-x-full scale-[0.985]">

                        <div class="h-full flex flex-col bg-slate-50 dark:bg-slate-900 shadow-custom-heavy rounded-l-[28px] sm:rounded-l-[40px] overflow-hidden border-l border-white/20 dark:border-white/5">
                        
                        {{-- Drawer Header Customizado com a cor da Classe --}}
                        <div class="px-6 py-8 relative shrink-0 transition-all duration-500 ease-out"
                            :class="drawerOpen ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'"
                            style="transition-delay: 70ms; background: linear-gradient(135deg, {{ $classe->cor }}, {{ $classe->cor }}dd);">
                            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white to-transparent"></div>
                            
                            <div class="relative z-10 flex items-start justify-between">
                                <div class="pr-8">
                                    <span class="inline-block px-3 py-1 bg-black/10 backdrop-blur-md rounded-lg text-white/90 text-[10px] font-black uppercase tracking-widest mb-3 border border-white/10">Cartão Analítico</span>
                                    <h2 class="text-2xl font-black text-white leading-tight uppercase tracking-tight" id="slide-over-title">
                                        <span x-text="currentStudent?.nome"></span>
                                    </h2>
                                </div>
                                <button @click="drawerOpen = false" class="w-10 h-10 rounded-full bg-black/10 hover:bg-black/20 backdrop-blur-md flex items-center justify-center text-white/80 hover:text-white transition-colors focus:outline-none ring-1 ring-white/10 shrink-0">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Drawer Body Conteúdo (Checklist) --}}
                        <div class="relative flex-1 py-6 px-6 sm:px-8 overflow-y-auto custom-scrollbar transition-all duration-500 ease-out"
                            :class="drawerOpen ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'"
                            style="transition-delay: 140ms;">
                            <template x-if="currentStudent">
                                <div class="space-y-8">
                                    @php $currentCat = ''; @endphp
                                    @foreach ($classe->requisitos as $req)
                                    
                                        {{-- Categoria Divisor --}}
                                        @if ($currentCat != $req->categoria)
                                            @php $currentCat = $req->categoria; @endphp
                                            <div class="relative pt-4 pb-2 first:pt-0">
                                                <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t-[2px] border-dashed border-slate-200 dark:border-slate-800"></div></div>
                                                <div class="relative flex justify-start">
                                                    <span class="pr-4 bg-slate-50 dark:bg-slate-900 text-[11px] font-black text-[#002F6C] dark:text-blue-400 uppercase tracking-widest">
                                                        {{ $req->categoria }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Card do Requisito --}}
                                        <label class="ui-card flex flex-col sm:flex-row items-start sm:items-center p-4 border-[2px] border-slate-100 hover:border-[#002F6C]/30 dark:hover:border-blue-500/30 dark:border-slate-800 transition-colors cursor-pointer group shadow-sm bg-white dark:bg-slate-800/80 gap-4 mb-3">
                                            <div class="flex items-center shrink-0">
                                                <input type="checkbox" :id="'req_' + {{ $req->id }}"
                                                    :checked="currentStudent.ids_cumpridos.includes({{ $req->id }})"
                                                    @change="toggleRequirement(currentStudent.id, {{ $req->id }}, $event.target.checked)"
                                                    class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 text-emerald-500 focus:ring-emerald-500 cursor-pointer shadow-inner">
                                            </div>
                                            <div class="flex flex-col min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                                    <span class="bg-slate-100 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded text-[10px] font-black uppercase font-mono tracking-widest">{{ $req->codigo }}</span>
                                                </div>
                                                <span class="text-sm font-bold text-slate-700 dark:text-slate-200 leading-snug group-hover:text-slate-900 dark:group-hover:text-white transition-colors">
                                                    {{ $req->descricao }}
                                                </span>
                                            </div>
                                        </label>

                                    @endforeach
                                    
                                    <div class="pt-8 pb-10 text-center">
                                        <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Fim dos Requisitos</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>

    {{-- Script Lógico Alpine.js --}}
    <script>
        function classManager(classeId, initialStudents) {
            return {
                activeTab: 'alunos',
                drawerOpen: false,
                students: initialStudents,
                currentStudent: null,
                selectedRequisitoId: '',

                openStudentDrawer(studentId) {
                    this.currentStudent = this.students.find(s => s.id === studentId);
                    requestAnimationFrame(() => {
                        this.drawerOpen = true;
                    });
                },

                async toggleRequirement(studentId, reqId, isChecked) {
                    let student = this.students.find(s => s.id === studentId);
                    if (isChecked) {
                        student.ids_cumpridos.push(parseInt(reqId));
                    } else {
                        student.ids_cumpridos = student.ids_cumpridos.filter(id => id !== parseInt(reqId));
                    }

                    // Recalcula o progresso de forma otimista
                    if(student.ids_cumpridos.length === 0) {
                        student.progresso_percentual = 0;
                    } else {
                        // Calcula % vs total de requisitos no blade
                        let totalReqs = {{ $classe->requisitos->count() }};
                        let filledReqs = student.ids_cumpridos.length;
                        student.progresso_percentual = Math.round((filledReqs / totalReqs) * 100);
                        if(student.progresso_percentual > 100) student.progresso_percentual = 100;
                    }

                    try {
                        const response = await fetch("{{ route('classes.toggle') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                desbravador_id: studentId,
                                requisito_id: reqId,
                                concluido: isChecked
                            })
                        });

                        if (!response.ok) throw new Error('Erro ao salvar no backend');
                    } catch (error) {
                        console.error(error);
                        // Idealmente exibir Toast de erro via componente
                    }
                }
            }
        }
    </script>
</x-app-layout>
