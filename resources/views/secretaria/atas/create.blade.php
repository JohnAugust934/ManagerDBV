<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Redigir Nova Ata
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('atas.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-bold mb-2">Data da Reunião</label>
                                <input type="date" name="data_reuniao" required value="{{ date('Y-m-d') }}"
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Tipo de Reunião</label>
                                <select name="tipo" required class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                                    <option value="Regular">Regular (Domingo)</option>
                                    <option value="Diretoria">Diretoria</option>
                                    <option value="Campori">Campori / Evento</option>
                                    <option value="Extraordinária">Extraordinária</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2">Secretário Responsável</label>
                            <input type="text" name="secretario_responsavel" value="{{ Auth::user()->name }}"
                                class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2">Participantes (Resumo)</label>
                            <input type="text" name="participantes" placeholder="Ex: Toda a diretoria e conselheiros"
                                class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold mb-2">Conteúdo da Ata</label>
                            <textarea name="conteudo" rows="10" required placeholder="Descreva tudo o que foi tratado..."
                                class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                                Salvar Ata
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>