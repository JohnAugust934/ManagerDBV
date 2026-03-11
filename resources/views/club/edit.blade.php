<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configurações do Clube') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-1">
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Identidade Visual</h3>

                        <div class="flex flex-col items-center text-center">
                            {{-- BLINDAGEM: Verifica se $club existe antes de acessar o logo --}}
                            @if ($club && $club->logo)
                                <div class="mb-6 relative group w-full flex justify-center">
                                    <img src="{{ asset('storage/' . $club->logo) }}" alt="Brasão do Clube"
                                        class="h-48 w-auto object-contain rounded-lg p-2 border-2 border-gray-100 dark:border-gray-700 shadow-sm">
                                </div>

                                <form method="POST" action="{{ route('club.remove_logo') }}" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Tem certeza que deseja remover o brasão do clube?')"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 rounded-lg transition-colors font-semibold text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                        Remover Brasão
                                    </button>
                                </form>
                            @else
                                <div
                                    class="w-full h-48 bg-gray-50 dark:bg-gray-700/50 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center mb-4 gap-3">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Nenhum brasão
                                        enviado</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl p-6 md:p-8 border border-gray-100 dark:border-gray-700">
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Dados Cadastrais</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Atualize as informações oficiais do
                                seu clube para que saiam corretamente nos relatórios, atas e documentos.</p>
                        </div>

                        <form method="POST" action="{{ route('club.update') }}" enctype="multipart/form-data"
                            class="space-y-6">
                            @csrf
                            @method('PATCH')

                            <div>
                                <x-input-label for="nome" :value="__('Nome do Clube')" />
                                {{-- BLINDAGEM: O uso do operador ?-> evita erro se o clube for null --}}
                                <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full"
                                    :value="old('nome', $club?->nome)" placeholder="Ex: Pioneiros da Colina" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('nome')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="cidade" :value="__('Cidade e Estado')" />
                                    <x-text-input id="cidade" name="cidade" type="text" class="mt-1 block w-full"
                                        :value="old('cidade', $club?->cidade)" placeholder="Ex: Araraquara - SP" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('cidade')" />
                                </div>

                                <div>
                                    <x-input-label for="associacao" :value="__('Associação/Missão')" />
                                    <x-text-input id="associacao" name="associacao" type="text"
                                        class="mt-1 block w-full" :value="old('associacao', $club?->associacao)" placeholder="Ex: APaC" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('associacao')" />
                                </div>
                            </div>

                            <div
                                class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-200 dark:border-gray-600 mt-2">
                                <x-input-label for="logo" :value="__('Enviar/Atualizar Brasão')" class="mb-2" />
                                <input id="logo" name="logo" type="file"
                                    accept="image/png, image/jpeg, image/jpg"
                                    class="block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400 transition-colors cursor-pointer">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Formatos recomendados: PNG
                                    (fundo transparente) ou JPG. Tamanho máximo: 2MB.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                            </div>

                            <div class="flex items-center gap-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-red-500/30">
                                    {{ __('Salvar Informações') }}
                                </button>

                                @if (session('success'))
                                    <p x-data="{ show: true }" x-show="show" x-transition.opacity.duration.500ms
                                        x-init="setTimeout(() => show = false, 3000)"
                                        class="text-sm font-bold text-green-600 dark:text-green-400 flex items-center gap-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ session('success') }}
                                    </p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
