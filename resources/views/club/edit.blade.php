<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Perfil do Clube') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Informações do Clube') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Atualize o brasão e os dados principais do seu clube.
                            </p>
                        </header>

                        @if (session('success'))
                        <div class="mt-4 p-4 font-medium text-sm text-green-600 bg-green-100 rounded-lg border border-green-200">
                            {{ session('success') }}
                        </div>
                        @endif

                        <div class="mt-6 space-y-6">

                            <div>
                                <x-input-label for="logo" :value="__('Brasão do Clube')" />

                                <div class="mt-2 flex items-start gap-6">
                                    <div class="shrink-0 relative group">
                                        @if($club->logo)
                                        <img class="h-24 w-24 rounded-full object-cover border-4 border-gray-100 shadow-sm"
                                            src="{{ asset('storage/' . $club->logo) }}"
                                            alt="Logo atual" />
                                        @else
                                        <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center border-4 border-white shadow-sm text-gray-400">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <form id="update-club-form" method="post" action="{{ route('club.update') }}" enctype="multipart/form-data">
                                            @csrf
                                            @method('patch')

                                            <input type="file" name="logo" id="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
                                            <p class="mt-1 text-xs text-gray-500">PNG, JPG ou GIF (Max. 2MB)</p>
                                        </form>

                                        @if($club->logo)
                                        <form method="POST" action="{{ route('club.logo.destroy') }}" class="mt-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-900 font-medium flex items-center gap-1 transition-colors" onclick="return confirm('Tem certeza que deseja remover o brasão?');">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Remover Brasão Atual
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="nome" :value="__('Nome do Clube')" />
                                    <input form="update-club-form" id="nome" name="nome" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('nome', $club->nome) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('nome')" />
                                </div>

                                <div>
                                    <x-input-label for="cidade" :value="__('Cidade')" />
                                    <input form="update-club-form" id="cidade" name="cidade" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('cidade', $club->cidade) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('cidade')" />
                                </div>

                                <div>
                                    <x-input-label for="associacao" :value="__('Associação / Campo')" />
                                    <input form="update-club-form" id="associacao" name="associacao" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('associacao', $club->associacao) }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('associacao')" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <x-primary-button form="update-club-form">{{ __('Salvar Alterações') }}</x-primary-button>
                            </div>
                        </div>

                    </section>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>