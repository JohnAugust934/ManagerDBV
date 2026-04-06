<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Configurações do Clube
        </h2>
    </x-slot>

    <div class="ui-page">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-1">
                <div class="ui-card p-6">
                    <h3 class="ui-title text-lg mb-4">Identidade Visual</h3>

                    <div class="flex flex-col items-center text-center gap-4">
                        @if ($club && $club->logo)
                            <img src="{{ asset('storage/' . $club->logo) }}" alt="Brasao do Clube"
                                class="h-48 w-auto object-contain rounded-xl p-2 border border-gray-100 dark:border-gray-700">

                            <form method="POST" action="{{ route('club.remove_logo') }}" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Tem certeza que deseja remover o brasao do clube?')"
                                    class="ui-btn-danger w-full">
                                    Remover Brasao
                                </button>
                            </form>
                        @else
                            <div class="w-full h-48 ui-card-muted border-dashed flex flex-col items-center justify-center gap-2">
                                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Nenhum brasao enviado</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <div class="ui-card p-6 md:p-8">
                    <h3 class="ui-title text-xl">Dados Cadastrais</h3>
                    <p class="ui-subtitle mt-1 mb-6">Atualize as informações oficiais do clube para relatorios e documentos.</p>

                    <form method="POST" action="{{ route('club.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="nome" :value="__('Nome do Clube')" />
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
                                <x-input-label for="associacao" :value="__('Associacao/Missao')" />
                                <x-text-input id="associacao" name="associacao" type="text" class="mt-1 block w-full"
                                    :value="old('associacao', $club?->associacao)" placeholder="Ex: APaC" required />
                                <x-input-error class="mt-2" :messages="$errors->get('associacao')" />
                            </div>
                        </div>

                        <div class="ui-card-muted p-4">
                            <x-input-label for="logo" :value="__('Enviar/Atualizar Brasao')" class="mb-2" />
                            <input id="logo" name="logo" type="file" accept="image/png, image/jpeg, image/jpg"
                                class="ui-input cursor-pointer">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Formatos: PNG ou JPG. Tamanho maximo: 2MB.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="submit" class="ui-btn-primary">Salvar Informações</button>

                            @if (session('success'))
                                <p x-data="{ show: true }" x-show="show" x-transition.opacity.duration.500ms
                                    x-init="setTimeout(() => show = false, 3000)"
                                    class="text-sm font-semibold text-green-600 dark:text-green-400">
                                    {{ session('success') }}
                                </p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

