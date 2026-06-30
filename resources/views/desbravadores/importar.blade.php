<x-app-layout>
    <x-slot name="header">Importar Desbravadores (CSV)</x-slot>

    <div class="ui-page space-y-6 max-w-3xl mx-auto">

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50">
                <ul class="text-sm font-bold text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Instruções --}}
        <div class="ui-card p-6">
            <h3 class="font-black text-slate-800 dark:text-white mb-3">Formato esperado</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Envie um arquivo <strong>.csv</strong> separado por ponto-e-vírgula (<code>;</code>) ou vírgula, com
                a primeira linha de cabeçalho. Colunas reconhecidas:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach ($campos as $chave => $label)
                    <div class="flex items-center gap-2 text-xs">
                        <code class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-black text-[#002F6C] dark:text-blue-400">{{ $chave }}</code>
                        <span class="text-slate-500 dark:text-slate-400">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 overflow-x-auto">
                <code class="text-[11px] text-slate-600 dark:text-slate-300 whitespace-pre">nome;data_nascimento;sexo;email;nome_responsavel
João da Silva;15/03/2012;M;joao@email.com;Maria da Silva</code>
            </div>
        </div>

        {{-- Formulário de upload --}}
        <form action="{{ route('desbravadores.importar.preview') }}" method="POST" enctype="multipart/form-data" class="ui-card p-6 space-y-5">
            @csrf

            <div>
                <label class="ui-input-label">Unidade de destino</label>
                <select name="unidade_id" required class="ui-input h-12">
                    <option value="">Selecione a unidade...</option>
                    @foreach ($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>{{ $unidade->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-input-label">Arquivo CSV</label>
                <input type="file" name="arquivo" accept=".csv,text/csv" required
                       class="ui-input h-12 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#002F6C] file:text-white file:font-bold file:text-xs">
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="{{ route('desbravadores.index') }}" class="ui-btn-secondary w-full sm:w-auto text-center">Cancelar</a>
                <button type="submit" class="ui-btn-primary w-full sm:w-auto">Analisar arquivo</button>
            </div>
        </form>
    </div>
</x-app-layout>
