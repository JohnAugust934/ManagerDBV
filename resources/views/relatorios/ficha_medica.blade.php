<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Ficha Médica - {{ $desbravador->nome }}</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; line-height: 1.45; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 14px; }
        .eyebrow { text-transform: uppercase; letter-spacing: 0.12em; color: #0f766e; font-size: 9px; font-weight: 700; }
        h1 { margin: 6px 0 4px; font-size: 22px; }
        .meta { color: #64748b; font-size: 10px; }
        .panel { border: 1px solid #dbe4ee; border-radius: 14px; padding: 12px; background: #f8fafc; margin-bottom: 12px; }
        .panel h2 { margin: 0 0 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #0f766e; }
        .list { list-style: none; margin: 0; padding: 0; }
        .list li { margin-bottom: 6px; }
        .alert { border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; border-radius: 10px; padding: 10px; margin-top: 8px; }
        .signature { margin-top: 24px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="eyebrow">Ficha Médica de Emergência</div>
        <h1>{{ $desbravador->nome }}</h1>
        <div class="meta">
            Clube: {{ Auth::user()->club->nome ?? 'Clube de Desbravadores' }} |
            Unidade: {{ $desbravador->unidade->nome ?? 'Sem unidade' }} |
            Emitido em {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="panel">
        <h2>Dados Pessoais</h2>
        <ul class="list">
            <li><strong>Nascimento:</strong> {{ $desbravador->data_nascimento?->format('d/m/Y') ?? '-' }} ({{ $desbravador->data_nascimento?->age ?? '-' }} anos)</li>
            <li><strong>Sexo:</strong> {{ $desbravador->sexo === 'M' ? 'Masculino' : ($desbravador->sexo === 'F' ? 'Feminino' : '-') }}</li>
            <li><strong>Classe:</strong> {{ $desbravador->classe->nome ?? 'Não definida' }}</li>
            <li><strong>Responsável:</strong> {{ $desbravador->nome_responsavel ?: '-' }}</li>
            <li><strong>Telefone do responsável:</strong> {{ $desbravador->telefone_responsavel ?: '-' }}</li>
            <li><strong>Telefone do desbravador:</strong> {{ $desbravador->telefone ?: '-' }}</li>
            <li><strong>Endereço:</strong> {{ $desbravador->endereco ?: '-' }}</li>
        </ul>
    </div>

    <div class="panel">
        <h2>Informações Clínicas</h2>
        <ul class="list">
            <li><strong>Tipo sanguíneo:</strong> {{ $desbravador->tipo_sanguineo ?: '-' }}</li>
            <li><strong>Cartão SUS:</strong> {{ $desbravador->numero_sus ?: '-' }}</li>
            <li><strong>Plano de saúde:</strong> {{ $desbravador->plano_saude ?: 'Não informado' }}</li>
        </ul>

        <div class="alert">
            <strong>Alergias:</strong> {{ $desbravador->alergias ?: 'Nenhuma alergia registrada.' }}
        </div>
        <div class="alert">
            <strong>Medicamentos contínuos:</strong> {{ $desbravador->medicamentos_continuos ?: 'Nenhum medicamento registrado.' }}
        </div>
    </div>

    <div class="panel">
        <h2>Autorização Médica</h2>
        <p>
            Em caso de emergência, e não sendo possível contato imediato com os responsáveis, esta ficha serve de apoio para atendimento
            e encaminhamento médico do desbravador acima identificado.
        </p>
        <div class="signature">
            _________________________________________________<br>
            Assinatura do responsável
        </div>
    </div>
</body>
</html>
