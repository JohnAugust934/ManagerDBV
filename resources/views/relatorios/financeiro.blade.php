<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório Financeiro</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
        }

        .total {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        .green {
            color: green;
        }

        .red {
            color: red;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Relatório de Fluxo de Caixa</h2>
        <p>Emitido em: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Tipo</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lancamentos as $item)
            <tr>
                <td>{{ $item->data_movimentacao->format('d/m/Y') }}</td>
                <td>{{ $item->descricao }}</td>
                <td>{{ $item->categoria }}</td>
                <td>{{ ucfirst($item->tipo) }}</td>
                <td class="{{ $item->tipo == 'entrada' ? 'green' : 'red' }}">
                    R$ {{ number_format($item->valor, 2, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="4" style="text-align: right;">Total Entradas:</td>
                <td class="green">R$ {{ number_format($entradas, 2, ',', '.') }}</td>
            </tr>
            <tr class="total">
                <td colspan="4" style="text-align: right;">Total Saídas:</td>
                <td class="red">R$ {{ number_format($saidas, 2, ',', '.') }}</td>
            </tr>
            <tr class="total">
                <td colspan="4" style="text-align: right;">SALDO ATUAL:</td>
                <td>R$ {{ number_format($saldo, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>