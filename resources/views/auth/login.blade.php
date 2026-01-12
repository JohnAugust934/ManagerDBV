<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-dbv-blue focus:ring-dbv-blue" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-dbv-blue focus:ring-dbv-blue" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-dbv-blue shadow-sm focus:ring-dbv-blue" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Lembrar de mim') }}</span>
            </label>

            @if (Route::has('password.request'))
            <a class="underline text-sm text-gray-600 hover:text-dbv-blue rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                {{ __('Esqueceu a senha?') }}
            </a>
            @endif
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-dbv-blue hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                {{ __('Entrar no Sistema') }}
            </button>
        </div>

        <div class="text-center mt-4">
            <span class="text-sm text-gray-600">Não tem conta? </span>
            <a href="{{ route('register') }}" class="text-sm font-bold text-dbv-red hover:underline">Cadastre-se</a>
        </div>
    </form>
</x-guest-layout>