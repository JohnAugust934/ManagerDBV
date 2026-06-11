<x-guest-layout>
    <div class="mb-6 text-center sm:text-left">
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1.5 drop-shadow-md">Verifique seu Email</h2>
        <p class="text-slate-300 text-sm font-medium leading-relaxed">
            Obrigado por se cadastrar! Antes de começar, confirme seu endereço de email clicando no link
            que acabamos de enviar. Se não recebeu o email, podemos enviar outro.
        </p>
    </div>

    {{-- Status --}}
    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 p-4 rounded-xl text-sm font-bold">
            Um novo link de verificação foi enviado para o email informado no cadastro.
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="w-full sm:w-auto relative inline-flex items-center justify-center gap-3 rounded-2xl px-6 py-4 text-sm font-black text-white uppercase tracking-widest bg-gradient-to-r from-[#D9222A] to-red-600 hover:from-red-600 hover:to-[#D9222A] transition-all duration-300 shadow-xl shadow-red-900/30 overflow-hidden group active:scale-[0.98]">
                <span class="relative z-10">Reenviar Email</span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="w-full sm:w-auto text-sm font-bold text-slate-300 hover:text-white underline underline-offset-4 transition-colors rounded-md py-2">
                Sair
            </button>
        </form>
    </div>
</x-guest-layout>
