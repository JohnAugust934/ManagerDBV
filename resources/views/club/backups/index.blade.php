<x-app-layout>
    <div class="ui-page" x-data="{
        isBackingUp: false,
        isRestoring: false,

        showRestoreConfirm: false,
        restoreDisk: '',
        restorePath: '',
        prepareRestore(disk, path) {
            this.restoreDisk = disk;
            this.restorePath = path;
            this.showRestoreConfirm = true;
        },

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

        <x-page-title title="Backups do Clube — {{ $club->nome }}" />

        {{-- AVISO DE ESCOPO --}}
        <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 mb-6 flex gap-3 items-start">
            <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Backup isolado por clube</p>
                <p class="text-sm text-blue-700 dark:text-blue-400 mt-0.5">
                    Estes backups contêm <strong>somente os dados do clube {{ $club->nome }}</strong> — nenhum dado de
                    outro clube é incluído. A restauração substitui apenas os dados deste clube.
                </p>
            </div>
        </div>

        {{-- PAINEL DE CONTROLE --}}
        <div class="ui-card overflow-hidden mb-6">
            <div class="p-6 bg-amber-50 dark:bg-amber-900/10 border-b border-amber-100 dark:border-amber-900/30 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-amber-800 dark:text-amber-400">Segurança do Clube</h3>
                    <p class="text-sm text-amber-600 dark:text-amber-300 mt-1">
                        Gere um backup completo dos seus dados (desbravadores, financeiro, eventos, frequência etc.)
                        ou restaure a partir de um backup anterior.
                    </p>
                </div>

                <form action="{{ route('club-backups.store') }}" method="POST" @submit="isBackingUp = true"
                    class="w-full lg:w-auto">
                    @csrf
                    <button type="submit" :disabled="isBackingUp || isRestoring"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg shadow-amber-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <span x-show="!isBackingUp" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Gerar Backup Agora
                        </span>
                        <span x-show="isBackingUp" x-cloak>Processando...</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- FEEDBACKS --}}
        @if (session('success'))
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 mb-4 text-green-800 dark:text-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 mb-4 text-red-800 dark:text-red-300 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- LISTA DE BACKUPS --}}
        <div class="ui-card overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Backups Disponíveis</h3>
                <span class="text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 px-3 py-1 rounded-full">
                    {{ $backups->total() }} encontrados
                </span>
            </div>

            @if ($backups->isEmpty())
                <div class="p-6">
                    <x-empty-state
                        title="Nenhum backup encontrado"
                        description="Clique em 'Gerar Backup Agora' para criar o primeiro backup deste clube." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Arquivo</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Local</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tamanho</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Data</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($backups as $backup)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-xs text-slate-700 dark:text-slate-300 break-all">
                                            {{ $backup['name'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full
                                            {{ $backup['disk'] === 'r2'
                                                ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
                                                : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                                            {{ strtoupper($backup['disk']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600 dark:text-slate-400 text-xs">
                                        {{ $backup['size'] }} MB
                                    </td>
                                    <td class="px-4 py-4 text-slate-600 dark:text-slate-400 text-xs">
                                        {{ $backup['date']->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Download --}}
                                            <a href="{{ route('club-backups.download', ['disk' => $backup['disk'], 'path' => $backup['path']]) }}"
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                                Baixar
                                            </a>

                                            {{-- Restaurar --}}
                                            <button type="button"
                                                @click="prepareRestore('{{ $backup['disk'] }}', '{{ $backup['path'] }}')"
                                                :disabled="isRestoring"
                                                class="text-xs text-amber-600 dark:text-amber-400 hover:underline font-medium disabled:opacity-50">
                                                Restaurar
                                            </button>

                                            {{-- Excluir --}}
                                            <button type="button"
                                                @click="prepareDelete('{{ $backup['disk'] }}', '{{ $backup['path'] }}', '{{ $backup['name'] }}')"
                                                class="text-xs text-red-500 dark:text-red-400 hover:underline font-medium">
                                                Excluir
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($backups->hasPages())
                    <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                        {{ $backups->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- MODAL: Confirmar Restauração --}}
        <div x-show="showRestoreConfirm" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6"
                @click.outside="showRestoreConfirm = false">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Confirmar Restauração</h3>
                </div>

                <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
                    Esta ação irá <strong class="text-amber-700 dark:text-amber-400">substituir todos os dados atuais</strong>
                    do clube <strong>{{ $club->nome }}</strong> pelos dados deste backup.
                </p>
                <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1 mb-5 list-disc list-inside">
                    <li>Desbravadores, financeiro, frequências, eventos e patrimônio serão substituídos</li>
                    <li>Os usuários do clube serão recriados — você precisará fazer login novamente</li>
                    <li>Dados de outros clubes <strong>não serão afetados</strong></li>
                    <li>Esta operação é irreversível</li>
                </ul>

                <form :action="'{{ route('club-backups.restore') }}'" method="POST"
                    @submit="isRestoring = true; showRestoreConfirm = false">
                    @csrf
                    <input type="hidden" name="disk" :value="restoreDisk">
                    <input type="hidden" name="path" :value="restorePath">
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="showRestoreConfirm = false"
                            class="ui-btn-secondary text-sm">Cancelar</button>
                        <button type="submit" :disabled="isRestoring"
                            class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-5 rounded-lg text-sm transition-all disabled:opacity-50">
                            Sim, Restaurar Este Backup
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL: Confirmar Exclusão --}}
        <div x-show="showDeleteConfirm" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6"
                @click.outside="showDeleteConfirm = false">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Excluir Backup</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-5">
                    Excluir permanentemente <span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-1 rounded" x-text="deleteName"></span>?
                </p>

                <form :action="'{{ route('club-backups.destroy') }}'" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="disk" :value="deleteDisk">
                    <input type="hidden" name="path" :value="deletePath">
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="showDeleteConfirm = false"
                            class="ui-btn-secondary text-sm">Cancelar</button>
                        <button type="submit" class="ui-btn-danger text-sm">Excluir</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- OVERLAY de carregamento --}}
        <div x-show="isRestoring" x-cloak
            class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex flex-col items-center justify-center gap-4">
            <svg class="animate-spin w-12 h-12 text-amber-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-white font-semibold text-lg">Restaurando dados do clube...</p>
            <p class="text-slate-300 text-sm">Não feche esta janela. Isso pode levar alguns minutos.</p>
        </div>

    </div>
</x-app-layout>
