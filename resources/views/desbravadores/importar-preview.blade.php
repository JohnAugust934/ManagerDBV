<x-app-layout>

    <div class="ui-page space-y-6 max-w-4xl mx-auto">

        @php
            $novos = collect($preview)->where('duplicado', false)->count();
            $duplicados = collect($preview)->where('duplicado', true)->count();
        @endphp

        {{-- Resumo --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="ui-card p-5 text-center">
                <p class="text-3xl font-black text-emerald-500">{{ $novos }}</p>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Serão importados</p>
            </div>
            <div class="ui-card p-5 text-center">
                <p class="text-3xl font-black text-amber-500">{{ $duplicados }}</p>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Já existem</p>
            </div>
            <div class="ui-card p-5 text-center col-span-2 sm:col-span-1">
                <p class="text-3xl font-black text-red-500">{{ count($erros) }}</p>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Linhas com erro</p>
            </div>
        </div>

        @if (!empty($erros))
            <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50">
                <p class="text-sm font-black text-red-700 dark:text-red-300 mb-2">Linhas ignoradas por erro de validação:</p>
                <ul class="text-xs font-medium text-red-600 dark:text-red-400 list-disc list-inside space-y-1">
                    @foreach ($erros as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tabela de preview --}}
        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-[11px] font-black text-slate-400 uppercase tracking-widest">
                        <tr>
                            <th class="text-left px-4 py-3">Nome</th>
                            <th class="text-left px-4 py-3">Nascimento</th>
                            <th class="text-left px-4 py-3">Sexo</th>
                            <th class="text-left px-4 py-3">Responsável</th>
                            <th class="text-left px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($preview as $linha)
                            <tr class="{{ $linha['duplicado'] ? 'bg-amber-50/50 dark:bg-amber-900/10' : '' }}">
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-white">{{ $linha['nome'] }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $linha['data_nascimento'] }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $linha['sexo'] }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $linha['nome_responsavel'] }}</td>
                                <td class="px-4 py-3">
                                    @if ($linha['duplicado'])
                                        <span class="px-2 py-1 rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 text-[10px] font-black uppercase">Já existe</span>
                                    @else
                                        <span class="px-2 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-[10px] font-black uppercase">Novo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Confirmação --}}
        <form action="{{ route('desbravadores.importar.confirmar') }}" method="POST" class="ui-card p-6 space-y-4">
            @csrf

            @error('confirmo_consentimento')
                <p class="text-sm font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl bg-blue-50/60 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/50">
                <input type="checkbox" name="confirmo_consentimento" value="1" required class="mt-0.5 w-5 h-5 rounded border-slate-300 text-[#002F6C]">
                <span class="text-sm font-bold text-slate-600 dark:text-slate-300">
                    Confirmo que possuo o consentimento LGPD dos responsáveis pelos dados aqui importados.
                </span>
            </label>

            @if ($duplicados > 0)
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="substituir_duplicados" value="1" class="w-5 h-5 rounded border-slate-300 text-[#002F6C]">
                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Importar também os {{ $duplicados }} que já existem (cria duplicata)</span>
                </label>
            @endif

            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="{{ route('desbravadores.importar.index') }}" class="ui-btn-secondary w-full sm:w-auto text-center">Voltar</a>
                <button type="submit" class="ui-btn-primary w-full sm:w-auto" @disabled($novos === 0 && $duplicados === 0)>
                    Confirmar importação
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
