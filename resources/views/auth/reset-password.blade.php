<x-guest-layout>
    <div class="mb-6 text-center sm:text-left">
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1.5 drop-shadow-md">Redefinir Senha</h2>
        <p class="text-slate-300 text-sm font-medium">Escolha uma nova senha para sua conta.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        {{-- Token de reset --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-400 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner"
                    placeholder="seu.email@exemplo.com">
            </div>
            @error('email')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
        </div>

        {{-- Senha --}}
        <div>
            <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Nova Senha</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-400 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner"
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
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-400 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner"
                    placeholder="Repita a nova senha">
            </div>
            @error('password_confirmation')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
        </div>

        {{-- Botão --}}
        <div class="pt-2">
            <button type="submit" class="w-full relative inline-flex items-center justify-center gap-3 rounded-2xl px-6 py-4 text-sm font-black text-white uppercase tracking-widest bg-gradient-to-r from-[#D9222A] to-red-600 hover:from-red-600 hover:to-[#D9222A] transition-all duration-300 shadow-xl shadow-red-900/30 overflow-hidden group active:scale-[0.98]">
                <span class="relative z-10">Redefinir Senha</span>
            </button>
        </div>
    </form>
</x-guest-layout>
