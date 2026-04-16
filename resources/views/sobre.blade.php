<x-app-layout>
    <x-slot name="header">Sobre o Sistema</x-slot>

    <div class="ui-page max-w-3xl mx-auto ui-animate-fade-up pb-20">
        
        {{-- Hero Card --}}
        <div class="ui-card overflow-hidden mb-8">
            <div class="p-8 sm:p-12 bg-gradient-to-br from-[#002F6C] via-[#00408f] to-blue-600 relative overflow-hidden text-center">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-white to-transparent"></div>

                <div class="relative z-10">
                    {{-- Logo DBV --}}
                    <div class="w-24 h-24 rounded-3xl bg-white/20 border-2 border-white/30 flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-4xl font-black text-white leading-none">DB</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black text-white mb-3 tracking-tight">Desbravadores Manager</h1>
                    <p class="text-blue-200 font-medium text-lg mb-6">Sistema de Gestão para Clubes de Desbravadores</p>

                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/15 border border-white/20 rounded-full text-white/80 text-sm font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Versão Atual: <strong class="ml-1">v1.3.3-beta</strong>
                    </div>
                </div>
            </div>

            {{-- Stats Rápidas --}}
            <div class="grid grid-cols-3 gap-px bg-slate-100 dark:bg-slate-800">
                @foreach([['Laravel', '12.x', 'Backend'], ['TailwindCSS', '3.x', 'UI Framework'], ['Alpine.js', '3.x', 'Interatividade']] as $tech)
                <div class="bg-white dark:bg-slate-900 px-4 py-5 text-center">
                    <p class="text-lg font-black text-slate-800 dark:text-white">{{ $tech[1] }}</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ $tech[0] }}</p>
                    <p class="text-[9px] font-bold text-slate-400 mt-0.5 opacity-60">{{ $tech[2] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Sobre o Sistema --}}
        <div class="ui-card p-6 sm:p-8 mb-6">
            <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight mb-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-[#002F6C]/10 dark:bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-[#002F6C] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                Sobre
            </h2>
            <p class="text-slate-600 dark:text-slate-400 font-medium leading-relaxed">
                O <strong class="text-slate-800 dark:text-white">Desbravadores Manager</strong> é um sistema de gestão desenvolvido especificamente para clubes de Desbravadores, proporcionando controle completo sobre membros, atividades pedagógicas, frequência, eventos e finanças do clube.
            </p>
            <p class="text-slate-600 dark:text-slate-400 font-medium leading-relaxed mt-3">
                Projetado com foco em <strong class="text-slate-800 dark:text-white">usabilidade mobile-first</strong>, o sistema permite que diretores e secretários gerenciem todas as atividades do clube de qualquer dispositivo, de forma rápida e intuitiva.
            </p>
        </div>

        {{-- Módulos Disponíveis --}}
        <div class="ui-card p-6 sm:p-8 mb-6">
            <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-[#002F6C]/10 dark:bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-[#002F6C] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </div>
                Módulos do Sistema
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach([
                    ['Secretaria', 'Cadastro de membros, unidades e perfis', 'text-blue-500', 'bg-blue-50 dark:bg-blue-500/10'],
                    ['Pedagógico', 'Classes, requisitos e especialidades', 'text-purple-500', 'bg-purple-50 dark:bg-purple-500/10'],
                    ['Frequência', 'Chamadas, pontuação e histórico', 'text-emerald-500', 'bg-emerald-50 dark:bg-emerald-500/10'],
                    ['Eventos', 'Calendário, inscrições e pagamentos', 'text-amber-500', 'bg-amber-50 dark:bg-amber-500/10'],
                    ['Financeiro', 'Caixa, mensalidades e patrimônio', 'text-red-500', 'bg-red-50 dark:bg-red-500/10'],
                    ['Relatórios', 'Documentos e exportações em PDF', 'text-slate-500', 'bg-slate-50 dark:bg-slate-800'],
                ] as $modulo)
                <div class="flex items-center gap-3 p-4 rounded-2xl {{ $modulo[3] }} border border-white/50 dark:border-white/10">
                    <div class="w-2.5 h-2.5 rounded-full {{ str_replace('text-', 'bg-', $modulo[2]) }} shrink-0"></div>
                    <div>
                        <p class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-tight">{{ $modulo[0] }}</p>
                        <p class="text-[11px] font-medium text-slate-500">{{ $modulo[1] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Contato / Suporte --}}
        <div class="ui-card p-6 sm:p-8 bg-gradient-to-br from-slate-50 to-white dark:from-slate-900 dark:to-slate-800/50">
            <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight mb-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-[#D9222A]/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-[#D9222A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                Desenvolvido com Dedicação
            </h2>
            <p class="text-slate-600 dark:text-slate-400 font-medium leading-relaxed">
                Este sistema foi desenvolvido com muito cuidado e carinho para facilitar o trabalho dos líderes de Desbravadores. Se encontrar algum problema ou tiver sugestões de melhorias, entre em contato com o administrador do sistema.
            </p>
            <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <span class="text-[#D9222A] font-black text-sm">❤️</span>
                <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest">Para a Glória de Deus e o serviço aos jovens.</p>
            </div>
        </div>

    </div>
</x-app-layout>
