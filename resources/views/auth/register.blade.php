<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1.5 drop-shadow-md">Criar Conta</h2>
        <p class="text-slate-400 text-sm font-medium">Complete o cadastro para acessar o sistema</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Nome --}}
        <div>
            <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Nome Completo</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-500 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner"
                    placeholder="Seu nome completo">
            </div>
            @error('name')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
        </div>

        {{-- Email (readonly) --}}
        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Email do Convite</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input id="email" type="email" name="email" value="{{ $email }}" readonly
                    class="block w-full rounded-2xl border-0 bg-black/10 text-slate-400 ring-1 ring-inset ring-white/5 text-sm py-4 pl-12 pr-4 cursor-not-allowed">
            </div>
            @error('email')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
        </div>

        {{-- Dados do Clube (se necessário) --}}
        @if($needsClubSetup)
            <div class="pt-1">
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex-1 h-px bg-white/10"></div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Dados do Clube</span>
                    <div class="flex-1 h-px bg-white/10"></div>
                </div>
                <div class="bg-blue-500/10 border border-blue-500/20 text-blue-300 px-4 py-3 rounded-xl text-xs font-bold mb-3">
                    Você está fundando o clube. Preencha os dados abaixo.
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="club_name" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Nome do Clube</label>
                        <input id="club_name" type="text" name="club_name" value="{{ old('club_name') }}" required placeholder="Ex: Clube Orion"
                            class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-500 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 px-4 shadow-inner">
                        @error('club_name')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="club_city" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Cidade</label>
                        <input id="club_city" type="text" name="club_city" value="{{ old('club_city') }}" required
                            class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-500 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 px-4 shadow-inner">
                        @error('club_city')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        @else
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 px-4 py-3 rounded-xl text-xs font-bold">
                ✓ Você será adicionado ao clube: <strong>{{ \App\Models\Club::first()->nome }}</strong>
            </div>
        @endif

        {{-- Divider Segurança --}}
        <div class="flex items-center gap-2 pt-1">
            <div class="flex-1 h-px bg-white/10"></div>
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Segurança</span>
            <div class="flex-1 h-px bg-white/10"></div>
        </div>

        {{-- Senha --}}
        <div>
            <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Senha</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-500 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner"
                    placeholder="Mínimo 8 caracteres">
            </div>
            @error('password')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
        </div>

        {{-- Confirmar Senha --}}
        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Confirmar Senha</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-500 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner"
                    placeholder="Repita a senha">
            </div>
            @error('password_confirmation')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
        </div>

        {{-- Botão Submit --}}
        <div class="pt-2">
            <button type="submit" class="w-full relative inline-flex items-center justify-center gap-3 rounded-2xl px-6 py-4 text-sm font-black text-white uppercase tracking-widest bg-gradient-to-r from-[#D9222A] to-red-600 hover:from-red-600 hover:to-[#D9222A] transition-all duration-300 shadow-xl shadow-red-900/30 overflow-hidden group active:scale-[0.98]">
                <span class="relative z-10 transition-transform duration-300 group-hover:-translate-y-10">
                    {{ $needsClubSetup ? 'Fundar Clube' : 'Completar Cadastro' }}
                </span>
                <span class="absolute inset-0 z-10 flex items-center justify-center translate-y-10 group-hover:translate-y-0 transition-transform duration-300">
                    Confirmar
                    <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </button>
        </div>

        <p class="text-center text-xs text-slate-400 font-medium">
            Já tem conta?
            <a href="{{ route('login') }}" class="text-[#FCD116] font-bold hover:underline">Fazer login</a>
        </p>
    </form>
</x-guest-layout>