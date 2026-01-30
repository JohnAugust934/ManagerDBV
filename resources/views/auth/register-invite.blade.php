<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Finalizar Cadastro</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Você foi convidado para o cargo:
            <span class="font-bold uppercase text-indigo-600">{{ $invitation->role }}</span>
        </p>
    </div>

    <form method="POST" action="{{ route('register.store_invite') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $invitation->token }}">

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="email" name="email" :value="$invitation->email" readonly />
        </div>

        <div class="mt-4">
            <x-input-label for="name" :value="__('Nome Completo')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Defina sua Senha')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirme a Senha')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-4">
                {{ __('Acessar Sistema') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>