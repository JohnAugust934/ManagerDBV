<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-200 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                </path>
            </svg>
            {{ __('Recuperação de Desastres (Backups)') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ isBackingUp: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- PAINEL DE GERAÇÃO --}}
            <div
                class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 mb-6">
                <div
                    class="p-6 bg-red-50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-900/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-red-800 dark:text-red-400">Sistema de Segurança e Nuvem</h3>
                        <p class="text-sm text-red-600 dark:text-red-300 mt-1">
                            Gere uma cópia completa do Banco de Dados e dos Arquivos enviados. O backup será salvo
                            localmente e sincronizado automaticamente com o Cloudflare R2.
                        </p>
                    </div>

                    <form action="{{ route('backups.store') }}" method="POST" @submit="isBackingUp = true"
                        class="shrink-0 w-full md:w-auto">
                        @csrf
                        <button type="submit" :disabled="isBackingUp"
                            class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg shadow-red-500/30 transition-all flex items-center justify-center gap-2 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">

                            <span x-show="!isBackingUp" class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Gerar Novo Backup
                            </span>

                            <span x-show="isBackingUp" x-cloak class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Processando...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- LISTA DE BACKUPS --}}
            <div
                class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Arquivos de Segurança</h3>
                    <span
                        class="text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 px-3 py-1 rounded-full">
                        {{ count($backups) }} encontrados
                    </span>
                </div>

                @if (empty($backups))
                    <div class="p-10 text-center flex flex-col items-center">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                </path>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Nenhum backup encontrado.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300 hidden md:table-header-group">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold">Arquivo</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Local de Armazenamento</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Tamanho</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Gerado em</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-100 dark:divide-gray-700 flex flex-col md:table-row-group">
                                @foreach ($backups as $bkp)
                                    <tr
                                        class="flex flex-col md:table-row bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors p-4 md:p-0 gap-3 md:gap-0">
                                        <td class="px-2 md:px-6 py-2 md:py-4 font-semibold text-gray-900 dark:text-white truncate"
                                            style="max-width: 250px;">
                                            {{ $bkp['name'] }}
                                        </td>
                                        <td class="px-2 md:px-6 py-1 md:py-4">
                                            @if ($bkp['disk'] === 'local')
                                                <span
                                                    class="px-2.5 py-1 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 text-[10px] font-bold rounded uppercase tracking-wider flex items-center inline-flex w-max">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                                        </path>
                                                    </svg>
                                                    Servidor Local
                                                </span>
                                            @else
                                                <span
                                                    class="px-2.5 py-1 bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 text-[10px] font-bold rounded uppercase tracking-wider flex items-center inline-flex w-max shadow-sm border border-orange-200 dark:border-orange-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z">
                                                        </path>
                                                    </svg>
                                                    Cloudflare R2 (Nuvem)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-2 md:px-6 py-1 md:py-4 text-gray-600 dark:text-gray-400">
                                            <span class="md:hidden text-xs font-semibold uppercase mr-2">Tamanho:</span>
                                            <strong
                                                class="text-gray-800 dark:text-gray-200">{{ $bkp['size'] }}</strong> MB
                                        </td>
                                        <td class="px-2 md:px-6 py-1 md:py-4 text-gray-600 dark:text-gray-400">
                                            <span class="md:hidden text-xs font-semibold uppercase mr-2">Data:</span>
                                            {{ $bkp['date']->format('d/m/Y \à\s H:i') }}
                                        </td>
                                        <td
                                            class="px-2 md:px-6 py-3 md:py-4 md:text-center mt-2 md:mt-0 border-t border-gray-100 md:border-none flex items-center justify-end gap-2">
                                            <a href="{{ route('backups.download', ['disk' => $bkp['disk'], 'path' => $bkp['path']]) }}"
                                                class="p-2 text-dbv-blue bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 rounded-lg transition-colors"
                                                title="Baixar Backup">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                    </path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('backups.destroy') }}" method="POST"
                                                class="inline-block"
                                                onsubmit="return confirm('Deseja excluir permanentemente este arquivo de backup do disco {{ strtoupper($bkp['disk']) }}? Isso não pode ser desfeito.');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="disk" value="{{ $bkp['disk'] }}">
                                                <input type="hidden" name="path" value="{{ $bkp['path'] }}">
                                                <button type="submit"
                                                    class="p-2 text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 rounded-lg transition-colors"
                                                    title="Apagar Arquivo">
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

        {{-- OVERLAY DE CARREGAMENTO --}}
        <div x-show="isBackingUp" x-transition.opacity.duration.300ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/80 backdrop-blur-sm" x-cloak>
            <div
                class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-2xl flex flex-col items-center max-w-md text-center border border-gray-100 dark:border-gray-700 m-4">
                <div class="relative w-20 h-20 mb-6">
                    <svg class="animate-spin w-full h-full text-dbv-blue/20" viewBox="0 0 100 100" fill="none">
                        <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="8">
                        </circle>
                    </svg>
                    <svg class="animate-spin w-full h-full text-dbv-blue absolute top-0 left-0" viewBox="0 0 100 100"
                        fill="none" style="animation-duration: 2s;">
                        <path d="M50 5a45 45 0 0 1 45 45" stroke="currentColor" stroke-width="8"
                            stroke-linecap="round"></path>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center text-dbv-blue">
                        <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                            </path>
                        </svg>
                    </div>
                </div>

                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Criando Backup...</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Estamos processando o seu Banco de Dados e enviando para a nuvem. Isso pode demorar um pouquinho.
                </p>
                <div
                    class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 p-4 rounded-xl w-full flex items-start gap-3 text-left">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <p class="text-xs text-red-800 dark:text-red-400 font-bold">
                        Não feche nem recarregue esta página! Ela voltará ao normal automaticamente.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
