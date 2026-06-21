<style>
    .cb-wrap { width: 100%; border-collapse: collapse; margin-bottom: 0; }
    .cb-logo-cell { width: 60px; vertical-align: middle; padding-right: 14px; }
    .cb-logo { width: 54px; height: 54px; border-radius: 8px; object-fit: contain; display: block; }
    .cb-info-cell { vertical-align: middle; }
    .cb-nome { font-size: 16px; font-weight: 700; color: #0f172a; line-height: 1.2; }
    .cb-meta { font-size: 9px; color: #64748b; margin-top: 3px; letter-spacing: 0.02em; }
    .cb-divider { border: none; border-top: 2px solid #0f172a; margin: 10px 0 10px; }
</style>

<table class="cb-wrap">
    <tr>
        @if (!empty($clubeLogoBase64))
            <td class="cb-logo-cell">
                <img src="{{ $clubeLogoBase64 }}" class="cb-logo" alt="Brasão">
            </td>
        @endif
        <td class="cb-info-cell">
            <div class="cb-nome">{{ $clubeNome }}</div>
            <div class="cb-meta">
                @if (!empty($clubeCidade)){{ $clubeCidade }}@endif
                @if (!empty($clubeCidade) && !empty($clubeAssociacao)) &nbsp;•&nbsp; @endif
                @if (!empty($clubeAssociacao)){{ $clubeAssociacao }}@endif
            </div>
        </td>
    </tr>
</table>
<hr class="cb-divider">
