<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-dbv-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ __('Sobre o Sistema') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 shadow-sm sm:rounded-2xl border border-gray-100 dark:border-slate-700 overflow-hidden">

                <div class="p-6 sm:p-10">
                    <div
                        class="flex flex-col items-center justify-center text-center border-b border-gray-100 dark:border-slate-700 pb-8 mb-8">
                        <div
                            class="w-20 h-20 bg-dbv-blue rounded-2xl flex items-center justify-center shadow-lg mb-4 transform rotate-3">
                            <svg class="w-12 h-12 text-dbv-yellow transform -rotate-3" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-2 tracking-tight">ManagerDBV
                        </h1>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Sistema de Gestao para Clubes de
                            Desbravadores</p>

                        <div
                            class="inline-flex items-center px-4 py-2 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-mono text-sm border border-blue-100 dark:border-blue-800 shadow-sm">
                            <span class="font-bold mr-2">Versão Atual:</span>
                            v1.3.1-beta
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        <div
                            class="bg-gray-50 dark:bg-slate-700/30 p-6 rounded-2xl border border-gray-200 dark:border-slate-600 flex flex-col h-full">
                            <div
                                class="w-12 h-12 bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center mb-4 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Suporte & Sugestões</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 leading-relaxed">
                                Ocorreu algum erro estranho durante o uso? Tem uma ideia legal de nova funcionalidade
                                que ajudaria o seu clube?
                            </p>
                            <div
                                class="bg-white dark:bg-slate-800 p-4 rounded-lg border border-gray-100 dark:border-slate-700 mb-6 flex-1 shadow-sm">
                                <p
                                    class="text-xs font-bold text-dbv-blue dark:text-blue-400 mb-1 uppercase tracking-wider">
                                    ⚠ï¸ Importante para Chamados:</p>
                                <p class="text-gray-600 dark:text-gray-400 text-xs leading-relaxed">
                                    Na descrição, detalhe o máximo que puder. Informe em <strong>qual tela
                                        estava</strong>, <strong>qual botão clicou</strong> e o <strong>que
                                        aconteceu</strong>. Isso nos ajuda a resolver muito mais rápido!
                                </p>
                            </div>
                            <a href="https://ticket.on3digital.com.br/pt/submit" target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex w-full items-center justify-center px-4 py-3 bg-dbv-yellow hover:bg-yellow-500 text-yellow-900 font-bold rounded-xl transition-colors shadow-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Abrir um Chamado
                            </a>
                        </div>

                        <div
                            class="bg-gray-50 dark:bg-slate-700/30 p-6 rounded-2xl border border-gray-200 dark:border-slate-600 flex flex-col h-full">
                            <div
                                class="w-12 h-12 bg-gray-200 dark:bg-slate-600 text-gray-700 dark:text-gray-200 rounded-xl flex items-center justify-center mb-4 shadow-sm">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Projeto & Código</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 leading-relaxed flex-1">
                                O ManagerDBV é um sistema pensado para a comunidade. Se você quiser ver como ele
                                funciona por baixo dos panos, conferir o histórico de atualizações ou até mesmo
                                colaborar, o projeto está disponível no GitHub!
                            </p>
                            <a href="https://github.com/JohnAugust934/ManagerDBV" target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex w-full items-center justify-center px-4 py-3 bg-gray-900 hover:bg-gray-800 text-white dark:bg-slate-600 dark:hover:bg-slate-500 font-bold rounded-xl transition-colors shadow-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Visitar Repositório
                            </a>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gray-50 dark:bg-slate-800/80 px-6 py-4 border-t border-gray-100 dark:border-slate-700 text-center">
                    <p
                        class="text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center justify-center gap-1">
                        Feito com <svg class="w-4 h-4 text-red-500 fill-current animate-pulse" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                clip-rule="evenodd" />
                        </svg> para facilitar o dia a dia dos Clubes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


