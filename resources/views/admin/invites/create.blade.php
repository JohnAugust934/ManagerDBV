<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('invites.index') }}"
                class="p-2 text-gray-500 hover:text-dbv-blue dark:text-gray-400 dark:hover:text-blue-400 bg-gray-100 dark:bg-gray-700 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-200 leading-tight">
                {{ __('Gerar Novo Convite') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <form action="{{ route('invites.store') }}" method="POST" class="p-6 md:p-8">
                    @csrf

                    {{-- E-MAIL (Obrigatório) --}}
                    <div class="mb-8">
                        <x-input-label for="email" value="E-mail do Convidado"
                            class="font-bold text-gray-800 dark:text-gray-200 text-base mb-2" />
                        <x-text-input id="email"
                            class="block w-full bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-lg py-3"
                            type="email" name="email" :value="old('email')" placeholder="Ex: conselheiro@email.com"
                            required />
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            O link gerado será exclusivo e amarrado a este e-mail.
                        </p>
                        @error('email')
                            <p class="text-red-500 text-sm mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700 mb-8">

                    {{-- CARGO --}}
                    <div class="mb-8">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-3 uppercase tracking-wider">Cargo
                            Padrão do Convite</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach (['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="{{ $role }}"
                                        class="peer sr-only" required {{ old('role') == $role ? 'checked' : '' }}>
                                    <div
                                        class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all peer-checked:border-dbv-blue peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 peer-checked:shadow-md text-center">
                                        <span
                                            class="block uppercase font-bold text-sm tracking-wider text-gray-600 dark:text-gray-300 peer-checked:text-dbv-blue dark:peer-checked:text-blue-400">
                                            {{ $role }}
                                        </span>
                                    </div>
                                    <div
                                        class="absolute top-2 right-2 hidden peer-checked:block text-dbv-blue dark:text-blue-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('role')
                            <p class="text-red-500 text-sm mt-2 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DATA DE EXPIRAÇÃO --}}
                    <div
                        class="bg-gray-50 dark:bg-gray-900/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700 mb-8">
                        <x-input-label for="expires_at" value="Data de Expiração (Opcional)"
                            class="font-bold text-gray-700 dark:text-gray-300" />
                        <input id="expires_at"
                            class="block mt-1 w-full md:w-1/2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            type="date" name="expires_at" value="{{ old('expires_at') }}" />
                        <p class="text-xs text-gray-500 mt-2">O link para de funcionar automaticamente após o dia
                            selecionado.</p>
                        @error('expires_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4">
                        <a href="{{ route('invites.index') }}"
                            class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Cancelar</a>
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-green-500/30 transform transition hover:-translate-y-0.5 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Criar Convite
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
