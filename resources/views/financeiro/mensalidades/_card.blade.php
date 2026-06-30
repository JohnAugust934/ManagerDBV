{{-- Card de uma mensalidade. Usado no grid (loop) e re-renderizado pelo
     controller na confirmação de pagamento via AJAX (atualização parcial). --}}
<div id="mensalidade-card-{{ $m->id }}"
     class="ui-card p-0 overflow-hidden border border-slate-100 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col pt-4">

    <!-- dbv header info -->
    <div class="px-5 flex items-center gap-3 mb-4">
        <div class="w-12 h-12 rounded-full border-2 border-slate-100 dark:border-slate-800 flex items-center justify-center bg-slate-50 dark:bg-slate-900 font-black text-lg text-slate-400 shrink-0">
            {{ mb_strtoupper(substr($m->desbravador->nome, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-black text-sm text-slate-800 dark:text-white truncate" title="{{ $m->desbravador->nome }}">{{ $m->desbravador->nome }}</p>
            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $m->desbravador->unidade->nome ?? 'Sem Unidade' }}</span>
        </div>
    </div>

    <!-- payment value and status core -->
    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/50 mx-4 rounded-xl flex items-center justify-between border {{ $m->status === 'pago' ? 'border-emerald-100 dark:border-emerald-900/30' : 'border-amber-100 dark:border-amber-900/30' }}">
        <p class="font-black text-lg {{ $m->status === 'pago' ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300' }}">
            R$ {{ number_format($m->valor, 2, ',', '.') }}
        </p>

        @if ($m->status === 'pago')
            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 rounded uppercase font-black text-[9px] tracking-widest flex items-center gap-1">
                Pago <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </span>
        @else
            <span class="px-2.5 py-1 bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 rounded uppercase font-black text-[9px] tracking-widest flex items-center gap-1">
                Pendente
            </span>
        @endif
    </div>

    <!-- action area -->
    <div class="mt-auto pt-4 pb-4 px-4">
        @if ($m->status === 'pendente')
            <button @click="openPagamento('{{ route('mensalidades.pagar', $m->id) }}', '{{ $m->desbravador->nome }}', '{{ number_format($m->valor, 2, ',', '.') }}')"
                    class="w-full h-11 bg-slate-800 hover:bg-slate-900 dark:bg-white dark:hover:bg-slate-200 dark:text-slate-900 text-white font-black text-[11px] uppercase tracking-widest rounded-xl transition-all shadow shadow-slate-900/10 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Confirmar Recebimento
            </button>
        @else
            <div class="w-full h-11 border border-emerald-100 dark:border-emerald-900/30 bg-emerald-50 dark:bg-emerald-900/10 flex items-center justify-center rounded-xl text-emerald-600 dark:text-emerald-500 font-bold text-[11px] tracking-wide">
                Quitada em {{ \Carbon\Carbon::parse($m->data_pagamento)->format('d/m/Y') }}
            </div>
            <button @click="estornar('{{ route('mensalidades.estornar', $m->id) }}')"
                    class="mt-2 w-full text-[10px] font-black uppercase tracking-widest text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 transition-colors">
                Estornar
            </button>
        @endif
    </div>
</div>
