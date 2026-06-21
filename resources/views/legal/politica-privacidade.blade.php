<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade — Desbravadores Manager</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: system-ui, sans-serif; }
        .prose h2 { margin-top: 2rem; margin-bottom: .75rem; font-size: 1.25rem; font-weight: 700; color: #002F6C; }
        .prose h3 { margin-top: 1.5rem; margin-bottom: .5rem; font-size: 1rem; font-weight: 700; color: #334155; }
        .prose p, .prose li { margin-bottom: .75rem; line-height: 1.7; color: #475569; }
        .prose ul { padding-left: 1.5rem; list-style: disc; }
        .prose table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .prose th { background: #f1f5f9; text-align: left; padding: .5rem .75rem; font-weight: 700; color: #002F6C; border: 1px solid #e2e8f0; }
        .prose td { padding: .5rem .75rem; border: 1px solid #e2e8f0; color: #475569; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800">

<div class="max-w-3xl mx-auto px-4 py-12">

    <div class="mb-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#002F6C] mb-4">
            <svg class="w-8 h-8 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-black text-[#002F6C]">Política de Privacidade</h1>
        <p class="text-slate-500 mt-2">Desbravadores Manager — Última atualização: {{ \Carbon\Carbon::parse('2026-06-21')->format('d/m/Y') }}</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 prose">

        <h2>1. Quem somos (Controlador de Dados)</h2>
        <p>
            O <strong>Desbravadores Manager</strong> é um sistema de gestão para clubes de Desbravadores.
            Cada clube que utiliza o sistema é o <strong>Controlador de Dados</strong> responsável pelos
            dados pessoais dos seus membros, nos termos da Lei Geral de Proteção de Dados (LGPD —
            Lei 13.709/2018).
        </p>
        <p>
            O responsável técnico pelo sistema (operador) pode ser contatado pelo e-mail do administrador
            do clube, disponível no painel de configurações.
        </p>

        <h2>2. Quais dados coletamos e para qual finalidade</h2>

        <table>
            <thead>
                <tr>
                    <th>Categoria de Dado</th>
                    <th>Finalidade</th>
                    <th>Base Legal (LGPD)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nome completo, data de nascimento, sexo</td>
                    <td>Identificação do membro e controle de secretaria</td>
                    <td>Legítimo interesse / Execução de contrato</td>
                </tr>
                <tr>
                    <td>CPF, RG</td>
                    <td>Identificação civil e emissão de documentos</td>
                    <td>Obrigação legal</td>
                </tr>
                <tr>
                    <td>Endereço, telefone, e-mail</td>
                    <td>Comunicação e logística de eventos</td>
                    <td>Legítimo interesse</td>
                </tr>
                <tr>
                    <td>Dados de saúde (alergias, medicamentos, tipo sanguíneo, plano de saúde)</td>
                    <td>Segurança em eventos e acampamentos</td>
                    <td>Proteção da vida / Consentimento (Art. 11)</td>
                </tr>
                <tr>
                    <td>Foto</td>
                    <td>Identificação visual e carteirinha</td>
                    <td>Consentimento</td>
                </tr>
                <tr>
                    <td>Frequência e pontuação</td>
                    <td>Acompanhamento pedagógico e ranking</td>
                    <td>Legítimo interesse</td>
                </tr>
                <tr>
                    <td>Dados financeiros (mensalidades)</td>
                    <td>Gestão financeira do clube</td>
                    <td>Execução de contrato</td>
                </tr>
            </tbody>
        </table>

        <h2>3. Tratamento de dados de crianças e adolescentes (Art. 14)</h2>
        <p>
            Os membros Desbravadores são, em sua maioria, crianças e adolescentes menores de 18 anos.
            Nos termos do Art. 14 da LGPD, o tratamento de dados pessoais de menores exige
            <strong>consentimento específico e em destaque dado por pelo menos um dos pais ou pelo
            responsável legal</strong>.
        </p>
        <p>
            Ao realizar o cadastro de um membro, o responsável legal declara, de forma expressa,
            que autoriza o tratamento dos dados pessoais do menor para as finalidades descritas nesta
            política. Essa autorização é registrada com data, hora e identificação do responsável.
        </p>

        <h2>4. Direitos dos titulares (Art. 18)</h2>
        <p>Os titulares de dados (ou seus responsáveis legais) têm direito a:</p>
        <ul>
            <li><strong>Acesso</strong> — solicitar a confirmação e o acesso aos seus dados</li>
            <li><strong>Retificação</strong> — corrigir dados incompletos, inexatos ou desatualizados</li>
            <li><strong>Anonimização ou exclusão</strong> — solicitar a eliminação de dados desnecessários</li>
            <li><strong>Portabilidade</strong> — receber seus dados em formato estruturado (JSON/CSV)</li>
            <li><strong>Revogação do consentimento</strong> — retirar o consentimento a qualquer momento</li>
        </ul>
        <p>
            Esses direitos podem ser exercidos diretamente na tela do perfil do desbravador, na aba
            <strong>Privacidade</strong>, acessível pela secretaria do clube. Alternativamente, entre
            em contato com o responsável pelo clube.
        </p>

        <h2>5. Prazo de retenção dos dados</h2>
        <table>
            <thead>
                <tr><th>Dado</th><th>Retenção</th></tr>
            </thead>
            <tbody>
                <tr><td>Dados de membros ativos</td><td>Enquanto ativo no clube</td></tr>
                <tr><td>Dados de membros desligados</td><td>5 anos (obrigação documental)</td></tr>
                <tr><td>Dados financeiros</td><td>5 anos (obrigação fiscal)</td></tr>
                <tr><td>Logs de auditoria</td><td>Conforme configuração do sistema</td></tr>
            </tbody>
        </table>

        <h2>6. Segurança das informações (Art. 46)</h2>
        <p>Adotamos medidas técnicas e administrativas para proteger os dados pessoais, incluindo:</p>
        <ul>
            <li>Controle de acesso por perfis de usuário (master, diretor, secretaria, etc.)</li>
            <li>Isolamento de dados por clube (multi-tenancy)</li>
            <li>Registros de auditoria de operações sensíveis</li>
            <li>Backups regulares com verificação de integridade</li>
            <li>Transmissão de dados via HTTPS</li>
        </ul>

        <h2>7. Compartilhamento de dados</h2>
        <p>
            Os dados não são compartilhados com terceiros, exceto quando necessário para cumprimento
            de obrigação legal ou determinação de autoridade competente.
        </p>

        <h2>8. Contato e encarregado (DPO)</h2>
        <p>
            Para exercer seus direitos ou esclarecer dúvidas sobre o tratamento de dados, entre em
            contato com o responsável pelo seu clube de Desbravadores. As informações de contato
            constam no painel do clube.
        </p>

        <h2>9. Alterações nesta política</h2>
        <p>
            Esta política pode ser atualizada periodicamente. Alterações relevantes serão comunicadas
            aos usuários do sistema. A data da última atualização é exibida no topo desta página.
        </p>

    </div>

    <div class="mt-8 text-center text-sm text-slate-400">
        <a href="{{ route('legal.termos') }}" class="hover:text-[#002F6C] font-medium transition-colors">Termos de Uso</a>
        @auth
            <span class="mx-2">&bull;</span>
            <a href="{{ route('dashboard') }}" class="hover:text-[#002F6C] font-medium transition-colors">Voltar ao Sistema</a>
        @endauth
    </div>

</div>
</body>
</html>
