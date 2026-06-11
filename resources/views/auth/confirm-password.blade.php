<x-guest-layout>
    <div class="mb-6 text-center sm:text-left">
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1.5 drop-shadow-md">Confirmar Senha</h2>
        <p class="text-slate-300 text-sm font-medium leading-relaxed">
            Esta é uma área segura do sistema. Confirme sua senha antes de continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        {{-- Senha --}}
        <div>
            <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Senha</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password" autofocus
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-400 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner"
                    placeholder="••••••••">
            </div>
            @error('password')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
        </div>

        {{-- Botão --}}
        <div class="pt-2">
            <button type="submit" class="w-full relative inline-flex items-center justify-center gap-3 rounded-2xl px-6 py-4 text-sm font-black text-white uppercase tracking-widest bg-gradient-to-r from-[#D9222A] to-red-600 hover:from-red-600 hover:to-[#D9222A] transition-all duration-300 shadow-xl shadow-red-900/30 overflow-hidden group active:scale-[0.98]">
                <span class="relative z-10">Confirmar</span>
            </button>
        </div>
    </form>
</x-guest-layout>
