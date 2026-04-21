<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Obsolescencia</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Obsolescencia de Activos</h1>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Código Interno</th>
                <th>¿Obsoleto?</th>
                <th>Vida Útil (%)</th>
                <th>Deterioro (%)</th>
                <th>Desviación Patrimonial</th>
                <th>Fin de Soporte</th>
                <th>Compatibilidad</th>
                <th>Capacidad Op.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product['id'] }}</td>
                <td>{{ $product['name'] }}</td>
                <td>{{ $product['internal_code'] }}</td>
                <td>{{ $product['is_obsolete'] }}</td>
                <td>{{ number_format($product['useful_life_percentage'], 2) }}%</td>
                <td>{{ number_format($product['deterioration'], 2) }}%</td>
                <td>${{ number_format($product['patrimonial_deviation'], 2) }}</td>
                <td>{{ $product['end_of_support'] ?? '-' }}</td>
                <td>{{ $product['compatibility_status'] ?? '-' }}</td>
                <td>{{ $product['operational_capacity'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>