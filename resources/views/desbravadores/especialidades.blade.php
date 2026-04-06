<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
            Especialidades: {{ $desbravador->nome }}
        </h2>
    </x-slot>

    <div class="ui-page">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="ui-card p-6">
                <h3 class="ui-title text-lg mb-4">Adicionar Nova</h3>

                <form action="{{ route('desbravadores.salvar-especialidades', $desbravador->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="data_conclusao" value="Data de Conclusao" />
                        <x-text-input id="data_conclusao" type="date" name="data_conclusao" :value="date('Y-m-d')" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label value="Selecione as Especialidades" />
                        <div class="h-72 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 p-2 space-y-2">
                            @foreach($especialidades as $esp)
                                @php $jaTem = $desbravador->especialidades->contains($esp->id); @endphp
                                <label class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer {{ $jaTem? 'opacity-50' : '' }}">
                                    <input type="checkbox" name="especialidades[]" value="{{ $esp->id }}" {{ $jaTem? 'disabled' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $esp->nome }}
                                        @if($jaTem)
                                            <span class="text-xs text-green-600 font-bold">(Ja possui)</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="ui-btn-primary">Adicionar Selecionadas</button>
                    </div>
                </form>
            </div>

            <div class="ui-card p-6">
                <h3 class="ui-title text-lg mb-4">Especialidades Conquistadas ({{ $desbravador->especialidades->count() }})</h3>

                @if($desbravador->especialidades->isEmpty())
                    <x-empty-state
                        title="Nenhuma especialidade cadastrada"
                        description="Selecione especialidades no painel ao lado para iniciar o historico deste desbravador." />
                @else
                    <div class="space-y-3">
                        @foreach($desbravador->especialidades as $esp)
                            <div class="flex justify-between items-center p-3 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/70" style="border-left: 4px solid {{ $esp->cor_fundo }}">
                                <div>
                                    <span class="font-bold text-gray-800 dark:text-white block">{{ $esp->nome }}</span>
                                    <span class="text-xs text-gray-500">Concluido em: {{ \Carbon\Carbon::parse($esp->pivot->data_conclusao)->format('d/m/Y') }}</span>
                                </div>

                                <form action="{{ route('desbravadores.remover-especialidade', ['desbravador' => $desbravador->id, 'especialidade' => $esp->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta especialidade?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Remover">Remover</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('desbravadores.show', $desbravador->id) }}" class="ui-btn-secondary">Voltar ao Perfil</a>
        </div>
    </div>
</x-app-layout>
