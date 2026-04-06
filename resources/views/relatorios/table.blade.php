<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 28px 24px 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 10.5px; line-height: 1.45; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .eyebrow { color: #0f766e; text-transform: uppercase; font-weight: 700; font-size: 9px; letter-spacing: 0.12em; }
        .header h1 { margin: 6px 0 4px; font-size: 22px; line-height: 1.1; }
        .subtitulo { color: #475569; margin-bottom: 6px; }
        .meta { color: #64748b; font-size: 9px; }
        .cards { width: 100%; margin: 14px 0 10px; border-collapse: separate; border-spacing: 10px 0; margin-left: -10px; }
        .cards td { width: 33.33%; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 12px; vertical-align: top; }
        .cards .label { display: block; color: #64748b; text-transform: uppercase; font-size: 8px; font-weight: 700; margin-bottom: 4px; }
        .cards .value { font-size: 13px; font-weight: 700; color: #0f172a; }
        .filters { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 12px; margin-bottom: 16px; }
        .filters h2 { margin: 0 0 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #0f766e; }
        .filters ul { margin: 0; padding: 0; list-style: none; }
        .filters li { margin-bottom: 4px; }
        table.report { width: 100%; border-collapse: collapse; }
        table.report th { background: #0f172a; color: #fff; padding: 9px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.06em; text-align: left; }
        table.report td { border-bottom: 1px solid #dbe4ee; padding: 8px; vertical-align: top; }
        table.report tbody tr:nth-child(even) { background: #f8fafc; }
        .empty { margin-top: 28px; border: 1px dashed #94a3b8; border-radius: 14px; padding: 30px; text-align: center; color: #475569; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; text-align: right; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="eyebrow">Desbravadores Manager</div>
        <h1>{{ $titulo }}</h1>
        <div class="subtitulo">{{ $subtitulo }}</div>
        <div class="meta">
            Clube: {{ $clubeNome }} |
            Emitido em {{ $emitidoEm }} |
            Responsável: {{ $responsavelNome }}
        </div>
    </div>

    @if (!empty($metricas))
        <table class="cards">
            <tr>
                @foreach ($metricas as $metrica)
                    <td>
                        <span class="label">{{ $metrica['label'] }}</span>
                        <span class="value">{{ $metrica['value'] }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if (!empty($filtros))
        <div class="filters">
            <h2>Filtros aplicados</h2>
            <ul>
                @foreach ($filtros as $label => $valor)
                    <li><strong>{{ $label }}:</strong> {{ $valor }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (empty($linhas))
        <div class="empty">Nenhum registro encontrado com os filtros selecionados.</div>
    @else
        <table class="report">
            <thead>
                <tr>
                    @foreach ($colunas as $coluna)
                        <th>{{ $coluna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($linhas as $linha)
                    <tr>
                        @foreach ($linha as $valor)
                            <td>{{ $valor }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">Sistema de Gestao Desbravadores Manager</div>
</body>
</html>

