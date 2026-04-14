<x-app-layout>
    <x-slot name="header">Criar Usuário</x-slot>

    <div class="ui-page max-w-5xl mx-auto space-y-6 ui-animate-fade-up">

        {{-- Header Navigation --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('usuarios.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-[#002F6C] dark:text-slate-400 dark:hover:text-blue-400 font-bold text-sm transition-colors group">
                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-[#002F6C]/10 dark:group-hover:bg-blue-500/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </div>
                Voltar à Equipe
            </a>
        </div>

        <div class="ui-card p-0 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col sm:flex-row items-start sm:items-center gap-4 justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Criar Novo Membro</h3>
                        <p class="text-sm font-medium text-slate-500 mt-0.5">Cadastre um novo dirigente, senha e distribua seus poderes de tela.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf

                <div class="p-6 sm:p-8 space-y-8">
                    {{-- Dados Pessoais --}}
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Identificação
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Nome Completo <span class="text-red-500">*</span></label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="ui-input w-full font-bold">
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <label for="email" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">E-mail de Acesso (Login) <span class="text-red-500">*</span></label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="ui-input w-full font-bold">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Senha --}}
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            Segurança
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Senha <span class="text-red-500">*</span></label>
                                <input id="password" type="password" name="password" required class="ui-input w-full font-bold">
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Confirmar Senha <span class="text-red-500">*</span></label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required class="ui-input w-full font-bold">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 my-6"></div>

                    {{-- Cargo --}}
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            Cargo Hierárquico base
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach (['diretor', 'secretario', 'tesoureiro', 'conselheiro', 'instrutor'] as $role)
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="role" value="{{ $role }}" class="peer sr-only" {{ $role == 'conselheiro' ? 'checked' : '' }}>
                                    <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-center font-black uppercase tracking-widest text-[11px] text-slate-500 peer-checked:border-[#002F6C] peer-checked:bg-[#002F6C]/5 peer-checked:text-[#002F6C] dark:peer-checked:border-blue-500 dark:peer-checked:bg-blue-500/10 dark:peer-checked:text-blue-400 transition-colors">
                                        {{ $role }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Permissões Extra --}}
                    <div class="px-5 py-5 rounded-2xl bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30">
                        <h4 class="text-sm font-black uppercase tracking-widest text-amber-700 dark:text-amber-500 mb-1 flex items-center gap-2">Poderes Adicionais (Opcional)</h4>
                        <p class="text-[10px] font-bold text-amber-600/80 dark:text-amber-500/70 uppercase tracking-widest mb-4">Marque apenas se este membro precisar acessar um módulo fora do cargo.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach (\App\Models\User::PERMISSOES as $key => $label)
                                @if ($key === 'gestao_acessos' && !($canGrantAccessManagement ?? false))
                                    @continue
                                @endif
                                <label class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-slate-800 border border-amber-200/50 dark:border-amber-700/30 cursor-pointer hover:border-amber-300 transition-colors">
                                    <input type="checkbox" name="extra_permissions[]" value="{{ $key }}" class="w-5 h-5 rounded border-amber-300 text-amber-500 focus:ring-amber-500 dark:bg-slate-900 dark:border-amber-700">
                                    <span class="text-[11px] font-black uppercase tracking-widest text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Rodapé / Botões --}}
                <div class="px-6 sm:px-8 py-5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row justify-end gap-3">
                    <a href="{{ route('usuarios.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-black text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm text-center transition-all">Cancelar</a>
                    <button type="submit" class="w-full sm:w-auto ui-btn-primary flex justify-center items-center gap-2 h-12">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Efetivar Cadastro
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
