<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-dbv-blue shadow-sm focus:ring-dbv-blue" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-slate-300">{{ __('Lembrar de mim') }}</span>
            </label>

            @if (Route::has('password.request'))
            <a class="underline text-sm text-gray-600 dark:text-slate-300 hover:text-dbv-blue rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                {{ __('Esqueceu a senha?') }}
            </a>
            @endif
        </div>

        <div>
            <button type="submit" class="ui-btn-primary w-full">
                {{ __('Entrar no Sistema') }}
            </button>
        </div>
    </form>
</x-guest-layout>
