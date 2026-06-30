{{-- Linha de tabela para o modo de visualização em lista.
     O id é o mesmo do _card para o AJAX de pagamento funcionar nos dois modos. --}}
<tr id="mensalidade-card-{{ $m->id }}" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">

    {{-- Membro (avatar + nome) --}}
    <td class="px-5 py-3.5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full border-2 border-slate-100 dark:border-slate-800 flex items-center justify-center bg-slate-50 dark:bg-slate-900 font-black text-sm text-slate-400 shrink-0">
                {{ mb_strtoupper(substr($m->desbravador->nome, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="font-black text-[13px] text-slate-800 dark:text-white truncate" title="{{ $m->desbravador->nome }}">{{ $m->desbravador->nome }}</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase truncate md:hidden">{{ $m->desbravador->unidade->nome ?? 'Sem Unidade' }}</p>
            </div>
        </div>
    </td>

    {{-- Unidade (coluna separada no desktop) --}}
    <td class="px-5 py-3.5 hidden md:table-cell">
        <span class="text-[12px] font-bold text-slate-500 dark:text-slate-400">{{ $m->desbravador->unidade->nome ?? '—' }}</span>
    </td>

    {{-- Valor --}}
    <td class="px-5 py-3.5 text-right whitespace-nowrap">
        <span class="font-black text-[13px] {{ $m->status === 'pago' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300' }}">
            R$ {{ number_format($m->valor, 2, ',', '.') }}
        </span>
    </td>

    {{-- Status --}}
    <td class="px-5 py-3.5 text-center hidden sm:table-cell whitespace-nowrap">
        @if ($m->status === 'pago')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg uppercase font-black text-[9px] tracking-widest">
                Pago <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </span>
        @else
            <span class="inline-block px-2.5 py-1 bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg uppercase font-black text-[9px] tracking-widest">
                Pendente
            </span>
        @endif
    </td>

    {{-- Ação --}}
    <td class="px-5 py-3.5 text-right whitespace-nowrap">
        @if ($m->status === 'pendente')
            <button @click="openPagamento('{{ route('mensalidades.pagar', $m->id) }}', '{{ $m->desbravador->nome }}', '{{ number_format($m->valor, 2, ',', '.') }}')"
                    class="inline-flex items-center gap-1.5 h-9 px-4 bg-slate-800 hover:bg-slate-900 dark:bg-white dark:hover:bg-slate-200 dark:text-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all shadow shadow-slate-900/10">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Confirmar
            </button>
        @else
            <div class="inline-flex items-center gap-3">
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-500">
                    Quitada em {{ \Carbon\Carbon::parse($m->data_pagamento)->format('d/m/Y') }}
                </span>
                <button @click="estornar('{{ route('mensalidades.estornar', $m->id) }}')"
                        class="text-[10px] font-black uppercase tracking-widest text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 transition-colors">
                    Estornar
                </button>
            </div>
        @endif
    </td>
</tr>
