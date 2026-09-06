@extends('layouts.app')

@section('title', 'Movimientos de inventario')

@section('content')
  <div class="text-slate-800 dark:text-slate-800">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Movimientos de inventario</h1>

  @php
    $movementStatuses = [
      'pending' => ['label' => 'Pendiente'],
      'partially_liquidated' => ['label' => 'Parcialmente liquidado'],
      'completed' => ['label' => 'Completado'],
      'cancelled' => ['label' => 'Cancelado'],
      'in_transit' => ['label' => 'En tránsito'],
      'received' => ['label' => 'Recibido'],
    ];
    $transferStatuses = [
      'pending' => 'Pendiente',
      'in_transit' => 'En tránsito',
      'received' => 'Recibida',
      'cancelled' => 'Cancelada',
    ];
  @endphp

  <form method="post" action="{{ route('movements.store') }}" id="operation-form" class="mb-6 rounded-lg bg-white p-3 shadow">
    @csrf
    <input type="hidden" name="operation_type" id="operation-type-value" value="{{ old('operation_type', 'entry') }}">
    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-6">
      <select id="operation-type" class="rounded border px-2 py-2 font-medium" required>
        <option value="entry">Entrada</option>
        <option value="exit">Salida</option>
        <option value="transfer">Transferencia</option>
        <option value="return">Devolución</option>
        <option value="adjustment">Ajuste</option>
        <option value="disposal">Baja</option>
      </select>
      <select name="product_id" class="rounded border px-2 py-2" required>
        <option value="">Producto…</option>
        @foreach($products as $product)
          <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->internal_code }} - {{ $product->name_item }}</option>
        @endforeach
      </select>
      <div data-field="warehouse" class="rounded border px-2 py-2">
        <select name="warehouse_id" class="w-full outline-none" required>
          <option value="">Almacén…</option>
          @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
          @endforeach
        </select>
      </div>
      <div data-field="source" class="hidden rounded border px-2 py-2">
        <select name="source_warehouse_id" class="w-full outline-none">
          <option value="">Almacén origen…</option>
          @foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach
        </select>
      </div>
      <div data-field="destination" class="hidden rounded border px-2 py-2">
        <select name="destination_warehouse_id" class="w-full outline-none">
          <option value="">Almacén destino…</option>
          @foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach
        </select>
      </div>
      <select name="technician_id" data-field="technician" class="hidden rounded border px-2 py-2">
        <option value="">Técnico / responsable…</option>
        @foreach($technicians as $technician)<option value="{{ $technician->id }}">{{ $technician->name }}</option>@endforeach
      </select>
      <input name="quantity" type="number" min="1" class="rounded border px-2 py-2" placeholder="Cantidad" required>
      <select name="reason" data-field="reason" class="rounded border px-2 py-2">
        <option value="">Motivo…</option>
        <option value="Entrega a técnico">Entrega a técnico</option>
        <option value="Devolución">Devolución</option>
        <option value="Ajuste de inventario">Ajuste de inventario</option>
        <option value="Baja de inventario">Baja de inventario</option>
      </select>
      <input name="origin" data-field="origin" class="hidden rounded border px-2 py-2" placeholder="Procedencia">
      <select name="adjustment_direction" data-field="adjustment" class="hidden rounded border px-2 py-2">
        <option value="in">Ajuste de entrada</option><option value="out">Ajuste de salida</option>
      </select>
      <input name="note" class="rounded border px-2 py-2" placeholder="Observación (opcional)">
      <button class="rounded bg-orange-600 px-3 py-2 font-medium text-white hover:bg-orange-700">Registrar operación</button>
    </div>
  </form>

  <script>
    const operationForm = document.getElementById('operation-form');
    const operationType = document.getElementById('operation-type');
    const operationValue = document.getElementById('operation-type-value');
    const transferUrl = @json(route('transfers.store'));
    const movementUrl = @json(route('movements.store'));

    function updateOperationFields() {
      const type = operationType.value;
      operationValue.value = type;
      operationForm.action = type === 'transfer' ? transferUrl : movementUrl;
      document.querySelectorAll('[data-field]').forEach((field) => field.classList.add('hidden'));
      const show = (name) => document.querySelectorAll(`[data-field="${name}"]`).forEach((field) => field.classList.remove('hidden'));
      const requireField = (name, required) => document.querySelectorAll(`[name="${name}"]`).forEach((field) => field.required = required);

      if (type === 'transfer') {
        show('source'); show('destination');
        requireField('warehouse_id', false); requireField('source_warehouse_id', true); requireField('destination_warehouse_id', true);
      } else {
        show('warehouse');
        requireField('warehouse_id', true); requireField('source_warehouse_id', false); requireField('destination_warehouse_id', false);
      }
      if (type === 'exit') { show('technician'); requireField('technician_id', true); }
      else requireField('technician_id', false);
      if (type === 'entry') show('origin');
      if (type === 'adjustment') show('adjustment');
      const needsReason = ['entry', 'exit', 'adjustment', 'disposal'].includes(type);
      if (needsReason) show('reason');
      requireField('reason', needsReason);
      document.querySelector('[name="quantity"]').required = true;
    }
    operationType.addEventListener('change', updateOperationFields);
    updateOperationFields();
  </script>

  <form method="GET" action="{{ route('movements.index') }}" class="mb-6 rounded-lg bg-white p-3 shadow">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-semibold text-gray-800">Filtros de movimientos</h2>
      <a href="{{ route('movements.index') }}" class="text-sm text-slate-600 hover:text-blue-700 hover:underline">Limpiar filtros</a>
    </div>
    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-4">
      <input type="search" name="q" value="{{ request('q') }}" class="rounded border px-2 py-2" placeholder="Código, serie, MAC, producto o número">
      <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded border px-2 py-2" title="Fecha desde">
      <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded border px-2 py-2" title="Fecha hasta">
      <select name="operation" class="rounded border px-2 py-2">
        <option value="">Tipo de movimiento</option>
        @foreach(['entry' => 'Entrada', 'exit' => 'Salida', 'transfer' => 'Transferencia', 'return' => 'Devolución', 'adjustment' => 'Ajuste', 'disposal' => 'Baja'] as $value => $label)
          <option value="{{ $value }}" @selected(request('operation') === $value)>{{ $label }}</option>
        @endforeach
      </select>
      <select name="warehouse_id" class="rounded border px-2 py-2">
        <option value="">Almacén</option>
        @foreach($warehouses as $warehouse)
          <option value="{{ $warehouse->id }}" @selected((string) request('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
        @endforeach
      </select>
      <select name="product_id" class="rounded border px-2 py-2">
        <option value="">Producto</option>
        @foreach($products as $product)
          <option value="{{ $product->id }}" @selected((string) request('product_id') === (string) $product->id)>{{ $product->name_item }}</option>
        @endforeach
      </select>
      <select name="category_id" class="rounded border px-2 py-2">
        <option value="">Categoría</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
      </select>
      <select name="status" class="rounded border px-2 py-2">
        <option value="">Estado</option>
        @foreach($movementStatuses as $value => $status)
          @php
            $label = $status['label'];
          @endphp
          <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
      </select>
      <select name="technician_id" class="rounded border px-2 py-2">
        <option value="">Técnico / responsable</option>
        @foreach($technicians as $technician)
          <option value="{{ $technician->id }}" @selected((string) request('technician_id') === (string) $technician->id)>{{ $technician->name }}</option>
        @endforeach
      </select>
      <select name="user_id" class="rounded border px-2 py-2">
        <option value="">Usuario</option>
        @foreach($users as $user)
          <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
        @endforeach
      </select>
      <button class="rounded bg-slate-800 px-3 py-2 font-medium text-white hover:bg-slate-700">Aplicar filtros</button>
    </div>
  </form>

  <section class="mb-6 rounded-lg bg-white p-3 shadow">
    <h2 class="mb-3 text-lg font-semibold text-gray-800">Transferencia entre almacenes</h2>
    <form method="POST" action="{{ route('transfers.store') }}" class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-6">
      @csrf
      <select name="product_id" class="rounded border px-2 py-2" required>
        <option value="">Producto…</option>
        @foreach($products as $product)
          <option value="{{ $product->id }}">{{ $product->internal_code }} - {{ $product->name_item }}</option>
        @endforeach
      </select>
      <select name="source_warehouse_id" class="rounded border px-2 py-2" required>
        <option value="">Desde…</option>
        @foreach($warehouses as $warehouse)
          <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
        @endforeach
      </select>
      <select name="destination_warehouse_id" class="rounded border px-2 py-2" required>
        <option value="">Hacia…</option>
        @foreach($warehouses as $warehouse)
          <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
        @endforeach
      </select>
      <input name="quantity" type="number" min="1" class="rounded border px-2 py-2" placeholder="Cantidad" required>
      <input name="note" class="rounded border px-2 py-2" placeholder="Observación (opcional)">
      <button class="rounded bg-indigo-600 px-3 py-2 font-medium text-white hover:bg-indigo-700">Transferir</button>
    </form>
  </section>

  <section class="mb-6 overflow-x-auto rounded-lg bg-white shadow">
    <div class="border-b px-5 py-4">
      <h2 class="text-lg font-semibold text-gray-800">Transferencias</h2>
    </div>
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-3 py-2">Número</th>
          <th class="px-3 py-2">Producto</th>
          <th class="px-3 py-2">Desde</th>
          <th class="px-3 py-2">Hacia</th>
          <th class="px-3 py-2 text-right">Cantidad</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($transfers as $transfer)
          <tr class="border-t">
            <td class="px-3 py-2 font-semibold text-indigo-700">{{ $transfer->reference_code }}</td>
            <td class="px-3 py-2">{{ $transfer->product->name_item }}</td>
            <td class="px-3 py-2">{{ $transfer->sourceWarehouse->name }}</td>
            <td class="px-3 py-2">{{ $transfer->destinationWarehouse->name }}</td>
            <td class="px-3 py-2 text-right font-semibold">{{ $transfer->quantity }}</td>
            <td class="px-3 py-2">{{ $transferStatuses[$transfer->status] ?? $transfer->status }}</td>
            <td class="px-3 py-2">
              @if($transfer->status === 'in_transit')
                <form method="POST" action="{{ route('transfers.receive', $transfer) }}" class="inline">
                  @csrf
                  @method('PATCH')
                  <button class="mr-2 text-green-700 hover:underline">Recibir</button>
                </form>
                <form method="POST" action="{{ route('transfers.cancel', $transfer) }}" class="inline">
                  @csrf
                  @method('PATCH')
                  <button class="text-red-700 hover:underline">Cancelar</button>
                </form>
              @else
                <span class="text-gray-400">Sin acciones</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Aún no hay transferencias.</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-5 py-4">{{ $transfers->links() }}</div>
  </section>

  <div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-3 py-2">N.º</th>
          <th class="px-3 py-2">Fecha</th>
          <th class="px-3 py-2">Operación</th>
          <th class="px-3 py-2">Producto</th>
          <th class="px-3 py-2 text-right">Cantidad</th>
          <th class="px-3 py-2">Almacén</th>
          <th class="px-3 py-2">Responsable</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2">Usuario</th>
          <th class="px-3 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movements as $movementRow)
          @php
            $operation = $movementRow->transfer_id
              ? 'Transferencia'
              : ($movementRow->reason === 'Devolución' ? 'Devolución' : ($movementRow->reason === 'Ajuste de inventario' ? 'Ajuste' : ($movementRow->status === 'disposed' ? 'Baja' : ($movementRow->type === 'in' ? 'Entrada' : 'Salida'))));
            $warehouseLabel = $movementRow->transfer
              ? $movementRow->transfer->sourceWarehouse->name . ' → ' . $movementRow->transfer->destinationWarehouse->name
              : $movementRow->warehouse->name;
            $responsible = $movementRow->technician->name ?? $movementRow->transfer?->creator?->name ?? '—';
            $statusLabel = match ($movementRow->status) {
              'pending' => 'Pendiente',
              'partially_liquidated' => 'Parcialmente liquidado',
              'in_transit' => 'En tránsito',
              'received' => 'Recibido',
              'completed' => 'Completado',
              'cancelled' => 'Cancelada',
              default => $movementStatuses[$movementRow->status]['label'] ?? $movementRow->status,
            };
            $statusClass = match ($movementRow->status) {
              'pending', 'partially_liquidated' => 'bg-amber-100 text-amber-800',
              'in_transit' => 'bg-blue-100 text-blue-800',
              'cancelled' => 'bg-red-100 text-red-800',
              default => 'bg-green-100 text-green-800',
            };
          @endphp
          <tr class="cursor-pointer border-t hover:bg-slate-50" onclick="window.location='{{ route('movements.show', $movementRow) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') window.location='{{ route('movements.show', $movementRow) }}'" tabindex="0" role="link">
            <td class="px-3 py-2 font-semibold text-blue-700">{{ $movementRow->reference_code ?? 'MOV-' . str_pad($movementRow->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td class="px-3 py-2">{{ $movementRow->created_at->format('d/m/Y') }}</td>
            <td class="px-3 py-2">{{ $operation }}</td>
            <td class="px-3 py-2 font-medium">{{ $movementRow->product->name_item }}</td>
            <td class="px-3 py-2 text-right font-semibold">{{ $movementRow->quantity }}</td>
            <td class="px-3 py-2">{{ $warehouseLabel }}</td>
            <td class="px-3 py-2">{{ $responsible }}</td>
            <td class="px-3 py-2"><span class="inline-flex rounded px-2 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
            <td class="px-3 py-2">{{ $movementRow->user->name ?? '—' }}</td>
            <td class="px-3 py-2"><a class="font-medium text-blue-700 hover:underline" href="{{ route('movements.show', $movementRow) }}">Ver detalle</a></td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="px-3 py-4 text-center text-gray-500">Aún no se registraron movimientos.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $movements->links() }}
  </div>
  </div>
@endsection
