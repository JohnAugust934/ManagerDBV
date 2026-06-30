<x-app-layout>
    <x-slot name="header">Novo Comunicado</x-slot>

    <div class="ui-page max-w-2xl mx-auto">
        <form action="{{ route('comunicados.store') }}" method="POST" class="ui-card p-6 space-y-5"
              x-data="{ destinatarios: '{{ old('destinatarios', 'ativos') }}', corpo: '{{ old('corpo') }}' }">
            @csrf

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50">
                    <ul class="text-sm font-bold text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="ui-input-label">Título</label>
                <input type="text" name="titulo" maxlength="255" required value="{{ old('titulo') }}" class="ui-input h-12">
            </div>

            <div>
                <label class="ui-input-label">Mensagem</label>
                <textarea name="corpo" rows="7" maxlength="5000" required x-model="corpo" class="ui-input"></textarea>
                <p class="text-xs text-slate-400 mt-1 text-right"><span x-text="corpo.length"></span> / 5000</p>
            </div>

            <div>
                <label class="ui-input-label">Destinatários</label>
                <select name="destinatarios" x-model="destinatarios" class="ui-input h-12">
                    <option value="todos">Todos os membros ativos</option>
                    <option value="ativos">Apenas membros ativos</option>
                    <option value="unidade">Por unidade</option>
                </select>
            </div>

            <div x-show="destinatarios === 'unidade'" x-cloak>
                <label class="ui-input-label">Unidade</label>
                <select name="unidade_id" class="ui-input h-12">
                    <option value="">Selecione...</option>
                    @foreach ($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>{{ $unidade->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-end pt-2">
                <a href="{{ route('comunicados.index') }}" class="ui-btn-secondary w-full sm:w-auto text-center">Cancelar</a>
                <button type="submit" class="ui-btn-primary w-full sm:w-auto">Enviar comunicado</button>
            </div>
        </form>
    </div>
</x-app-layout>
