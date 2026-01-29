<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Especialidades: {{ $desbravador->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Adicionar Nova</h3>

                    <form action="{{ route('desbravadores.salvar-especialidades', $desbravador->id) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data de Conclusão</label>
                            <input type="date" name="data_conclusao" value="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-gray-300" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selecione as Especialidades</label>
                            <div class="h-64 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-2 space-y-2">
                                @foreach($especialidades as $esp)
                                @php
                                $jaTem = $desbravador->especialidades->contains($esp->id);
                                @endphp
                                <label class="flex items-center space-x-2 p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer {{ $jaTem ? 'opacity-50' : '' }}">
                                    <input type="checkbox" name="especialidades[]" value="{{ $esp->id }}" {{ $jaTem ? 'disabled' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $esp->nome }}
                                        @if($jaTem) <span class="text-xs text-green-600 font-bold">(Já possui)</span> @endif
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Adicionar Selecionadas
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Especialidades Conquistadas ({{ $desbravador->especialidades->count() }})</h3>

                    @if($desbravador->especialidades->isEmpty())
                    <p class="text-gray-500 italic">Nenhuma especialidade cadastrada ainda.</p>
                    @else
                    <div class="space-y-3">
                        @foreach($desbravador->especialidades as $esp)
                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded border-l-4" style="border-color: {{ $esp->cor_fundo }}">
                            <div>
                                <span class="font-bold text-gray-800 dark:text-white block">{{ $esp->nome }}</span>
                                <span class="text-xs text-gray-500">Concluído em: {{ \Carbon\Carbon::parse($esp->pivot->data_conclusao)->format('d/m/Y') }}</span>
                            </div>

                            <form action="{{ route('desbravadores.remover-especialidade', ['desbravador' => $desbravador->id, 'especialidade' => $esp->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta especialidade?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Remover">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>

            <div class="text-center">
                <a href="{{ route('desbravadores.show', $desbravador->id) }}" class="text-gray-500 hover:underline">Voltar ao Perfil</a>
            </div>

        </div>
    </div>
</x-app-layout>