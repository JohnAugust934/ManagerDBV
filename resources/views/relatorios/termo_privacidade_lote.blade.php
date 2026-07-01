<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
    <title>Termos de Privacidade — Lote</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; line-height: 1.55; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 18px; }
        .eyebrow { color: #0f766e; text-transform: uppercase; font-weight: 700; font-size: 9px; letter-spacing: 0.12em; }
        h1 { margin: 6px 0 4px; font-size: 22px; }
        .subtitulo { color: #475569; }
        .meta { color: #64748b; font-size: 9px; margin-top: 6px; }
        .page-break { page-break-after: always; }
        .section { margin-bottom: 16px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 12px 16px; }
        .section h2 { margin: 0 0 10px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #0f766e; }
        .grid { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-left: -10px; }
        .grid td { width: 50%; vertical-align: top; }
        .item-label { display: block; font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 2px; }
        .item-value { font-size: 12px; font-weight: 700; color: #111827; }
        .termo { font-size: 10px; line-height: 1.5; }
        .termo h3 { font-size: 13px; margin: 0 0 6px; }
        .termo p { margin: 0 0 6px; }
        .assinaturas { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .assinaturas td { width: 50%; padding-top: 34px; vertical-align: top; }
        .linha { border-top: 1px solid #334155; margin-top: 20px; padding-top: 6px; text-align: center; font-size: 10px; color: #475569; }
        .controle { margin-top: 16px; font-size: 8px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    @forelse ($desbravadores as $desbravador)
        <div class="{{ ! $loop->last ? 'page-break' : '' }}">
            <div class="header">
                @include('relatorios._club_brand')
                <div class="eyebrow">Documento oficial do clube</div>
                <h1>Termo de Privacidade (LGPD)</h1>
                <div class="subtitulo">Consentimento do responsável legal para tratamento de dados</div>
                <div class="meta">Emitido em {{ $emitidoEm }} &bull; Versão do termo: {{ $versao }}</div>
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
                            <span class="item-value">{{ $desbravador->data_nascimento?->format('d/m/Y') ?? '-' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="item-label">Unidade</span>
                            <span class="item-value">{{ $desbravador->unidade?->nome ?? 'Sem unidade' }}</span>
                        </td>
                        <td>
                            <span class="item-label">Responsável legal</span>
                            <span class="item-value">{{ $desbravador->nome_responsavel ?: '-' }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="section termo">
                {!! $termoHtml !!}
            </div>

            <table class="assinaturas">
                <tr>
                    <td><div class="linha">Assinatura do responsável legal</div></td>
                    <td><div class="linha">Local e data</div></td>
                </tr>
            </table>

            <div class="controle">
                Controle: DBV-{{ $desbravador->id }}-{{ now()->format('YmdHis') }}
            </div>
        </div>
    @empty
        <p>Nenhum desbravador selecionado.</p>
    @endforelse
</body>
</html>
