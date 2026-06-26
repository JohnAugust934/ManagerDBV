<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso — Desbravadores Manager</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: system-ui, sans-serif; }
        .prose h2 { margin-top: 2rem; margin-bottom: .75rem; font-size: 1.25rem; font-weight: 700; color: #002F6C; }
        .prose p, .prose li { margin-bottom: .75rem; line-height: 1.7; color: #475569; }
        .prose ul { padding-left: 1.5rem; list-style: disc; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800">

<div class="max-w-3xl mx-auto px-4 py-12">

    <div class="mb-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#002F6C] mb-4">
            <svg class="w-8 h-8 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h1 class="text-3xl font-black text-[#002F6C]">Termos de Uso</h1>
        <p class="text-slate-500 mt-2">Desbravadores Manager — Última atualização: {{ \Carbon\Carbon::parse('2026-06-21')->format('d/m/Y') }}</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 prose">

        <h2>1. Aceitação dos termos</h2>
        <p>
            Ao utilizar o <strong>Desbravadores Manager</strong>, você concorda com estes Termos de Uso
            e com a nossa <a href="{{ route('legal.privacidade') }}" class="text-[#002F6C] font-medium hover:underline">Política de Privacidade</a>.
            O acesso ao sistema é restrito a usuários convidados pelo clube.
        </p>

        <h2>2. Finalidade do sistema</h2>
        <p>
            O Desbravadores Manager é uma plataforma de gestão interna para clubes de Desbravadores,
            destinada ao controle de secretaria, pedagógico, financeiro, eventos e patrimônio.
            O uso é exclusivamente institucional.
        </p>

        <h2>3. Responsabilidades do usuário</h2>
        <ul>
            <li>Manter suas credenciais de acesso em sigilo</li>
            <li>Utilizar o sistema apenas para fins legítimos relacionados à gestão do clube</li>
            <li>Não compartilhar dados de membros com terceiros não autorizados</li>
            <li>Reportar imediatamente qualquer uso indevido ao responsável pelo clube</li>
            <li>Garantir que o consentimento dos responsáveis legais foi obtido antes de cadastrar menores</li>
        </ul>

        <h2>4. Responsabilidades do clube (Controlador de Dados)</h2>
        <p>
            O clube é o Controlador de Dados nos termos da LGPD e é responsável por:
        </p>
        <ul>
            <li>Obter e registrar o consentimento dos responsáveis legais dos membros menores de idade</li>
            <li>Atender às solicitações de exercício de direitos dos titulares</li>
            <li>Manter os dados dos membros atualizados e precisos</li>
            <li>Definir quem tem acesso ao sistema por meio dos perfis de usuário</li>
        </ul>

        <h2>5. Propriedade dos dados</h2>
        <p>
            Todos os dados inseridos no sistema pertencem ao clube. O fornecedor do sistema não
            utiliza os dados para qualquer finalidade além da operação do sistema.
        </p>

        <h2>6. Disponibilidade</h2>
        <p>
            O sistema é fornecido "como está". O clube é responsável por manter backups regulares
            dos seus dados. Não há garantia de disponibilidade ininterrupta.
        </p>

        <h2>7. Alterações nestes termos</h2>
        <p>
            Estes termos podem ser atualizados. Alterações relevantes serão comunicadas aos usuários
            cadastrados. O uso continuado do sistema após alterações implica aceitação dos novos termos.
        </p>

    </div>

    <div class="mt-8 text-center text-sm text-slate-400">
        <a href="{{ route('legal.privacidade') }}" class="hover:text-[#002F6C] font-medium transition-colors">Política de Privacidade</a>
        @auth
            <span class="mx-2">&bull;</span>
            <a href="{{ route('dashboard') }}" class="hover:text-[#002F6C] font-medium transition-colors">Voltar ao Sistema</a>
        @endauth
    </div>

</div>
</body>
</html>
