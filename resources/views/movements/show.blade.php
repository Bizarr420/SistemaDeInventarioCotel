@extends('layouts.app')

@section('title', 'Detalle de movimiento')

@section('content')
  @php
    $resultLabels = [
      'in_use' => 'En uso',
      'returned' => 'Devuelto',
      'damaged' => 'Dañado',
      'under_repair' => 'En reparación',
      'lost' => 'Perdido',
      'disposed' => 'Dado de baja',
      'other' => 'Otro',
    ];
    $resultReasons = [
      'customer_damage' => 'Daño causado por cliente',
      'technical_failure' => 'Falla técnica',
      'installation_damage' => 'Daño durante instalación',
      'transport_damage' => 'Daño durante transporte',
      'other' => 'Otro',
    ];
  @endphp
  <div class="mb-5 flex items-center justify-between gap-3">
    <div>
      <a href="{{ route('movements.index') }}" class="text-sm text-blue-700 hover:underline">Volver a movimientos</a>
      <h1 class="mt-1 text-2xl font-semibold text-gray-800">Detalle de movimiento #{{ $movement->id }}</h1>
    </div>
    <span class="rounded px-3 py-1 text-sm font-semibold {{ $movement->type === 'out' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
      {{ $movement->type === 'out' ? 'Salida' : 'Entrada' }}
    </span>
  </div>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    <section class="rounded-lg bg-white p-5 shadow lg:col-span-2">
      <h2 class="mb-4 text-lg font-semibold text-gray-800">Resumen del movimiento</h2>
      <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div><dt class="text-xs uppercase text-gray-500">Fecha y hora</dt><dd class="font-medium text-gray-900">{{ $movement->created_at->format('d/m/Y H:i') }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Producto</dt><dd class="font-medium text-gray-900">{{ $movement->product->name_item }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Técnico</dt><dd class="font-medium text-gray-900">{{ $movement->technician->name ?? '—' }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Usuario que registró</dt><dd class="font-medium text-gray-900">{{ $movement->user->name ?? '—' }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Almacén</dt><dd class="font-medium text-gray-900">{{ $movement->warehouse->name }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Motivo</dt><dd class="font-medium text-gray-900">{{ $movement->reason ?? '—' }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Cantidad retirada</dt><dd class="font-medium text-gray-900">{{ $movement->quantity }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Cantidad pendiente de liquidar</dt><dd class="font-medium text-amber-700">{{ $movement->items->where('liquidation_status', 'pending')->count() }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Referencia</dt><dd class="font-medium text-gray-900">{{ $movement->reference_code ?? 'MOV-' . str_pad($movement->id, 6, '0', STR_PAD_LEFT) }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Estado del movimiento</dt><dd class="font-medium text-gray-900">{{ ['pending' => 'Pendiente', 'partially_liquidated' => 'Parcialmente liquidado', 'completed' => 'Completado', 'cancelled' => 'Cancelado', 'in_transit' => 'En tránsito', 'received' => 'Recibido'][$movement->status] ?? $movement->status }}</dd></div>
        <div><dt class="text-xs uppercase text-gray-500">Observaciones</dt><dd class="font-medium text-gray-900">{{ $movement->note ?? '—' }}</dd></div>
      </dl>
    </section>

    <aside class="rounded-lg bg-amber-50 p-5 shadow">
      <h2 class="text-lg font-semibold text-amber-900">Liquidación pendiente</h2>
      <p class="mt-2 text-sm text-amber-800">Los equipos fueron entregados al técnico. Aquí se podrá registrar posteriormente qué ocurrió con cada unidad.</p>
    </aside>
  </div>

  <section class="mt-5 overflow-x-auto rounded-lg bg-white shadow">
    <div class="border-b px-5 py-4">
      <h2 class="text-lg font-semibold text-gray-800">Detalle de los equipos</h2>
    </div>
    <form method="POST" action="{{ route('movements.liquidate', $movement) }}">
      @csrf
      @method('PATCH')
      <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-4 py-3">Equipo</th>
          @if($movement->product->tracking_mode === 'individual')
            <th class="px-4 py-3">Número de serie</th>
            <th class="px-4 py-3">MAC</th>
            <th class="px-4 py-3">Código interno</th>
            <th class="px-4 py-3">Código patrimonial</th>
          @endif
          <th class="px-4 py-3">Estado de liquidación</th>
          <th class="px-4 py-3">Resultado</th>
          <th class="px-4 py-3">Motivo</th>
          <th class="px-4 py-3">Observación</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movement->items as $item)
          <tr class="border-t">
            <td class="px-4 py-3 font-medium">Equipo {{ $item->item_number }}</td>
            @if($movement->product->tracking_mode === 'individual')
              <td class="px-4 py-3"><input name="items[{{ $item->id }}][serial_number]" value="{{ old('items.' . $item->id . '.serial_number', $item->serial_number) }}" class="w-full rounded border px-2 py-1" placeholder="Serie"></td>
              <td class="px-4 py-3"><input name="items[{{ $item->id }}][mac_address]" value="{{ old('items.' . $item->id . '.mac_address', $item->mac_address) }}" class="w-full rounded border px-2 py-1" placeholder="MAC"></td>
              <td class="px-4 py-3"><input name="items[{{ $item->id }}][internal_code]" value="{{ old('items.' . $item->id . '.internal_code', $item->internal_code) }}" class="w-full rounded border px-2 py-1" placeholder="Código interno"></td>
              <td class="px-4 py-3"><input name="items[{{ $item->id }}][patrimonial_code]" value="{{ old('items.' . $item->id . '.patrimonial_code', $item->patrimonial_code) }}" class="w-full rounded border px-2 py-1" placeholder="Código patrimonial"></td>
            @endif
            <td class="px-4 py-3"><span class="rounded bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">{{ $item->liquidation_status === 'pending' ? 'Pendiente' : $item->liquidation_status }}</span></td>
            <td class="px-4 py-3">
              @if($item->liquidation_status === 'pending')
                <select name="items[{{ $item->id }}][final_status]" data-result-select class="w-full rounded border px-2 py-1" required>
                  <option value="">Seleccionar resultado</option>
                  @foreach($resultLabels as $value => $label)
                    <option value="{{ $value }}" @selected(old('items.' . $item->id . '.final_status') === $value)>{{ $label }}</option>
                  @endforeach
                </select>
              @else
                {{ $resultLabels[$item->final_status] ?? str_replace('_', ' ', $item->final_status ?? '—') }}
              @endif
            </td>
            <td class="px-4 py-3">
              @if($item->liquidation_status === 'pending')
                <select name="items[{{ $item->id }}][result_reason]" data-reason-select class="hidden w-full rounded border px-2 py-1">
                  <option value="">Seleccionar motivo</option>
                  @foreach($resultReasons as $value => $label)
                    <option value="{{ $value }}" @selected(old('items.' . $item->id . '.result_reason') === $value)>{{ $label }}</option>
                  @endforeach
                </select>
              @else
                {{ $resultReasons[$item->result_reason] ?? '—' }}
              @endif
            </td>
            <td class="px-4 py-3">
              @if($item->liquidation_status === 'pending')
                <input name="items[{{ $item->id }}][note]" value="{{ old('items.' . $item->id . '.note', $item->note) }}" class="w-full rounded border px-2 py-1" placeholder="Observación (opcional)">
              @else
                {{ $item->note ?? '—' }}
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="{{ $movement->product->tracking_mode === 'individual' ? 9 : 6 }}" class="px-4 py-5 text-center text-gray-500">Este movimiento no tiene equipos detallados.</td></tr>
        @endforelse
      </tbody>
      </table>
      @if($movement->items->contains('liquidation_status', 'pending'))
        <div class="flex justify-end border-t px-5 py-4">
          <button type="submit" class="rounded bg-orange-600 px-4 py-2 font-medium text-white hover:bg-orange-700">Guardar liquidación</button>
        </div>
      @endif
    </form>
  </section>

  @if($movement->relatedMovements->isNotEmpty())
    <section class="mt-5 rounded-lg bg-white p-5 shadow">
      <h2 class="text-lg font-semibold text-gray-800">Movimientos relacionados</h2>
      @foreach($movement->relatedMovements as $related)
        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded border border-slate-200 p-3 text-sm">
          <div><strong>{{ $related->reference_code }}</strong><span class="ml-2 text-slate-600">Liquidación de salida registrada el {{ $related->created_at->format('d/m/Y H:i') }}</span></div>
          <span class="rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">No elimina la salida original</span>
        </div>
      @endforeach
    </section>
  @endif

  <script>
    document.querySelectorAll('[data-result-select]').forEach(function (select) {
      const updateReason = function () {
        const reason = this.closest('tr').querySelector('[data-reason-select]');
        const needsReason = ['damaged', 'other'].includes(this.value);
        reason?.classList.toggle('hidden', !needsReason);
        if (reason) reason.required = needsReason;
      };

      select.addEventListener('change', updateReason);
      updateReason.call(select);
    });
  </script>
@endsection
