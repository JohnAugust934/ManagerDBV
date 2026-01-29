<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestão de Unidade
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                <a href="{{ route('unidades.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 flex items-center font-bold text-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar para Lista
                </a>

                <a href="{{ route('unidades.edit', $unidade->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md font-bold text-xs uppercase tracking-widest shadow-sm transition focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Editar Dados da Unidade
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">

                        <div>
                            <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">{{ $unidade->nome }}</h3>
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <span class="uppercase font-bold tracking-wide mr-2 text-xs text-indigo-600 dark:text-indigo-400">Conselheiro:</span>
                                <span class="font-medium text-lg text-gray-700 dark:text-gray-300">{{ $unidade->conselheiro }}</span>
                            </div>
                        </div>

                        @if($unidade->grito_guerra)
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-100 dark:border-gray-600 max-w-xl w-full md:w-auto relative">
                            <svg class="absolute top-2 left-2 w-6 h-6 text-gray-200 dark:text-gray-600 opacity-50 transform -scale-x-100" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14.017 21L14.017 18C14.017 16.054 15.65 14.773 17.683 14.225C17.935 14.156 18.106 13.918 18.082 13.658C17.228 13.791 16.365 13.575 15.674 13.065C14.28 12.036 14.017 10.05 14.017 8.007V5H21V12.083C21 16.92 17.892 20.89 14.017 21ZM5 21L5 18C5 16.054 6.633 14.773 8.667 14.225C8.918 14.156 9.09 13.918 9.065 13.658C8.212 13.791 7.348 13.575 6.658 13.065C5.263 12.036 5 10.05 5 8.007V5H11.983V12.083C11.983 16.92 8.875 20.89 5 21Z" />
                            </svg>
                            <p class="text-gray-700 dark:text-gray-300 italic font-serif text-center px-4 relative z-10">
                                "{{ $unidade->grito_guerra }}"
                            </p>
                        </div>
                        @else
                        <div class="text-gray-400 italic text-sm">
                            (Sem grito de guerra definido)
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Membros da Unidade
                    </h3>
                    <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        {{ $unidade->desbravadores->count() }} Desbravadores Ativos
                    </span>
                </div>

                <div class="p-0">
                    @if($unidade->desbravadores->isEmpty())
                    <div class="p-12 text-center flex flex-col items-center">
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 mb-4 font-medium">Nenhum desbravador ativo nesta unidade.</p>
                        <a href="{{ route('desbravadores.create') }}" class="text-indigo-600 hover:text-indigo-800 hover:underline font-bold text-sm">
                            + Cadastrar novo desbravador
                        </a>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-4">Nome</th>
                                    <th class="px-6 py-4">Classe</th>
                                    <th class="px-6 py-4 text-center">Idade</th>
                                    <th class="px-6 py-4 text-center">Ações Rápidas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($unidade->desbravadores as $dbv)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition group">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                        <a href="{{ route('desbravadores.show', $dbv) }}" class="hover:text-indigo-500 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold shadow-sm">
                                                {{ substr($dbv->nome, 0, 1) }}
                                            </div>
                                            {{ $dbv->nome }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-600 rounded text-xs text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-500">
                                            {{ $dbv->classe_atual }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($dbv->data_nascimento)->age }} anos
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('progresso.index', $dbv->id) }}" class="flex items-center px-2 py-1 bg-green-50 text-green-700 border border-green-200 rounded hover:bg-green-100 text-xs font-bold transition" title="Acompanhar Classes">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Classes
                                            </a>
                                            <a href="{{ route('desbravadores.especialidades', $dbv->id) }}" class="flex items-center px-2 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded hover:bg-purple-100 text-xs font-bold transition" title="Gerenciar Especialidades">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                                </svg>
                                                Espec.
                                            </a>
                                            <a href="{{ route('desbravadores.show', $dbv) }}" class="flex items-center px-2 py-1 bg-gray-50 text-gray-600 border border-gray-200 rounded hover:bg-gray-100 text-xs font-bold transition" title="Ver Prontuário">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>