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

    <div class="py-8" x-data="{
        isBackingUp: false,
        isImporting: false,
        isRestoring: false,
    
        {{-- Estados para o modal de restauração --}}
        showRestoreConfirm: false,
        restoreDisk: '',
        restorePath: '',
        prepareRestore(disk, path) {
            this.restoreDisk = disk;
            this.restorePath = path;
            this.showRestoreConfirm = true;
        },
    
        {{-- Estados para o modal de exclusão --}}
        showDeleteConfirm: false,
        deleteDisk: '',
        deletePath: '',
        deleteName: '',
        prepareDelete(disk, path, name) {
            this.deleteDisk = disk;
            this.deletePath = path;
            this.deleteName = name;
            this.showDeleteConfirm = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- PAINEL DE CONTROLE --}}
            <div
                class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 mb-6">
                <div
                    class="p-6 bg-red-50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-900/30 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-red-800 dark:text-red-400">Painel de Segurança e Nuvem</h3>
                        <p class="text-sm text-red-600 dark:text-red-300 mt-1">
                            Gere uma cópia de segurança, importe de outra máquina ou restaure o sistema inteiro com um
                            clique.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <form id="importForm" action="{{ route('backups.import') }}" method="POST"
                            enctype="multipart/form-data" class="hidden">
                            @csrf
                            <input type="file" id="backupFile" name="backup_file" accept=".zip"
                                @change="isImporting = true; $el.form.submit();">
                        </form>

                        <button onclick="document.getElementById('backupFile').click();" type="button"
                            :disabled="isImporting || isBackingUp || isRestoring"
                            class="bg-white hover:bg-gray-50 text-gray-700 font-bold py-3 px-6 rounded-lg border border-gray-200 shadow-sm transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                            <span x-show="!isImporting" class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Importar do PC
                            </span>
                            <span x-show="isImporting" x-cloak>Enviando...</span>
                        </button>

                        <form action="{{ route('backups.store') }}" method="POST" @submit="isBackingUp = true"
                            class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" :disabled="isBackingUp || isImporting || isRestoring"
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg shadow-red-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                <span x-show="!isBackingUp" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                        </path>
                                    </svg>
                                    Gerar Novo Backup
                                </span>
                                <span x-show="isBackingUp" x-cloak>Processando...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- LISTA DE BACKUPS --}}
            <div
                class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Arquivos Disponíveis</h3>
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
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Nenhum backup encontrado no servidor ou
                            nuvem.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300 hidden md:table-header-group">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold">Arquivo</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Local</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Tamanho</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Data</th>
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
                                                    class="px-2.5 py-1 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 text-[10px] font-bold rounded uppercase flex items-center inline-flex w-max">Local</span>
                                            @else
                                                <span
                                                    class="px-2.5 py-1 bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 text-[10px] font-bold rounded uppercase flex items-center inline-flex w-max">Nuvem
                                                    (R2)</span>
                                            @endif
                                        </td>
                                        <td class="px-2 md:px-6 py-1 md:py-4 text-gray-600 dark:text-gray-400">
                                            <span class="md:hidden text-xs font-semibold uppercase mr-2">Tam:</span>
                                            <strong
                                                class="text-gray-800 dark:text-gray-200">{{ $bkp['size'] }}</strong> MB
                                        </td>
                                        <td class="px-2 md:px-6 py-1 md:py-4 text-gray-600 dark:text-gray-400">
                                            <span class="md:hidden text-xs font-semibold uppercase mr-2">Data:</span>
                                            {{ $bkp['date']->format('d/m/Y H:i') }}
                                        </td>
                                        <td
                                            class="px-2 md:px-6 py-3 md:py-4 md:text-center flex flex-wrap items-center justify-end gap-2">

                                            {{-- BOTÃO DE RESTAURAR --}}
                                            <button type="button"
                                                @click="prepareRestore('{{ $bkp['disk'] }}', '{{ $bkp['path'] }}')"
                                                class="text-xs px-3 py-1.5 bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 rounded transition-colors font-bold flex items-center gap-1 disabled:opacity-50"
                                                :disabled="isImporting || isBackingUp || isRestoring">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                    </path>
                                                </svg>
                                                Restaurar
                                            </button>

                                            {{-- BOTÃO DE BAIXAR --}}
                                            <a href="{{ route('backups.download', ['disk' => $bkp['disk'], 'path' => $bkp['path']]) }}"
                                                target="_blank" download="{{ $bkp['name'] }}"
                                                class="p-1.5 text-dbv-blue bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 rounded transition-colors disabled:opacity-50"
                                                title="Baixar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                    </path>
                                                </svg>
                                            </a>

                                            {{-- BOTÃO DE EXCLUIR --}}
                                            <button type="button"
                                                @click="prepareDelete('{{ $bkp['disk'] }}', '{{ $bkp['path'] }}', '{{ $bkp['name'] }}')"
                                                class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 rounded transition-colors disabled:opacity-50"
                                                title="Apagar" :disabled="isImporting || isBackingUp || isRestoring">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- MODAL DE CONFIRMAÇÃO DE RESTAURAÇÃO        --}}
        {{-- ========================================== --}}
        <div x-show="showRestoreConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm" @click="showRestoreConfirm = false"></div>

            <div
                class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-xl p-8 transform transition-all animate-fade-in-up">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-6 text-red-600">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">⚠️ Atenção! Ação Crítica</h3>
                    <p class="text-base text-gray-600 dark:text-gray-400 mb-8 max-w-md">
                        Esta ação apagará <strong class="text-gray-800 dark:text-white">TODO</strong> o banco de dados
                        atual e substituirá por este backup. Você será desconectado se a sua senha na época for
                        diferente. Deseja prosseguir?
                    </p>
                </div>

                <form action="{{ route('backups.restore') }}" method="POST"
                    @submit="isRestoring = true; showRestoreConfirm = false;">
                    @csrf
                    <input type="hidden" name="disk" :value="restoreDisk">
                    <input type="hidden" name="path" :value="restorePath">

                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" @click="showRestoreConfirm = false"
                            class="bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-4 px-8 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm transition-all flex items-center justify-center">
                            Não, Cancelar
                        </button>
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg shadow-red-500/30 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Sim, Restaurar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ========================================== --}}

        {{-- ========================================== --}}
        {{-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO           --}}
        {{-- ========================================== --}}
        <div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm" @click="showDeleteConfirm = false"></div>

            <div
                class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg p-8 transform transition-all animate-fade-in-up">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-6 text-red-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Excluir Arquivo?</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Você está prestes a excluir permanentemente o backup:
                    </p>
                    <div
                        class="w-full bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 mb-6 border border-gray-100 dark:border-gray-600">
                        <strong class="text-gray-900 dark:text-gray-200 break-all text-sm"
                            x-text="deleteName"></strong>
                    </div>
                    <div
                        class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 p-3 rounded-xl w-full mb-8">
                        <p class="text-xs text-red-800 dark:text-red-400 font-semibold">Esta ação não poderá ser
                            desfeita e o arquivo será apagado do servidor.</p>
                    </div>
                </div>

                <form action="{{ route('backups.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="disk" :value="deleteDisk">
                    <input type="hidden" name="path" :value="deletePath">

                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" @click="showDeleteConfirm = false"
                            class="bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm transition-all flex items-center justify-center">
                            Cancelar
                        </button>
                        <button type="submit" @click="showDeleteConfirm = false"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-red-500/30 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Sim, Excluir
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ========================================== --}}

        {{-- ========================================== --}}
        {{-- OVERLAY DE CARREGAMENTO GLOBAL CORRIGIDO   --}}
        {{-- ========================================== --}}
        <div x-show="isBackingUp || isImporting || isRestoring" x-transition.opacity.duration.300ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/80 backdrop-blur-sm" x-cloak>
            <div
                class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-2xl flex flex-col items-center max-w-md text-center m-4">

                {{-- Novo Ícone de Carregamento (Tailwind Padrão Seguro) --}}
                <div class="flex justify-center items-center mb-6">
                    <svg class="animate-spin h-16 w-16 text-blue-600 dark:text-blue-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>

                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2"
                    x-text="isRestoring ? 'Restaurando Sistema...' : 'Processando Arquivos...'"></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6"
                    x-text="isRestoring ? 'Apagando banco atual e injetando dados do backup. Isso vai demorar um pouco.' : 'Aguarde o envio ou a geração dos arquivos em segurança.'">
                </p>

                <div
                    class="bg-red-50 dark:bg-red-900/20 border border-red-100 p-4 rounded-xl w-full flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <p class="text-xs text-red-800 dark:text-red-400 font-bold text-left">Não feche nem recarregue a
                        página!</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
