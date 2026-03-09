<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dbv-blue dark:text-gray-200 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                </path>
            </svg>
            {{ __('Gestão de Convites') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div
                    class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Gerencie os convites enviados. Links
                            utilizados ou expirados podem ser visualizados no histórico.</p>
                    </div>
                    <a href="{{ route('invites.create') }}"
                        class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-lg shadow-green-500/30 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Novo Convite
                    </a>
                </div>

                @if ($invites->isEmpty())
                    <div class="p-10 text-center flex flex-col items-center">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                </path>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Nenhum convite gerado no momento.</p>
                    </div>
                @else
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6 bg-gray-50 dark:bg-gray-900/50">
                        @foreach ($invites as $invite)
                            @php
                                // Lógica de Status Inteligente
                                $isUsed = !is_null($invite->registered_at);
                                $isExpired = !$isUsed && $invite->expires_at && $invite->expires_at->isPast();

                                $statusText = $isUsed ? 'Usado' : ($isExpired ? 'Expirado' : 'Pendente');
                                $statusColor = $isUsed
                                    ? 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                    : ($isExpired
                                        ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                        : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400');
                                $barColor = $isUsed ? 'bg-gray-400' : ($isExpired ? 'bg-red-500' : 'bg-dbv-blue');
                            @endphp

                            <div
                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col {{ $isUsed ? 'opacity-80' : '' }}">
                                {{-- Tarja Superior de Cor --}}
                                <div class="h-1.5 w-full {{ $barColor }}"></div>

                                <div class="p-5 flex-1 flex flex-col">

                                    {{-- Cabeçalho do Card (Badges e Lixeira) --}}
                                    <div class="flex justify-between items-start mb-4 gap-2">
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                class="px-2.5 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] font-bold rounded uppercase tracking-wider">
                                                {{ $invite->role }}
                                            </span>
                                            <span
                                                class="px-2.5 py-1 {{ $statusColor }} text-[10px] font-bold rounded uppercase tracking-wider">
                                                {{ $statusText }}
                                            </span>
                                        </div>
                                        <form action="{{ route('invites.destroy', $invite->id) }}" method="POST"
                                            onsubmit="return confirm('{{ $isUsed ? 'Excluir este registro do histórico?' : 'Cancelar este convite imediatamente? O link parará de funcionar.' }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-400 hover:text-red-600 transition-colors p-1"
                                                title="Excluir Convite">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Informações --}}
                                    <div class="space-y-3 mb-5">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 mr-2 text-gray-400 shrink-0 mt-0.5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <div>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400">Enviado
                                                    para:</span>
                                                <span
                                                    class="block text-sm font-semibold text-gray-900 dark:text-white truncate"
                                                    title="{{ $invite->email }}">{{ $invite->email }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Expira: <strong
                                                class="ml-1 {{ $isExpired ? 'text-red-500' : 'text-gray-800 dark:text-white' }}">
                                                {{ $invite->expires_at ? $invite->expires_at->format('d/m/Y') : 'Não expira' }}
                                            </strong>
                                        </div>
                                    </div>

                                    {{-- Rodapé Interativo --}}
                                    @if ($isUsed)
                                        <div
                                            class="mt-auto bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-200 dark:border-gray-700 text-center">
                                            <span
                                                class="text-xs font-semibold text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1.5">
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Utilizado em {{ $invite->registered_at->format('d/m/Y \à\s H:i') }}
                                            </span>
                                        </div>
                                    @elseif($isExpired)
                                        <div
                                            class="mt-auto bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-200 dark:border-red-800/30 text-center">
                                            <span
                                                class="text-xs font-semibold text-red-500 dark:text-red-400 flex items-center justify-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Este convite não é mais válido
                                            </span>
                                        </div>
                                    @else
                                        @php
                                            $inviteUrl = route('register.invite', ['token' => $invite->token]);
                                        @endphp
                                        <div x-data="{ copiado: false, url: '{{ $inviteUrl }}' }" class="mt-auto relative">
                                            <input type="text" readonly value="{{ $inviteUrl }}"
                                                class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-500 rounded-lg pr-12 focus:ring-0 focus:border-gray-200">
                                            <button
                                                @click="navigator.clipboard.writeText(url); copiado = true; setTimeout(() => copiado = false, 2000)"
                                                class="absolute right-1 top-1 bottom-1 px-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded flex items-center justify-center text-gray-600 hover:text-dbv-blue hover:bg-blue-50 transition-colors"
                                                title="Copiar Link">
                                                <svg x-show="!copiado" class="w-4 h-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                                                    </path>
                                                </svg>
                                                <svg x-show="copiado" x-cloak class="w-4 h-4 text-green-500"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
