<x-app-layout>
    {{-- Injetando dados no cabeçalho do Layout Principal --}}
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-3 h-10 rounded-full shadow-sm" style="background-color: {{ $classe->cor }}"></div>

            <div>
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ $classe->nome }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $desbravadores->count() }} alunos matriculados
                </p>
            </div>
        </div>
    </x-slot>

    {{-- Configurações Alpine --}}
    <div x-data="classManager({{ $classe->id }}, {{ $desbravadores->toJson() }})" class="min-h-screen pb-20">

        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="px-0 flex justify-start">
                <a href="{{ route('classes.index') }}"
                    class="ui-btn-secondary w-full sm:w-auto"
                    title="Voltar">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>

            {{-- TABS DE NAVEGAÇÃO --}}
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'alunos'"
                        :class="activeTab === 'alunos'?
                            'border-dbv-blue text-dbv-blue dark:text-blue-400 dark:border-blue-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        Visão por Aluno
                    </button>

                    <button @click="activeTab = 'lote'"
                        :class="activeTab === 'lote'?
                            'border-dbv-blue text-dbv-blue dark:text-blue-400 dark:border-blue-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        Aula em Lote
                    </button>
                </nav>
            </div>

            {{-- CONTEÚDO TAB 1: ALUNOS --}}
            <div x-show="activeTab === 'alunos'" class="ui-animate-fade-up">
                @if ($desbravadores->isEmpty())
                    <x-empty-state
                        title="Nenhum aluno nesta classe"
                        description="Verifique a classificacao etaria e a vinculacao de classe dos desbravadores." />
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($desbravadores as $dbv)
                            <div @click="openStudentDrawer({{ $dbv->id }})"
                                class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-500 transition cursor-pointer flex items-center gap-4 group">

                                <div
                                    class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-lg font-bold text-gray-500 dark:text-gray-300 shrink-0">
                                    {{ substr($dbv->nome, 0, 1) }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3
                                        class="font-bold text-gray-800 dark:text-gray-100 truncate group-hover:text-dbv-blue dark:group-hover:text-blue-400 transition-colors">
                                        {{ $dbv->nome }}</h3>

                                    <div
                                        class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-{{ $classe->cor == '#3B82F6'? 'blue' : 'green' }}-500 h-2.5 rounded-full transition-all duration-500"
                                            style="width: {{ $dbv->progresso_percentual }}%; background-color: {{ $classe->cor }}">
                                        </div>
                                    </div>
                                    <p class="text-xs text-right mt-1 text-gray-400">{{ $dbv->progresso_percentual }}%
                                        concluído</p>
                                </div>

                                <svg class="w-5 h-5 text-gray-300 group-hover:text-dbv-blue dark:group-hover:text-blue-400"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- CONTEÚDO TAB 2: LOTE --}}
            <div x-show="activeTab === 'lote'" x-cloak
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 ui-animate-fade-up">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selecione o Requisito
                        da Aula de Hoje:</label>
                    <select x-model="selectedRequisitoId"
                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        <option value="">-- Escolha um requisito --</option>
                        @foreach ($classe->requisitos as $req)
                            <option value="{{ $req->id }}">
                                {{ $req->codigo }} - {{ Str::limit($req->descricao, 80) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="selectedRequisitoId" class="space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b dark:border-gray-700">
                        <h3 class="font-bold text-gray-800 dark:text-white">Lista de Aprovação</h3>
                        <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Marque para
                            assinar</span>
                    </div>

                    @if ($desbravadores->isEmpty())
                        <x-empty-state title="Sem alunos nesta classe" description="Inclua alunos nesta classe para registrar requisitos em lote." />
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <template x-for="student in students" :key="student.id">
                                <label
                                    class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition select-none">
                                    <input type="checkbox"
                                        class="w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-500 mr-3"
                                        :checked="student.ids_cumpridos.includes(parseInt(selectedRequisitoId))"
                                        @change="toggleRequirement(student.id, selectedRequisitoId, $event.target.checked)">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200"
                                        x-text="student.nome"></span>
                                </label>
                            </template>
                        </div>
                    @endif
                </div>

                <div x-show="!selectedRequisitoId">
                    <x-empty-state
                        title="Nenhum requisito selecionado"
                        description="Selecione um requisito acima para iniciar a aula em lote." />
                </div>
            </div>

        </div>

        {{-- DRAWER LATERAL (CORRIGIDO) --}}
        {{-- Adicionado z-50 para ficar acima de tudo e bg-gray-900/75 para o backdrop escuro --}}
        <div x-show="drawerOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title"
            role="dialog" aria-modal="true" x-cloak>

            {{-- Backdrop Escuro --}}
            <div class="absolute inset-0 bg-gray-900/75 transition-opacity backdrop-blur-sm"
                @click="drawerOpen = false" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                {{-- Painel do Drawer --}}
                <div class="w-screen max-w-md transform transition ease-in-out duration-300 sm:duration-500"
                    x-transition:enter="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="translate-x-0" x-transition:leave-end="translate-x-full">

                    {{-- Conteúdo Sólido (bg-white/dark:bg-gray-800) --}}
                    <div class="h-full flex flex-col bg-white dark:bg-gray-800 shadow-2xl">

                        {{-- Drawer Header --}}
                        <div class="px-4 py-6 sm:px-6" style="background-color: {{ $classe->cor }}; color: white;">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-lg font-bold text-white leading-6" id="slide-over-title">
                                        <span x-text="currentStudent?.nome"></span>
                                    </h2>
                                    <p class="text-white/80 text-sm mt-1">Checklist individual</p>
                                </div>
                                <button @click="drawerOpen = false"
                                    class="text-white/70 hover:text-white focus:outline-none">
                                    <span class="sr-only">Fechar</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Drawer Body --}}
                        <div
                            class="relative flex-1 py-6 px-4 sm:px-6 overflow-y-auto custom-scrollbar bg-white dark:bg-gray-800">
                            <template x-if="currentStudent">
                                <div class="space-y-6">
                                    @php $currentCat = ''; @endphp
                                    @foreach ($classe->requisitos as $req)
                                        @if ($currentCat != $req->categoria)
                                            @php $currentCat = $req->categoria; @endphp
                                            <div class="relative pt-2">
                                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                                    <div class="w-full border-t border-gray-200 dark:border-gray-600">
                                                    </div>
                                                </div>
                                                <div class="relative flex justify-start">
                                                    <span
                                                        class="pr-2 bg-white dark:bg-gray-800 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                        {{ $req->categoria }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                        <div
                                            class="flex items-start p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition border border-transparent hover:border-gray-200 dark:hover:border-gray-600 group">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" :id="'req_' + {{ $req->id }}"
                                                    :checked="currentStudent.ids_cumpridos.includes({{ $req->id }})"
                                                    @change="toggleRequirement(currentStudent.id, {{ $req->id }}, $event.target.checked)"
                                                    class="focus:ring-indigo-500 h-5 w-5 text-green-600 border-gray-300 rounded cursor-pointer transition">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label :for="'req_' + {{ $req->id }}"
                                                    class="font-medium text-gray-700 dark:text-gray-200 cursor-pointer group-hover:text-black dark:group-hover:text-white transition-colors">
                                                    {{ $req->descricao }}
                                                </label>
                                                <p class="text-xs text-gray-400 mt-1 font-mono">Cód:
                                                    {{ $req->codigo }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Script Lógico Alpine.js (Mantido igual, só para garantir a referência) --}}
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
                    this.drawerOpen = true;
                },

                async toggleRequirement(studentId, reqId, isChecked) {
                    let student = this.students.find(s => s.id === studentId);
                    if (isChecked) {
                        student.ids_cumpridos.push(parseInt(reqId));
                    } else {
                        student.ids_cumpridos = student.ids_cumpridos.filter(id => id !== parseInt(reqId));
                    }

                    // Cálculo simples de progresso visual (opcional)
                    // student.progresso_percentual = ...

                    try {
                        const response = await fetch("{{ route('classes.toggle') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                desbravador_id: studentId,
                                requisito_id: reqId,
                                concluido: isChecked
                            })
                        });

                        if (!response.ok) throw new Error('Erro ao salvar');
                    } catch (error) {
                        console.error(error);
                        alert('Erro de conexão ao salvar.');
                    }
                }
            }
        }
    </script>
</x-app-layout>



