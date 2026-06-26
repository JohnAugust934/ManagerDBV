<x-guest-layout>
    <div class="mb-6 text-center sm:text-left">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#FCD116]/10 border border-[#FCD116]/20 mb-4">
            <svg class="w-7 h-7 text-[#FCD116]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1.5 drop-shadow-md">Atualização de Termos</h2>
        <p class="text-slate-400 text-sm font-medium">
            Para continuar usando o sistema, precisamos do seu aceite dos termos atualizados de uso e privacidade (LGPD).
        </p>
    </div>

    <div class="mb-5 bg-blue-500/10 border border-blue-500/20 text-blue-200 p-4 rounded-xl text-sm font-medium leading-relaxed">
        Sua conta foi criada antes da adoção dos nossos termos de proteção de dados.
        Leia e confirme abaixo para manter o acesso.
    </div>

    <form method="POST" action="{{ route('termos.aceitar.store') }}" class="space-y-5">
        @csrf

        {{-- Aceite dos Termos (LGPD Art. 7 / Art. 8) --}}
        <div class="pt-1">
            <label class="flex items-start gap-3 cursor-pointer group">
                <div class="relative mt-0.5 shrink-0">
                    <input type="checkbox" name="aceite_termos" id="aceite_termos" value="1"
                        {{ old('aceite_termos') ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-2 border-white/30 bg-white/10 text-[#FCD116] focus:ring-[#FCD116] cursor-pointer">
                </div>
                <span class="text-sm text-slate-300 font-medium leading-relaxed">
                    Li e concordo com os
                    <a href="{{ route('legal.termos') }}" target="_blank" class="font-black text-[#FCD116] hover:underline">Termos de Uso</a>
                    e a
                    <a href="{{ route('legal.privacidade') }}" target="_blank" class="font-black text-[#FCD116] hover:underline">Política de Privacidade</a>.
                    *
                </span>
            </label>
            @error('aceite_termos')
                <p class="mt-1.5 text-xs text-red-400 font-medium ml-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Botão Submit --}}
        <div class="pt-1">
            <button type="submit" class="w-full relative inline-flex items-center justify-center gap-3 rounded-2xl px-6 py-4 text-sm font-black text-white uppercase tracking-widest bg-gradient-to-r from-[#D9222A] to-red-600 hover:from-red-600 hover:to-[#D9222A] transition-all duration-300 shadow-xl shadow-red-900/30 overflow-hidden group active:scale-[0.98]">
                <span class="relative z-10 transition-transform duration-300 group-hover:-translate-y-10">Aceitar e Continuar</span>
                <span class="absolute inset-0 z-10 flex items-center justify-center translate-y-10 group-hover:translate-y-0 transition-transform duration-300">
                    Continuar
                    <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
            </button>
        </div>
    </form>

    {{-- Sair, caso não queira aceitar --}}
    <form method="POST" action="{{ route('logout') }}" class="mt-5 text-center">
        @csrf
        <button type="submit" class="text-sm font-bold text-slate-400 hover:text-white transition-colors">
            Não aceito — sair da conta
        </button>
    </form>
</x-guest-layout>
