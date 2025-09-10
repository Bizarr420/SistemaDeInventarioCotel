{{-- resources/views/products/index.blade.php (extracto) --}}
<table class="min-w-full text-sm">
  <thead>
    <tr class="bg-slate-50">
      <th class="px-3 py-2 text-left">COD.INT.</th>
      <th class="px-3 py-2 text-left">Nro de parte</th>
      <th class="px-3 py-2 text-left">Item</th>
      <th class="px-3 py-2 text-left">Nombre de Ítem</th>
      <th class="px-3 py-2 text-left">Und</th>
      <th class="px-3 py-2 text-center">Stock total</th>
      <th class="px-3 py-2 text-left">Por almacén</th>
      <th class="px-3 py-2"></th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $p)
      <tr class="border-t">
        <td class="px-3 py-2">{{ $p->internal_code }}</td>
        <td class="px-3 py-2">{{ $p->part_number }}</td>
        <td class="px-3 py-2">{{ $p->item }}</td>
        <td class="px-3 py-2">{{ $p->name_item }}</td>
        <td class="px-3 py-2">{{ $p->unit }}</td>
        <td class="px-3 py-2 text-center font-semibold">{{ $p->total_stock }}</td>
        <td class="px-3 py-2">
          @foreach($p->stocks as $st)
            <span class="inline-block rounded bg-slate-100 px-2 py-0.5 mr-1">
              {{ $st->warehouse->code }}: {{ $st->current_stock }}
            </span>
          @endforeach
        </td>
        <td class="px-3 py-2 text-right">
          <a class="text-blue-600" href="{{ route('products.edit',$p) }}">Editar</a>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
