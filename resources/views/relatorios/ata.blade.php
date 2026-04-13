<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
    <title>Ata {{ $ata->id }}</title>
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
        .cards .value { font-size: 12px; font-weight: 700; color: #0f172a; }
        .panel { border: 1px solid #cbd5e1; border-radius: 12px; margin-top: 12px; overflow: hidden; }
        .panel-title { background: #0f172a; color: #fff; padding: 9px 12px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; }
        .panel-body { padding: 12px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { width: 50%; border-top: 1px solid #e2e8f0; padding: 10px 12px; vertical-align: top; }
        .grid tr:first-child td { border-top: none; }
        .field-label { display: block; color: #64748b; text-transform: uppercase; font-size: 8px; font-weight: 700; margin-bottom: 4px; }
        .field-value { color: #0f172a; font-size: 10px; }
        .conteudo { white-space: pre-line; font-size: 10.5px; color: #1e293b; line-height: 1.6; }
        .assinaturas { width: 100%; margin-top: 28px; border-collapse: collapse; }
        .assinaturas td { width: 50%; padding-top: 22px; text-align: center; }
        .assinaturas .linha { border-top: 1px solid #64748b; margin: 0 auto 6px; width: 85%; }
        .assinaturas .rotulo { color: #64748b; text-transform: uppercase; font-size: 8px; letter-spacing: 0.08em; font-weight: 700; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; text-align: right; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="eyebrow">Desbravadores Manager</div>
        <h1>Ata de Reuniao</h1>
        <div class="subtitulo">Documento oficial da secretaria</div>
        <div class="meta">
            Clube: {{ $clubeNome }} |
            Emitido em {{ $emitidoEm }} |
            Responsavel: {{ $responsavelNome }}
        </div>
    </div>

    <table class="cards">
        <tr>
            <td>
                <span class="label">Data da reuniao</span>
                <span class="value">{{ $ata->data_reuniao?->format('d/m/Y')?? 'Não informado' }}</span>
            </td>
            <td>
                <span class="label">Horario</span>
                <span class="value">
                    {{ optional($ata->hora_inicio)->format('H:i')?? 'Não informado' }}
                    @if ($ata->hora_fim)
                        as {{ optional($ata->hora_fim)->format('H:i') }}
                    @endif
                </span>
            </td>
            <td>
                <span class="label">Local</span>
                <span class="value">{{ $ata->local?: 'Não informado' }}</span>
            </td>
        </tr>
    </table>

    <div class="panel">
        <div class="panel-title">Identificacao</div>
        <div class="panel-body" style="padding: 0;">
            <table class="grid">
                <tr>
                    <td>
                        <span class="field-label">Titulo</span>
                        <span class="field-value">{{ $ata->titulo }}</span>
                    </td>
                    <td>
                        <span class="field-label">Participantes</span>
                        <span class="field-value">{{ $ata->participantes?: 'Não informado' }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-title">Conteudo da ata</div>
        <div class="panel-body">
            <div class="conteudo">{{ $ata->conteudo }}</div>
        </div>
    </div>

    <table class="assinaturas">
        <tr>
            <td>
                <div class="linha"></div>
                <div class="rotulo">Secretario(a)</div>
            </td>
            <td>
                <div class="linha"></div>
                <div class="rotulo">Diretor(a)</div>
            </td>
        </tr>
    </table>

    <div class="footer">Sistema de Gestao Desbravadores Manager</div>
</body>
</html>

