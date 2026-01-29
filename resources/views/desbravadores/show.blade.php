<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Prontuário Digital
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-xl">
                        {{ substr($desbravador->nome, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $desbravador->nome }}</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $desbravador->classe_atual }} • {{ $desbravador->unidade->nome }}</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('relatorios.autorizacao', $desbravador->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Autorização PDF
                    </a>
                    <a href="{{ route('desbravadores.edit', $desbravador) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Dados
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