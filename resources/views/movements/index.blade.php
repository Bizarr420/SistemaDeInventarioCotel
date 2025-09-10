{{-- resources/views/movements/index.blade.php (form simple) --}}
<form method="post" action="{{ route('movements.store') }}" class="grid sm:grid-cols-5 gap-3 mb-4">
  @csrf
  <select name="product_id" class="border rounded px-2 py-2" required>
    <option value="">Producto…</option>
    @foreach($products as $p)
      <option value="{{ $p->id }}">{{ $p->internal_code }} - {{ $p->name_item }}</option>
    @endforeach
  </select>
  <select name="warehouse_id" class="border rounded px-2 py-2" required>
    <option value="">Almacén…</option>
    @foreach($warehouses as $w)
      <option value="{{ $w->id }}">{{ $w->code }} - {{ $w->name }}</option>
    @endforeach
  </select>
  <select name="type" class="border rounded px-2 py-2" required>
    <option value="in">Entrada</option>
    <option value="out">Salida</option>
  </select>
  <input name="quantity" type="number" min="1" class="border rounded px-2 py-2" placeholder="Cantidad" required>
  <button class="px-3 py-2 bg-blue-600 text-white rounded">Registrar</button>
  <div class="sm:col-span-5">
    <input name="note" class="border rounded px-2 py-2 w-full" placeholder="Observación (opcional)">
  </div>
</form>
