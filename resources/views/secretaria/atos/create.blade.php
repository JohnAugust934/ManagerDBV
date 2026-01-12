<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Emitir Ato Administrativo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('atos.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-bold mb-2">Data do Ato</label>
                                <input type="date" name="data" required value="{{ date('Y-m-d') }}"
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Tipo de Ato</label>
                                <select name="tipo" required class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                                    <option value="Nomeação">Nomeação (Cargos)</option>
                                    <option value="Exoneração">Exoneração (Saída de Cargo)</option>
                                    <option value="Admissão">Admissão/Investidura</option>
                                    <option value="Transferência">Transferência</option>
                                    <option value="Disciplina">Disciplina</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2">Descrição Resumida</label>
                            <input type="text" name="descricao_resumida" required placeholder="Ex: Nomeação para Capitão da Unidade Águias"
                                class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2">Desbravador Envolvido (Opcional)</label>
                            <select name="desbravador_id" class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
                                <option value="">-- Não se aplica / Geral --</option>
                                @foreach($desbravadores as $dbv)
                                <option value="{{ $dbv->id }}">{{ $dbv->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold mb-2">Texto Oficial (Opcional)</label>
                            <textarea name="texto_completo" rows="4" placeholder="Detalhes adicionais..."
                                class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                                Registrar Ato
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>