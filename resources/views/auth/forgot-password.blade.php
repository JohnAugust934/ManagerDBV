<x-guest-layout>
    <div class="mb-6 text-center sm:text-left">
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1.5 drop-shadow-md">Recuperar Senha</h2>
        <p class="text-slate-300 text-sm font-medium leading-relaxed">
            Esqueceu sua senha? Sem problema. Informe seu email e enviaremos um link para você definir uma nova.
        </p>
    </div>

    {{-- Status da sessão --}}
    @if (session('status'))
        <div class="mb-5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 p-4 rounded-xl text-sm font-bold">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-400 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner"
                    placeholder="seu.email@exemplo.com">
            </div>
            @error('email')<p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>@enderror
        </div>

        {{-- Botão --}}
        <div class="pt-2">
            <button type="submit" class="w-full relative inline-flex items-center justify-center gap-3 rounded-2xl px-6 py-4 text-sm font-black text-white uppercase tracking-widest bg-gradient-to-r from-[#D9222A] to-red-600 hover:from-red-600 hover:to-[#D9222A] transition-all duration-300 shadow-xl shadow-red-900/30 overflow-hidden group active:scale-[0.98]">
                <span class="relative z-10">Enviar Link de Recuperação</span>
            </button>
        </div>

        <p class="text-center text-xs text-slate-400 font-medium pt-1">
            Lembrou a senha?
            <a href="{{ route('login') }}" class="text-[#FCD116] font-bold hover:text-white transition-colors">Voltar ao login</a>
        </p>
    </form>
</x-guest-layout>
