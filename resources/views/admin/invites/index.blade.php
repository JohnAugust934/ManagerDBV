<x-app-layout>
    <x-slot name="header">Gestão de Convites</x-slot>

    <div class="ui-page">
        <div class="ui-card overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <p class="ui-subtitle">Gerencie convites enviados. Links utilizados ou expirados continuam no histórico.</p>
                <a href="{{ route('invites.create') }}" class="ui-btn-primary w-full sm:w-auto">Novo Convite</a>
            </div>

            @if ($invites->isEmpty())
                <div class="p-6">
                    <x-empty-state
                        title="Nenhum convite gerado"
                        description="Crie um novo convite para compartilhar acesso seguro ao sistema.">
                        <x-slot:action>
                            <a href="{{ route('invites.create') }}" class="ui-btn-primary">Criar Convite</a>
                        </x-slot:action>
                    </x-empty-state>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 p-4 md:p-6 bg-slate-50 dark:bg-slate-900/50">
                    @foreach ($invites as $invite)
                        @php
                            $isUsed = !is_null($invite->registered_at);
                            $isExpired = !$isUsed && $invite->expires_at && $invite->expires_at->isPast();

                            $statusText = $isUsed ? 'Usado' : ($isExpired ? 'Expirado' : 'Pendente');
                            $statusColor = $isUsed
                                ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300'
                                : ($isExpired
                                    ? 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400'
                                    : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400');
                        @endphp

                        <div class="ui-card p-4 {{ $isUsed ? 'opacity-80' : '' }}">
                            <div class="flex justify-between items-start gap-2 mb-4">
                                <div class="flex flex-wrap gap-2">
                                    <span class="ui-badge bg-[#002F6C]/10 text-[#002F6C] dark:bg-blue-500/20 dark:text-blue-400">{{ $invite->role }}</span>
                                    <span class="ui-badge {{ $statusColor }}">{{ $statusText }}</span>
                                </div>
                                <form action="{{ route('invites.destroy', $invite->id) }}" method="POST"
                                    onsubmit="return confirm('{{ $isUsed ? 'Excluir este registro do histórico?' : 'Cancelar este convite imediatamente? O link para de funcionar.' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Excluir Convite">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>

                            <div class="space-y-2 text-sm text-slate-700 dark:text-slate-300 mb-4">
                                <p class="truncate" title="{{ $invite->email }}"><strong>Email:</strong> {{ $invite->email }}</p>
                                <p><strong>Expira:</strong> {{ $invite->expires_at ? $invite->expires_at->format('d/m/Y') : 'Não expira' }}</p>
                            </div>

                            @if ($isUsed)
                                <div class="ui-card-muted p-3 text-xs font-semibold text-center">Utilizado em {{ $invite->registered_at->format('d/m/Y \a\s H:i') }}</div>
                            @elseif($isExpired)
                                <div class="flex flex-col gap-2">
                                    <div class="ui-card-muted p-3 text-xs font-semibold text-center text-red-600 dark:text-red-400">Este convite não é mais válido</div>
                                    <form action="{{ route('invites.resend', $invite->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full ui-btn-secondary py-1.5 text-xs">
                                            Renovar e Reenviar
                                        </button>
                                    </form>
                                </div>
                            @else
                                @php $inviteUrl = route('register.invite', ['token' => $invite->token]); @endphp
                                <div class="space-y-2">
                                    <div x-data="{ copiado: false, url: '{{ $inviteUrl }}' }" class="relative">
                                        <input type="text" readonly value="{{ $inviteUrl }}" class="ui-input pr-24 text-xs" />
                                        <button @click="navigator.clipboard.writeText(url); copiado = true; setTimeout(() => copiado = false, 2000)"
                                            class="absolute right-1 top-1/2 -translate-y-1/2 px-3 h-9 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                            <span x-show="!copiado">Copiar</span>
                                            <span x-show="copiado" x-cloak class="text-emerald-600 dark:text-emerald-400">Copiado ✓</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('invites.resend', $invite->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full ui-btn-secondary py-1.5 text-xs">
                                            Reenviar por e-mail
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
