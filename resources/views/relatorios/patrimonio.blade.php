<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Inventário de Patrimônio</title>
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
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #ccc;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Inventário de Patrimônio</h2>
        <p>Clube de Desbravadores</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qtd</th>
                <th>Estado</th>
                <th>Local</th>
                <th>Valor Unit.</th>
                <th>Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itens as $item)
            <tr>
                <td>{{ $item->item }}</td>
                <td>{{ $item->quantidade }}</td>
                <td>{{ $item->estado_conservacao }}</td>
                <td>{{ $item->local_armazenamento }}</td>
                <td>R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($item->valor_estimado * $item->quantidade, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold;">VALOR TOTAL DO PATRIMÔNIO:</td>
                <td style="font-weight: bold;">R$ {{ number_format($totalValor, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>