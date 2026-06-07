<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('especialidades.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-white leading-tight">Detalhes da Especialidade</h2>
        </div>
    </x-slot>

    <div class="ui-page max-w-5xl mx-auto ui-animate-fade-up pb-20 space-y-6">
        <div class="ui-card overflow-hidden">
            <div class="h-1.5 w-full" style="background: {{ $especialidade->cor_fundo ?? '#9CA3AF' }}"></div>
            <div class="p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-2">{{ $especialidade->codigo ?? 'Sem código oficial' }}</p>
                        <h1 class="text-3xl font-black text-slate-800 dark:text-white leading-tight">{{ $especialidade->nome }}</h1>
                        <p class="mt-2 text-slate-500 font-semibold">{{ $especialidade->area }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 md:w-[320px]">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-900/40">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Investidos</p>
                            <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $especialidade->desbravadores_count }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-900/40">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Tipo</p>
                            <p class="text-sm font-black text-slate-800 dark:text-white mt-1">{{ $especialidade->is_avancada ? 'Avançada' : 'Regular' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-900/40 col-span-2">
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">Origem</p>
                            @if($especialidade->url_oficial)
                                <a href="{{ $especialidade->url_oficial }}" target="_blank" rel="noopener" class="text-sm font-bold text-[#002F6C] dark:text-blue-400 hover:underline break-all">
                                    {{ $especialidade->url_oficial }}
                                </a>
                            @else
                                <p class="text-sm font-semibold text-slate-500">Sem URL oficial cadastrada.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <a href="{{ route('especialidades.historico', $especialidade) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Ver histórico de alterações
                    </a>
                    <a href="{{ route('especialidades.edit', $especialidade) }}" class="ui-btn-primary px-5 py-2 rounded-xl">Editar Especialidade</a>
                </div>
            </div>
        </div>

        <div class="ui-card p-6 sm:p-8" x-data="{ editandoId: null, novaDescricao: '' }">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">Requisitos Oficiais</h3>
                <span class="text-xs font-black uppercase tracking-widest text-slate-400">{{ $especialidade->requisitosOficiais->count() }} itens</span>
            </div>

            @if($especialidade->requisitosOficiais->isEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm font-semibold mb-6">
                    Nenhum requisito cadastrado. Adicione manualmente abaixo ou use o comando
                    <code class="bg-amber-100 px-1 rounded">php artisan especialidades:sync-oficiais --requirements</code>.
                </div>
            @else
                <ol class="space-y-3 mb-6">
                    @foreach($especialidade->requisitosOficiais as $req)
                        <li class="group flex items-start gap-3 p-3 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-slate-200 dark:hover:border-slate-600 transition-colors">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center text-xs font-black mt-0.5">{{ $req->ordem }}</span>

                            <div class="flex-1 min-w-0" x-show="editandoId !== {{ $req->id }}">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 leading-relaxed">{{ $req->descricao }}</p>
                            </div>

                            {{-- Formulário de edição inline --}}
                            <form action="{{ route('especialidades.requisitos.update', [$especialidade, $req]) }}" method="POST"
                                class="flex-1 min-w-0 gap-2 flex items-end" x-show="editandoId === {{ $req->id }}" x-cloak>
                                @csrf @method('PUT')
                                <textarea name="descricao" rows="2"
                                    class="flex-1 ui-input text-sm resize-none" x-ref="edit{{ $req->id }}"
                                    :value="novaDescricao">{{ $req->descricao }}</textarea>
                                <div class="flex flex-col gap-1 shrink-0">
                                    <button type="submit" class="px-3 py-1 bg-emerald-500 text-white rounded-lg text-xs font-bold hover:bg-emerald-600 transition-colors">Salvar</button>
                                    <button type="button" @click="editandoId = null" class="px-3 py-1 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-bold text-slate-500 hover:bg-slate-50 transition-colors">Cancelar</button>
                                </div>
                            </form>

                            <div class="flex gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity" x-show="editandoId !== {{ $req->id }}">
                                <button type="button"
                                    @click="editandoId = {{ $req->id }}; novaDescricao = '{{ addslashes($req->descricao) }}'"
                                    class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Editar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form action="{{ route('especialidades.requisitos.destroy', [$especialidade, $req]) }}" method="POST"
                                    onsubmit="return confirm('Remover este requisito?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Remover">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif

            {{-- Adicionar novo requisito --}}
            <div class="border-t border-slate-100 dark:border-slate-800 pt-5" x-data="{ aberto: false }">
                <button type="button" @click="aberto = !aberto"
                    class="flex items-center gap-2 text-sm font-bold text-[#002F6C] dark:text-blue-400 hover:text-blue-700 transition-colors">
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-45': aberto }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Adicionar requisito
                </button>

                <form action="{{ route('especialidades.requisitos.store', $especialidade) }}" method="POST"
                    class="mt-4 flex flex-col gap-3" x-show="aberto" x-cloak>
                    @csrf
                    <textarea name="descricao" rows="3" placeholder="Descreva o requisito..." required
                        class="ui-input text-sm resize-none"></textarea>
                    <div class="flex gap-2">
                        <button type="submit" class="ui-btn-primary px-4 py-2 text-sm">Adicionar</button>
                        <button type="button" @click="aberto = false" class="ui-btn-secondary px-4 py-2 text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
