<x-app-layout>
    <x-slot name="header">Painel da Plataforma</x-slot>

    <div class="ui-page max-w-5xl mx-auto ui-animate-fade-up pb-20">

        {{-- Cabeçalho --}}
        <div class="ui-card p-6 sm:p-8 mb-6 bg-gradient-to-br from-[#002F6C] via-[#00408f] to-blue-600 text-white">
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Administração da Plataforma</h1>
            <p class="text-blue-200 font-medium mt-1">Gerencie todos os clubes, entre em modo suporte e exporte dados.</p>

            <div class="grid grid-cols-2 gap-4 mt-6 max-w-md">
                <div class="bg-white/10 border border-white/20 rounded-2xl px-4 py-3">
                    <p class="text-3xl font-black">{{ $totalClubes }}</p>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-blue-200">Clubes</p>
                </div>
                <div class="bg-white/10 border border-white/20 rounded-2xl px-4 py-3">
                    <p class="text-3xl font-black">{{ $totalUsuarios }}</p>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-blue-200">Usuários vinculados</p>
                </div>
            </div>
        </div>

        {{-- Ações --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h2 class="text-lg font-black text-slate-800 dark:text-white">Clubes cadastrados</h2>
            <a href="{{ route('platform.clubs.create') }}" class="ui-btn-primary w-full sm:w-auto">
                Novo Clube
            </a>
        </div>

        {{-- Lista de clubes --}}
        <div class="space-y-3">
            @forelse($clubs as $row)
                <div class="ui-card p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="text-base font-black text-slate-800 dark:text-white truncate">{{ $row->model->nome }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $row->model->cidade }} · {{ $row->model->associacao ?? 'Sem associação' }}
                        </p>
                        <div class="flex flex-wrap gap-3 mt-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <span>{{ $row->usuarios }} usuários</span>
                            <span>{{ $row->unidades }} unidades</span>
                            <span>{{ $row->desbravadores }} desbravadores</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                        <form method="POST" action="{{ route('platform.enter', $row->model) }}" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="ui-btn-primary w-full sm:w-auto">Entrar (suporte)</button>
                        </form>
                        <a href="{{ route('platform.export', $row->model) }}" class="ui-btn-secondary w-full sm:w-auto text-center">
                            Exportar
                        </a>
                    </div>
                </div>
            @empty
                <div class="ui-card p-8 text-center text-slate-500 dark:text-slate-400">
                    Nenhum clube cadastrado ainda. Use “Novo Clube” para começar.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
