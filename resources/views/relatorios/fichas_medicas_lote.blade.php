<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
    <title>Fichas Médicas em Lote</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; line-height: 1.45; }
        .page-break { page-break-after: always; }
        .sheet { border: 1px solid #cbd5e1; border-radius: 18px; padding: 16px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 14px; }
        .eyebrow { text-transform: uppercase; letter-spacing: 0.12em; color: #0f766e; font-size: 9px; font-weight: 700; }
        h1 { margin: 6px 0 4px; font-size: 20px; }
        .meta { color: #64748b; font-size: 10px; }
        .panel { border: 1px solid #dbe4ee; border-radius: 14px; padding: 12px; background: #f8fafc; margin-bottom: 12px; }
        .panel h2 { margin: 0 0 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #0f766e; }
        .list { list-style: none; margin: 0; padding: 0; }
        .list li { margin-bottom: 6px; }
        .alert { border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; border-radius: 10px; padding: 10px; margin-top: 8px; }
    </style>
</head>
<body>
    @foreach ($desbravadores as $desbravador)
        <div class="sheet">
            <div class="header">
                <div class="eyebrow">Ficha Médica de Emergência</div>
                <h1>{{ $desbravador['nome'] }}</h1>
                <div class="meta">
                    Clube: {{ $clubeNome }} |
                    Unidade: {{ $desbravador['unidade'] }} |
                    Emitido em {{ $emitidoEm }}
                </div>
            </div>

            <div class="panel">
                <h2>Dados Pessoais</h2>
                <ul class="list">
                    <li><strong>Nascimento:</strong> {{ $desbravador['data_nascimento'] }} ({{ $desbravador['idade'] }})</li>
                    <li><strong>Sexo:</strong> {{ $desbravador['sexo'] }}</li>
                    <li><strong>Classe:</strong> {{ $desbravador['classe'] }}</li>
                    <li><strong>Responsavel:</strong> {{ $desbravador['nome_responsavel'] }}</li>
                    <li><strong>Telefone do responsavel:</strong> {{ $desbravador['telefone_responsavel'] }}</li>
                    <li><strong>Telefone do desbravador:</strong> {{ $desbravador['telefone'] }}</li>
                </ul>
            </div>

            <div class="panel">
                <h2>Informações Clínicas</h2>
                <ul class="list">
                    <li><strong>Tipo sanguíneo:</strong> {{ $desbravador['tipo_sanguineo'] }}</li>
                    <li><strong>Cartão SUS:</strong> {{ $desbravador['numero_sus'] }}</li>
                    <li><strong>Plano de saude:</strong> {{ $desbravador['plano_saude'] }}</li>
                </ul>
                <div class="alert"><strong>Alergias:</strong> {{ $desbravador['alergias'] }}</div>
                <div class="alert"><strong>Medicamentos continuos:</strong> {{ $desbravador['medicamentos_continuos'] }}</div>
            </div>
        </div>

        @if (! $loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>

