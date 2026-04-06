<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Autorização de Participação</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; line-height: 1.55; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 18px; }
        .eyebrow { color: #0f766e; text-transform: uppercase; font-weight: 700; font-size: 9px; letter-spacing: 0.12em; }
        h1 { margin: 6px 0 4px; font-size: 24px; }
        .subtitulo { color: #475569; }
        .meta { color: #64748b; font-size: 9px; margin-top: 6px; }
        .section { margin-bottom: 18px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px 16px; }
        .section h2 { margin: 0 0 10px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #0f766e; }
        .grid { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-left: -10px; }
        .grid td { width: 50%; vertical-align: top; }
        .item-label { display: block; font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 2px; }
        .item-value { font-size: 12px; font-weight: 700; color: #111827; }
        .notice { background: #f8fafc; border-left: 4px solid #0f766e; padding: 10px 12px; }
        .assinaturas { margin-top: 34px; width: 100%; border-collapse: collapse; }
        .assinaturas td { width: 50%; padding-top: 30px; vertical-align: top; }
        .linha { border-top: 1px solid #334155; margin-top: 28px; padding-top: 6px; text-align: center; font-size: 10px; color: #475569; }
        .checkboxes { margin-top: 10px; }
        .checkboxes div { margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="eyebrow">Documento oficial do clube</div>
        <h1>Autorização para Participação em Evento</h1>
        <div class="subtitulo">Termo de ciencia e autorização do responsável legal</div>
        <div class="meta">
            Clube: {{ auth()->user()?->club?->nome?? 'Clube de Desbravadores' }} |
            Emitido em {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="section">
        <h2>Dados do Evento</h2>
        <table class="grid">
            <tr>
                <td>
                    <span class="item-label">Evento</span>
                    <span class="item-value">{{ $evento->nome }}</span>
                </td>
                <td>
                    <span class="item-label">Local</span>
                    <span class="item-value">{{ $evento->local }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="item-label">Saída / Início</span>
                    <span class="item-value">{{ $evento->data_inicio?->format('d/m/Y H:i')?? '-' }}</span>
                </td>
                <td>
                    <span class="item-label">Retorno / Término</span>
                    <span class="item-value">{{ $evento->data_fim?->format('d/m/Y H:i')?? '-' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Dados do Desbravador</h2>
        <table class="grid">
            <tr>
                <td>
                    <span class="item-label">Nome completo</span>
                    <span class="item-value">{{ $desbravador->nome }}</span>
                </td>
                <td>
                    <span class="item-label">Data de nascimento</span>
                    <span class="item-value">{{ $desbravador->data_nascimento?->format('d/m/Y')?? '-' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="item-label">Unidade</span>
                    <span class="item-value">{{ $desbravador->unidade?->nome?? 'Sem unidade' }}</span>
                </td>
                <td>
                    <span class="item-label">Classe atual</span>
                    <span class="item-value">{{ $desbravador->classe?->nome?? 'Não definida' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Dados do Responsável</h2>
        <table class="grid">
            <tr>
                <td>
                    <span class="item-label">Responsável legal</span>
                    <span class="item-value">{{ $desbravador->nome_responsavel?? '-' }}</span>
                </td>
                <td>
                    <span class="item-label">Telefone para contato</span>
                    <span class="item-value">{{ $desbravador->telefone_responsavel?? $desbravador->telefone?? '-' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="item-label">Endereço</span>
                    <span class="item-value">{{ $desbravador->endereco?? '-' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Informações de Saúde</h2>
        <table class="grid">
            <tr>
                <td>
                    <span class="item-label">Tipo sanguíneo</span>
                    <span class="item-value">{{ $desbravador->tipo_sanguineo?? 'Não informado' }}</span>
                </td>
                <td>
                    <span class="item-label">Cartão SUS</span>
                    <span class="item-value">{{ $desbravador->numero_sus?? 'Não informado' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="item-label">Plano de saude</span>
                    <span class="item-value">{{ $desbravador->plano_saude?? 'Não informado' }}</span>
                </td>
                <td>
                    <span class="item-label">Medicamentos contínuos</span>
                    <span class="item-value">{{ $desbravador->medicamentos_continuos?? 'Nenhum informado' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="item-label">Alergias / observações importantes</span>
                    <span class="item-value">{{ $desbravador->alergias?? 'Nenhuma informada' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Declaração do Responsável</h2>
        <p>
            Eu, <strong>{{ $desbravador->nome_responsavel?? '____________________________________________' }}</strong>,
            responsável legal por <strong>{{ $desbravador->nome }}</strong>, autorizo sua participação no evento acima identificado.
        </p>
        <p>
            Declaro estar ciente da programação, dos horários, do local de realização e das orientações do clube.
            Confirmo que as informações de saude acima estão corretas e atualizadas.
        </p>
        <p>
            Em caso de necessidade de atendimento de urgência ou emergência, autorizo a liderança responsável a tomar
            as providências imediatas necessárias para preservar a saude e a integridade do(a) desbravador(a), inclusive
            encaminhamento para atendimento médico.
        </p>

        <div class="notice">
            O responsável deve revisar os dados deste documento antes da assinatura e informar imediatamente qualquer alteração relevante.
        </div>

        <div class="checkboxes">
            <div>[&nbsp;&nbsp;] Confirmo que li e concordo com as informações acima.</div>
            <div>[&nbsp;&nbsp;] Confirmo que o desbravador está apto para participar da atividade.</div>
        </div>
    </div>

    <table class="assinaturas">
        <tr>
            <td>
                <div class="linha">Assinatura do responsável legal</div>
            </td>
            <td>
                <div class="linha">Data</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="linha">Nome legível do responsável</div>
            </td>
            <td>
                <div class="linha">Documento do responsável</div>
            </td>
        </tr>
    </table>
</body>
</html>


