<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { font-size: 16px; margin-bottom: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 4px; text-align: left; }
        th { background: #e2e8f0; }
    </style>
</head>
<body>
    <h1>Listado de activos fijos</h1>
    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)<th>{{ $heading }}</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
                <tr>
                    <td>{{ $asset->internal_code }}</td>
                    <td>{{ $asset->name_item }}</td>
                    <td>{{ $asset->category?->name }}</td>
                    <td>{{ $asset->supplier?->name }}</td>
                    <td>{{ $asset->location_branch }}</td>
                    <td>{{ $asset->location_floor }}</td>
                    <td>{{ $asset->location_office }}</td>
                    <td>{{ $asset->assigned_to }}</td>
                    <td>{{ $asset->assigned_department }}</td>
                    <td>{{ $asset->quantity }}</td>
                    <td>{{ number_format((float) $asset->unit_cost, 2) }}</td>
                    <td>{{ $asset->asset_status }}</td>
                    <td>{{ $asset->obsolete_disposition_status }}</td>
                    <td>{{ optional($asset->latestVerification?->verified_at)->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
