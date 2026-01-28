<x-guest-layout>
    <div class="max-h-[80vh] overflow-y-auto px-2 custom-scrollbar">

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Dados do Usuário</h3>

                <div>
                    <x-input-label for="name" :value="__('Nome Completo')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="email" :value="__('Email (Vinculado ao Convite)')" />
                    <x-text-input id="email" class="block mt-1 w-full bg-gray-100 text-gray-500 cursor-not-allowed" type="email" name="email" :value="$email" readonly />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>

            @if($needsClubSetup)
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Dados do Clube</h3>
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded relative mb-4 text-sm">
                    Você está fundando o clube no sistema. Preencha os dados abaixo.
                </div>

                <div>
                    <x-input-label for="club_name" :value="__('Nome do Clube')" />
                    <x-text-input id="club_name" class="block mt-1 w-full" type="text" name="club_name" :value="old('club_name')" required placeholder="Ex: Clube Orion" />
                    <x-input-error :messages="$errors->get('club_name')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="club_city" :value="__('Cidade')" />
                    <x-text-input id="club_city" class="block mt-1 w-full" type="text" name="club_city" :value="old('club_city')" required />
                    <x-input-error :messages="$errors->get('club_city')" class="mt-2" />
                </div>
            </div>
            @else
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Vínculo</h3>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative text-sm">
                    Você será adicionado ao clube: <strong>{{ \App\Models\Club::first()->nome }}</strong>.
                </div>
            </div>
            @endif

            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Segurança</h3>

                <div class="mt-4">
                    <x-input-label for="password" :value="__('Senha')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirmar Senha')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>

            <div class="flex items-center justify-end mt-4 pt-4 border-t">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Já tem conta?') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ $needsClubSetup ? __('Fundar Clube') : __('Completar Cadastro') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>