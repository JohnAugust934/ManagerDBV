<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-200 leading-tight">
            {{ __('Gerenciar Colunas da Chamada') }}
        </h2>
    </x-slot>

    <div class="ui-page space-y-6">
        @if (!empty($legacyMode) && $legacyMode)
            <div class="ui-card p-6 border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700">
                <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                    Atualizacao pendente: execute `php artisan migrate` para habilitar o cadastro e edicao de colunas personalizadas.
                </p>
            </div>
        @endif

        <div class="ui-card p-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Configure os campos exibidos na Nova Chamada. O nome e mostrado em MAIUSCULO com a pontuacao.
            </p>
        </div>

        <div class="ui-card p-6" x-data="{ rows: [{ name: '', points: 1 }] }">
            <form action="{{ route('frequencia.columns.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">Colunas existentes</h3>
                    <div class="space-y-3">
                        @foreach ($columns as $column)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-6">
                                    <x-input-label :for="'column_name_'.$column->id" :value="$column->is_fixed ? __('Nome (Coluna Fixa)') : __('Nome')" />
                                    <input id="column_name_{{ $column->id }}" type="text"
                                        name="columns[{{ $column->id }}][name]" value="{{ old('columns.'.$column->id.'.name', $column->name) }}"
                                        class="ui-input mt-1" required maxlength="60">
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label :for="'column_points_'.$column->id" :value="__('Pontos (1 a 10)')" />
                                    <input id="column_points_{{ $column->id }}" type="number"
                                        name="columns[{{ $column->id }}][points]" value="{{ old('columns.'.$column->id.'.points', $column->points) }}"
                                        class="ui-input mt-1" min="1" max="10" required>
                                </div>
                                <div class="md:col-span-2">
                                    <span
                                        class="inline-flex px-3 py-2 rounded-lg text-xs font-semibold {{ $column->is_fixed ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                        {{ $column->is_fixed ? 'Fixa' : 'Adicional' }}
                                    </span>
                                </div>
                                <div class="md:col-span-2">
                                    @if (empty($legacyMode) && !empty($column->can_delete) && $column->can_delete)
                                        <button type="submit" form="delete-column-{{ $column->id }}"
                                            onclick="return confirm('Deseja remover esta coluna?');"
                                            class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40 transition-colors">
                                            Remover
                                        </button>
                                    @elseif (!$column->is_fixed)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Ja utilizada
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">Novas colunas</h3>
                        <button type="button"
                            class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold bg-dbv-blue text-white hover:bg-blue-800 transition-colors disabled:opacity-60"
                            @click="rows.push({ name: '', points: 1 })"
                            {{ (!empty($legacyMode) && $legacyMode) ? 'disabled' : '' }}>
                            Adicionar coluna
                        </button>
                    </div>

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <div class="md:col-span-7">
                                <x-input-label :value="__('Nome da nova coluna')" />
                                <input type="text" class="ui-input mt-1"
                                    x-bind:name="'new_columns[' + index + '][name]'" x-model="row.name"
                                    maxlength="60" placeholder="Ex.: Caderno"
                                    {{ (!empty($legacyMode) && $legacyMode) ? 'disabled' : '' }}>
                            </div>
                            <div class="md:col-span-3">
                                <x-input-label :value="__('Pontos (1 a 10)')" />
                                <input type="number" min="1" max="10" class="ui-input mt-1"
                                    x-bind:name="'new_columns[' + index + '][points]'" x-model="row.points"
                                    {{ (!empty($legacyMode) && $legacyMode) ? 'disabled' : '' }}>
                            </div>
                            <div class="md:col-span-2">
                                <button type="button"
                                    class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40 transition-colors"
                                    @click="rows.splice(index, 1)" x-show="rows.length > 1">
                                    Remover
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white bg-dbv-blue hover:bg-blue-800 disabled:opacity-60"
                        {{ (!empty($legacyMode) && $legacyMode) ? 'disabled' : '' }}>
                        {{ __('Salvar Configuracao') }}
                    </button>
                </div>
            </form>

            @if (empty($legacyMode))
                @foreach ($columns as $column)
                    @if (!empty($column->can_delete) && $column->can_delete)
                        <form id="delete-column-{{ $column->id }}"
                            action="{{ route('frequencia.columns.destroy', $column->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
