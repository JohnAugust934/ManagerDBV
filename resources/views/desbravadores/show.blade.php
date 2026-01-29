<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Prontuário Digital
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm flex flex-col lg:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4 w-full lg:w-auto">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-xl shrink-0">
                        {{ substr($desbravador->nome, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ $desbravador->nome }}</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $desbravador->classe_atual }} • {{ $desbravador->unidade->nome }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center lg:justify-end gap-2 w-full lg:w-auto">
                    <a href="{{ route('progresso.index', $desbravador->id) }}" class="flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded shadow-sm text-xs font-bold uppercase transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Classes
                    </a>

                    <a href="{{ route('desbravadores.especialidades', $desbravador->id) }}" class="flex items-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded shadow-sm text-xs font-bold uppercase transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        Especialidades
                    </a>

                    <a href="{{ route('desbravadores.edit', $desbravador) }}" class="flex items-center px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded shadow-sm text-xs font-bold uppercase transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Editar
                    </a>

                    <a href="{{ route('relatorios.autorizacao', $desbravador->id) }}" target="_blank" class="flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded shadow-sm text-xs font-bold uppercase transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="md:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6 h-fit">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Informações Gerais</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between border-b dark:border-gray-700 pb-2">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Status</span>
                            @if($desbravador->ativo)
                            <span class="text-green-600 font-bold text-sm">Ativo</span>
                            @else
                            <span class="text-red-600 font-bold text-sm">Inativo</span>
                            @endif
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-2">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Idade</span>
                            <span class="text-gray-900 dark:text-gray-200 font-medium text-sm">
                                {{ \Carbon\Carbon::parse($desbravador->data_nascimento)->age }} anos
                            </span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-2">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Sexo</span>
                            <span class="text-gray-900 dark:text-gray-200 font-medium text-sm">
                                {{ $desbravador->sexo == 'M' ? 'Masculino' : 'Feminino' }}
                            </span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-2">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Nascimento</span>
                            <span class="text-gray-900 dark:text-gray-200 font-medium text-sm">
                                {{ $desbravador->data_nascimento->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 space-y-6">

                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h4 class="text-md font-bold text-indigo-600 dark:text-indigo-400 mb-4 flex items-center gap-2">
                            Contato e Responsável
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Responsável Legal</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $desbravador->nome_responsavel }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Telefone Responsável</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $desbravador->telefone_responsavel }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">E-mail</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $desbravador->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Telefone Pessoal</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $desbravador->telefone ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase">Endereço</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $desbravador->endereco }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-l-4 border-red-400">
                        <h4 class="text-md font-bold text-red-600 dark:text-red-400 mb-4 flex items-center gap-2">
                            Dados Médicos
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Cartão SUS</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $desbravador->numero_sus }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Tipo Sanguíneo</p>
                                <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded">
                                    {{ $desbravador->tipo_sanguineo ?? 'Não Inf.' }}
                                </span>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase">Plano de Saúde</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $desbravador->plano_saude ?? 'Não possui' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase">Alergias</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $desbravador->alergias ?? 'Nenhuma relatada' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase">Medicamentos Contínuos</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $desbravador->medicamentos_continuos ?? 'Nenhum' }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>