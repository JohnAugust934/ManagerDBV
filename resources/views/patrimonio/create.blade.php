<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Cadastrar Item no Patrimônio
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('patrimonio.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2">Descrição do Item</label>
                            <input type="text" name="item" required placeholder="Ex: Barraca Iglu 4 Pessoas - Marca X"
                                class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-bold mb-2">Quantidade</label>
                                <input type="number" name="quantidade" value="1" min="1" required
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Valor Estimado (Unit.)</label>
                                <input type="number" name="valor_estimado" step="0.01" placeholder="0.00"
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Data Aquisição</label>
                                <input type="date" name="data_aquisicao"
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-bold mb-2">Estado de Conservação</label>
                                <select name="estado_conservacao" required class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                                    <option value="Novo">Novo</option>
                                    <option value="Bom">Bom</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Ruim">Ruim</option>
                                    <option value="Inservível">Inservível (Lixo/Sucata)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Local de Armazenamento</label>
                                <input type="text" name="local_armazenamento" placeholder="Ex: Armário da Secretaria"
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2">Observações</label>
                            <textarea name="observacoes" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition">
                                Salvar Patrimônio
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>