<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumen SICAT</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        h1, h2, h3 { margin: 0; }
        .meta { margin: 6px 0 14px; color: #6b7280; }
        .grid { width: 100%; border-collapse: collapse; }
        .card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; margin: 4px 0; }
        .section { margin-top: 16px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 8px; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>Resumen SICAT Integral</h1>
    <div class="meta">Generado el {{ now()->format('d/m/Y H:i') }}</div>

    <div class="card">
        <div class="row"><span>Activos fijos</span><strong>{{ $fixedAssetCount }}</strong></div>
        <div class="row"><span>Obsoletos</span><strong>{{ $assetObsoleteCount }}</strong></div>
        <div class="row"><span>Dictámenes aprobados</span><strong>{{ $approvedDictamens }}</strong></div>
        <div class="row"><span>Ajustes SICAT registrados</span><strong>{{ $postedAdjustments }}</strong></div>
    </div>

    <div class="section">
        <div class="section-title">Flujo SICAT</div>
        <div class="card">
            <div class="row"><span>Obsolescencia detectada</span><strong>{{ $assetObsoleteCount }}</strong></div>
            <div class="row"><span>Dictámenes técnicos</span><strong>{{ $totalDictamens }}</strong></div>
            <div class="row"><span>Pendientes de dictamen</span><strong>{{ $pendingDictamens }}</strong></div>
            <div class="row"><span>Ajustes SICAT</span><strong>{{ $totalAdjustments }}</strong></div>
            <div class="row"><span>Disposiciones finales</span><strong>{{ $disposalAdjustments }}</strong></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Trazabilidad patrimonial</div>
        <div class="card">
            <div class="row"><span>Valor técnico total</span><strong>{{ number_format($assetTechnicalTotal, 2) }}</strong></div>
            <div class="row"><span>Valor bruto simulado al corte</span><strong>{{ number_format($assetAcquisitionTotal, 2) }}</strong></div>
            <div class="row"><span>Depreciación acumulada simulada</span><strong>{{ number_format($assetDepreciationTotal, 2) }}</strong></div>
            <div class="row"><span>Valor neto del escenario</span><strong>{{ number_format($assetAccountingTotal, 2) }}</strong></div>
            <div class="row"><span>Diferencia técnica frente al neto simulado</span><strong>{{ number_format($assetPatrimonialGap, 2) }}</strong></div>
            <div class="row"><span>Deterioros registrados</span><strong>{{ $deteriorationAdjustments }}</strong></div>
            <div class="row"><span>Ajustes SICAT pendientes</span><strong>{{ $pendingAdjustments }}</strong></div>
        </div>
    </div>

    <div class="section muted">
        Escenario SICAT simulado al {{ \Illuminate\Support\Carbon::parse($assetLedgerSummary['cutoff_date'])->format('d/m/Y') }}.
        Los valores de ficha y las depreciaciones simuladas no constituyen saldos del mayor contable.
        Los ajustes SICAT tampoco son asientos: la base no almacena cuentas contables ni débitos y créditos.
    </div>
</body>
</html>