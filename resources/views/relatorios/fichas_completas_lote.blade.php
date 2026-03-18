<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Fichas Completas dos Desbravadores</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 10px; line-height: 1.42; }
        .page-break { page-break-after: always; }
        .sheet { border: 1px solid #cbd5e1; border-radius: 18px; padding: 18px 18px 10px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 14px; }
        .eyebrow { text-transform: uppercase; letter-spacing: 0.12em; color: #0f766e; font-size: 8px; font-weight: 700; }
        h1 { margin: 6px 0 4px; font-size: 20px; line-height: 1.15; }
        .meta { color: #64748b; font-size: 9px; }
        .summary { margin-bottom: 12px; border: 1px solid #dbe4ee; border-radius: 14px; padding: 10px 12px; background: #f8fafc; }
        .grid { width: 100%; border-collapse: separate; border-spacing: 10px 10px; margin-left: -10px; }
        .grid td { width: 50%; vertical-align: top; }
        .panel { border: 1px solid #dbe4ee; border-radius: 14px; padding: 12px; background: #f8fafc; }
        .panel h2 { margin: 0 0 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #0f766e; }
        .list { margin: 0; padding: 0; list-style: none; }
        .list li { margin-bottom: 5px; }
        .chips { margin-top: 8px; }
        .chip { display: inline-block; margin: 0 6px 6px 0; padding: 4px 8px; background: #e2e8f0; border-radius: 999px; font-size: 9px; }
        table.simple { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.simple th { text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; padding-bottom: 6px; }
        table.simple td { border-top: 1px solid #e2e8f0; padding: 6px 4px 6px 0; vertical-align: top; }
        .empty { color: #64748b; font-style: italic; }
    </style>
</head>
<body>
    @foreach ($desbravadores as $desbravador)
        <div class="sheet">
            <div class="header">
                <div class="eyebrow">Ficha Completa do Desbravador</div>
                <h1>{{ $desbravador['nome'] }}</h1>
                <div class="meta">
                    Clube: {{ $clubeNome }} |
                    Emitido em {{ $emitidoEm }} |
                    Responsável: {{ $responsavelNome }}
                </div>
            </div>

            @if ($loop->first && !empty($filtros))
                <div class="summary">
                    <strong>Filtros deste lote:</strong>
                    @foreach ($filtros as $label => $valor)
                        <span>{{ $label }}: {{ $valor }}{{ !$loop->last ? ' | ' : '' }}</span>
                    @endforeach
                </div>
            @endif

            <table class="grid">
                <tr>
                    <td>
                        <div class="panel">
                            <h2>Cadastro</h2>
                            <ul class="list">
                                <li><strong>Status:</strong> {{ $desbravador['status'] }}</li>
                                <li><strong>Data de nascimento:</strong> {{ $desbravador['data_nascimento'] }} ({{ $desbravador['idade'] }})</li>
                                <li><strong>Sexo:</strong> {{ $desbravador['sexo'] }}</li>
                                <li><strong>CPF:</strong> {{ $desbravador['cpf'] }}</li>
                                <li><strong>RG:</strong> {{ $desbravador['rg'] }}</li>
                                <li><strong>Unidade:</strong> {{ $desbravador['unidade'] }}</li>
                                <li><strong>Conselheiro:</strong> {{ $desbravador['conselheiro'] }}</li>
                                <li><strong>Classe atual:</strong> {{ $desbravador['classe'] }}</li>
                                <li><strong>Progresso da classe:</strong> {{ $desbravador['progresso_classe'] }}%</li>
                            </ul>
                        </div>
                    </td>
                    <td>
                        <div class="panel">
                            <h2>Contato e Responsável</h2>
                            <ul class="list">
                                <li><strong>E-mail:</strong> {{ $desbravador['email'] }}</li>
                                <li><strong>Telefone:</strong> {{ $desbravador['telefone'] }}</li>
                                <li><strong>Endereço:</strong> {{ $desbravador['endereco'] }}</li>
                                <li><strong>Responsável:</strong> {{ $desbravador['nome_responsavel'] }}</li>
                                <li><strong>Telefone do responsável:</strong> {{ $desbravador['telefone_responsavel'] }}</li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="panel">
                            <h2>Saúde</h2>
                            <ul class="list">
                                <li><strong>Cartão SUS:</strong> {{ $desbravador['numero_sus'] }}</li>
                                <li><strong>Tipo sanguíneo:</strong> {{ $desbravador['tipo_sanguineo'] }}</li>
                                <li><strong>Plano de saúde:</strong> {{ $desbravador['plano_saude'] }}</li>
                                <li><strong>Alergias:</strong> {{ $desbravador['alergias'] }}</li>
                                <li><strong>Medicamentos continuos:</strong> {{ $desbravador['medicamentos_continuos'] }}</li>
                            </ul>
                        </div>
                    </td>
                    <td>
                        <div class="panel">
                            <h2>Especialidades</h2>
                            @if (empty($desbravador['especialidades']))
                                <div class="empty">Nenhuma especialidade vinculada.</div>
                            @else
                                <div class="chips">
                                    @foreach ($desbravador['especialidades'] as $especialidade)
                                        <span class="chip">{{ $especialidade }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="panel">
                            <h2>Eventos Vinculados</h2>
                            @if (empty($desbravador['eventos']))
                                <div class="empty">Nenhum evento vinculado.</div>
                            @else
                                <table class="simple">
                                    <thead>
                                        <tr>
                                            <th>Evento</th>
                                            <th>Período</th>
                                            <th>Pago</th>
                                            <th>Autorização</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($desbravador['eventos'] as $evento)
                                            <tr>
                                                <td>{{ $evento['nome'] }}</td>
                                                <td>{{ $evento['data'] }}</td>
                                                <td>{{ $evento['pago'] }}</td>
                                                <td>{{ $evento['autorizacao'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="panel">
                            <h2>Frequência e Pontuação</h2>
                            <ul class="list">
                                <li><strong>Registros:</strong> {{ $desbravador['frequencias']['total'] }}</li>
                                <li><strong>Presenças:</strong> {{ $desbravador['frequencias']['presencas'] }}</li>
                                <li><strong>Faltas:</strong> {{ $desbravador['frequencias']['faltas'] }}</li>
                                <li><strong>Pontos acumulados:</strong> {{ $desbravador['frequencias']['pontos'] }}</li>
                            </ul>

                            @if (!empty($desbravador['frequencias']['ultimos']))
                                <table class="simple">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Presença</th>
                                            <th>Pontos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($desbravador['frequencias']['ultimos'] as $frequencia)
                                            <tr>
                                                <td>{{ $frequencia['data'] }}</td>
                                                <td>{{ $frequencia['presenca'] }}</td>
                                                <td>{{ $frequencia['pontos'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <div class="panel">
                            <h2>Requisitos Cumpridos da Classe</h2>
                            @if (empty($desbravador['requisitos']))
                                <div class="empty">Nenhum requisito concluído registrado.</div>
                            @else
                                <table class="simple">
                                    <thead>
                                        <tr>
                                            <th>Classe</th>
                                            <th>Categoria</th>
                                            <th>Código</th>
                                            <th>Descrição</th>
                                            <th>Conclusão</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($desbravador['requisitos'] as $requisito)
                                            <tr>
                                                <td>{{ $requisito['classe'] }}</td>
                                                <td>{{ $requisito['categoria'] }}</td>
                                                <td>{{ $requisito['codigo'] }}</td>
                                                <td>{{ $requisito['descricao'] }}</td>
                                                <td>{{ $requisito['conclusao'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if (! $loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
