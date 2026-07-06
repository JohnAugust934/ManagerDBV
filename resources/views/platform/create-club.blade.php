<x-app-layout>

    <div class="ui-page max-w-2xl mx-auto ui-animate-fade-up pb-20">
        <div class="ui-card p-6 sm:p-8">
            <h1 class="text-xl font-black text-slate-800 dark:text-white mb-1">Cadastrar novo clube</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                Cria o clube e o usuário master inicial, que poderá configurar o restante.
            </p>

            <form method="POST" action="{{ route('platform.clubs.store') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="nome" value="Nome do clube" />
                    <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome')" required autofocus />
                    <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="cidade" value="Cidade" />
                        <x-text-input id="cidade" name="cidade" type="text" class="mt-1 block w-full" :value="old('cidade')" required />
                        <x-input-error :messages="$errors->get('cidade')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="associacao" value="Associação" />
                        <x-text-input id="associacao" name="associacao" type="text" class="mt-1 block w-full" :value="old('associacao')" />
                        <x-input-error :messages="$errors->get('associacao')" class="mt-1" />
                    </div>
                </div>

                <hr class="border-slate-200 dark:border-slate-700">

                <h2 class="text-sm font-black uppercase tracking-wider text-slate-400">Usuário master inicial</h2>

                <div>
                    <x-input-label for="master_name" value="Nome" />
                    <x-text-input id="master_name" name="master_name" type="text" class="mt-1 block w-full" :value="old('master_name')" required />
                    <x-input-error :messages="$errors->get('master_name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="master_email" value="E-mail" />
                    <x-text-input id="master_email" name="master_email" type="email" class="mt-1 block w-full" :value="old('master_email')" required />
                    <x-input-error :messages="$errors->get('master_email')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="master_password" value="Senha (mín. 8 caracteres)" />
                    <x-text-input id="master_password" name="master_password" type="password" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('master_password')" class="mt-1" />
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" class="ui-btn-primary w-full sm:w-auto">Criar clube</button>
                    <a href="{{ route('platform.index') }}" class="ui-btn-secondary w-full sm:w-auto text-center">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
