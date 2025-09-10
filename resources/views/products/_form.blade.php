{{-- resources/views/products/_form.blade.php --}}
@csrf
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <x-input label="COD.INT." name="internal_code" :value="old('internal_code', $product->internal_code ?? '')" />
  <x-input label="Nro de parte" name="part_number" :value="old('part_number', $product->part_number ?? '')" />
  <x-input label="Item" name="item" :value="old('item', $product->item ?? '')" />
  <x-input label="Nombre de Ítem *" name="name_item" required :value="old('name_item', $product->name_item ?? '')" />
  <x-input label="CND" name="cnd" :value="old('cnd', $product->cnd ?? '')" />
  <x-input label="Und" name="unit" :value="old('unit', $product->unit ?? '')" />
  <x-input label="MAC" name="mac" :value="old('mac', $product->mac ?? '')" />
  <x-select label="Categoría" name="category_id" :value="old('category_id', $product->category_id ?? '')">
    <option value="">--</option>
    @foreach($categories as $c)
      <option value="{{ $c->id }}" @selected(old('category_id',$product->category_id ?? '')==$c->id)>{{ $c->name }}</option>
    @endforeach
  </x-select>
  <x-select label="Proveedor" name="supplier_id" :value="old('supplier_id', $product->supplier_id ?? '')">
    <option value="">--</option>
    @foreach($suppliers as $s)
      <option value="{{ $s->id }}" @selected(old('supplier_id',$product->supplier_id ?? '')==$s->id)>{{ $s->name }}</option>
    @endforeach
  </x-select>
  <div class="sm:col-span-2">
    <x-textarea label="Descripción" name="description">{{ old('description', $product->description ?? '') }}</x-textarea>
  </div>
  <div class="sm:col-span-2">
    <x-textarea label="Observación" name="note">{{ old('note', $product->note ?? '') }}</x-textarea>
  </div>
</div>
<button class="mt-4 px-4 py-2 rounded-lg bg-blue-600 text-white">Guardar</button>
