<x-guest-layout>
    <!-- Header texto da caixa de login -->
    <div class="mb-8 text-center sm:text-left shadow-transparent">
        <h2 class="text-3xl font-black text-white tracking-tight mb-2 drop-shadow-md">Bem-vindo de volta</h2>
        <p class="text-slate-400 text-sm font-medium">Insira suas credenciais para acessar sua conta</p>
    </div>

    <!-- Alert / Erros (usando a session info original) -->
    @if (session('status'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm font-bold">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-500 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner" 
                    placeholder="seu.email@exemplo.com">
            </div>
            @if($errors->has('email'))
                <p class="mt-2 text-sm text-red-500 font-medium ml-1">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 ml-1">Senha</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password" 
                    class="block w-full rounded-2xl border-0 bg-black/20 text-white placeholder-slate-500 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-[#FCD116] focus:bg-white/5 transition-all text-sm py-4 pl-12 pr-4 shadow-inner" 
                    placeholder="••••••••">
            </div>
            @if($errors->has('password'))
                <p class="mt-2 text-sm text-red-500 font-medium ml-1">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between pt-2">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <div class="relative flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-white/20 bg-black/20 checked:border-transparent checked:bg-[#D9222A] transition-all">
                    <svg class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3.5 h-3.5 pointer-events-none opacity-0 peer-checked:opacity-100 text-white" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="ml-3 text-sm font-medium text-slate-300 group-hover:text-white transition-colors">Lembrar de mim</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-bold text-[#FCD116] hover:text-[#FFF] transition-colors" href="{{ route('password.request') }}">
                    Esqueceu a senha?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="w-full relative inline-flex items-center justify-center gap-3 rounded-2xl px-6 py-4 text-sm font-black text-white uppercase tracking-widest bg-gradient-to-r from-[#D9222A] to-red-600 hover:from-red-600 hover:to-[#D9222A] transition-all duration-300 shadow-xl shadow-red-900/30 overflow-hidden group">
                <span class="relative z-10 transition-transform duration-300 group-hover:-translate-y-10">Entrar no Sistema</span>
                <span class="absolute inset-0 z-10 flex items-center justify-center translate-y-10 group-hover:translate-y-0 transition-transform duration-300">
                    Acessar
                    <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
            </button>
        </div>
        
        @if (Route::has('register'))
        <div class="text-center mt-6">
            <p class="text-xs text-slate-400 font-medium">Ainda não tem conta? <a href="{{ route('register') }}" class="text-[#FCD116] font-bold hover:underline">Cadastre-se aqui</a></p>
        </div>
        @endif
    </form>
</x-guest-layout>
