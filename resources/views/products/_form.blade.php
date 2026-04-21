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
  <x-input label="Fin de soporte" type="date" name="end_of_support" :value="old('end_of_support', optional($product->end_of_support)->format('Y-m-d') ?? '')" />
  <x-select label="Compatibilidad" name="compatibility_status">
    <option value="">--</option>
    <option value="compatible" @selected(old('compatibility_status',$product->compatibility_status ?? '')=='compatible')>Compatible</option>
    <option value="incompatible" @selected(old('compatibility_status',$product->compatibility_status ?? '')=='incompatible')>Incompatible</option>
    <option value="degraded" @selected(old('compatibility_status',$product->compatibility_status ?? '')=='degraded')>Degradado</option>
  </x-select>
  <x-input label="Capacidad operativa (%)" type="number" name="operational_capacity" :value="old('operational_capacity', $product->operational_capacity ?? '')" min="0" max="100" />
  <x-input label="Vida útil estimada (años)" type="number" name="useful_life_years" :value="old('useful_life_years', $product->useful_life_years ?? '')" min="0" />
  <x-input label="Fecha de adquisición" type="date" name="acquisition_date" :value="old('acquisition_date', optional($product->acquisition_date)->format('Y-m-d') ?? '')" />
  <x-input label="Valor de adquisición" type="number" step="0.01" name="acquisition_value" :value="old('acquisition_value', $product->acquisition_value ?? '')" />
  <x-input label="Valor contable actual" type="number" step="0.01" name="current_accounting_value" :value="old('current_accounting_value', $product->current_accounting_value ?? '')" />
  <x-input label="Valor técnico estimado" type="number" step="0.01" name="technical_value" :value="old('technical_value', $product->technical_value ?? '')" />
  <x-select label="Estado de obsolescencia" name="obsolescence_status">
    <option value="active" @selected(old('obsolescence_status',$product->obsolescence_status ?? '')=='active')>Activo</option>
    <option value="critical" @selected(old('obsolescence_status',$product->obsolescence_status ?? '')=='critical')>Crítico</option>
    <option value="obsolete" @selected(old('obsolescence_status',$product->obsolescence_status ?? '')=='obsolete')>Obsoleto</option>
  </x-select>
  <div class="sm:col-span-3">
    <x-textarea label="Criterios de obsolescencia adicionales (JSON)" name="obsolescence_criteria">{{ old('obsolescence_criteria', json_encode($product->obsolescence_criteria ?? [])) }}</x-textarea>
  </div>
  <div class="sm:col-span-2">
    <x-textarea label="Descripción" name="description">{{ old('description', $product->description ?? '') }}</x-textarea>
  </div>
  <div class="sm:col-span-2">
    <x-textarea label="Observación" name="note">{{ old('note', $product->note ?? '') }}</x-textarea>
  </div>
</div>
<button class="mt-4 px-4 py-2 rounded-lg bg-blue-600 text-white">Guardar</button>
