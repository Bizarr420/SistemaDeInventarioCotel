<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reconocimientos Contables SICAT</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1, h2 { margin: 0; }
        .meta { margin: 6px 0 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f4f4f4; }
        .right { text-align: right; }
        .small { font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h1>Reconocimientos Contables SICAT</h1>
    <div class="meta">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Activo</th>
                <th>Dictamen</th>
                <th>Tipo</th>
                <th class="right">Técnico</th>
                <th class="right">Contable</th>
                <th class="right">Reconocido</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($adjustments as $adjustment)
                <tr>
                    <td>{{ optional($adjustment->posted_at)->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>
                        <strong>{{ $adjustment->product->name_item ?? '-' }}</strong><br>
                        <span class="small">{{ $adjustment->product->internal_code ?? '' }}</span>
                    </td>
                    <td>{{ $adjustment->dictamen?->id ?? '-' }}</td>
                    <td>{{ $adjustment->adjustment_type === 'disposal' ? 'Baja / Disposición' : 'Deterioro' }}</td>
                    <td class="right">{{ number_format((float) $adjustment->technical_value, 2) }}</td>
                    <td class="right">{{ number_format((float) $adjustment->current_accounting_value, 2) }}</td>
                    <td class="right">{{ number_format((float) $adjustment->recognized_amount, 2) }}</td>
                    <td>{{ $adjustment->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>