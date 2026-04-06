<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-100 leading-tight">{{ __('Gestao de Convites') }}</h2>
    </x-slot>

    <div class="ui-page">
        <div class="ui-card overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <p class="ui-subtitle">Gerencie convites enviados. Links utilizados ou expirados continuam no historico.</p>
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
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 p-4 md:p-6 bg-gray-50 dark:bg-gray-900/50">
                    @foreach ($invites as $invite)
                        @php
                            $isUsed = !is_null($invite->registered_at);
                            $isExpired = !$isUsed && $invite->expires_at && $invite->expires_at->isPast();

                            $statusText = $isUsed? 'Usado' : ($isExpired? 'Expirado' : 'Pendente');
                            $statusColor = $isUsed? 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                : ($isExpired? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                    : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400');
                        @endphp

                        <div class="ui-card p-4 {{ $isUsed? 'opacity-80' : '' }}">
                            <div class="flex justify-between items-start gap-2 mb-4">
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-2.5 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] font-bold rounded uppercase tracking-wider">{{ $invite->role }}</span>
                                    <span class="px-2.5 py-1 {{ $statusColor }} text-[10px] font-bold rounded uppercase tracking-wider">{{ $statusText }}</span>
                                </div>
                                <form action="{{ route('invites.destroy', $invite->id) }}" method="POST"
                                    onsubmit="return confirm('{{ $isUsed? 'Excluir este registro do historico?' : 'Cancelar este convite imediatamente? O link para de funcionar.' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Excluir Convite">Excluir</button>
                                </form>
                            </div>

                            <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300 mb-4">
                                <p class="truncate" title="{{ $invite->email }}"><strong>Email:</strong> {{ $invite->email }}</p>
                                <p><strong>Expira:</strong> {{ $invite->expires_at? $invite->expires_at->format('d/m/Y') : 'Não expira' }}</p>
                            </div>

                            @if ($isUsed)
                                <div class="ui-card-muted p-3 text-xs font-semibold text-center">Utilizado em {{ $invite->registered_at->format('d/m/Y \a\s H:i') }}</div>
                            @elseif($isExpired)
                                <div class="ui-card-muted p-3 text-xs font-semibold text-center text-red-600 dark:text-red-400">Este convite não e mais valido</div>
                            @else
                                @php $inviteUrl = route('register.invite', ['token' => $invite->token]); @endphp
                                <div x-data="{ copiado: false, url: '{{ $inviteUrl }}' }" class="relative">
                                    <input type="text" readonly value="{{ $inviteUrl }}" class="ui-input pr-24 text-xs" />
                                    <button @click="navigator.clipboard.writeText(url); copiado = true; setTimeout(() => copiado = false, 2000)"
                                        class="absolute right-1 top-1 px-3 h-[34px] rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs font-semibold">
                                        <span x-show="!copiado">Copiar</span>
                                        <span x-show="copiado" x-cloak>Copiado</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

