@php($supplier ??= null)
@csrf
<div class="grid gap-4">
  <div>
    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
    <input id="name" name="name" type="text" value="{{ old('name', $supplier->name ?? '') }}" required
           class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
  </div>
  <div>
    <label for="contact" class="block text-sm font-medium text-gray-700">Contacto</label>
    <input id="contact" name="contact" type="text" value="{{ old('contact', $supplier->contact ?? '') }}"
           class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
    @error('contact') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
  </div>
  <div>
    <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
    <input id="phone" name="phone" type="text" value="{{ old('phone', $supplier->phone ?? '') }}"
           class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
    @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
  </div>
  <div>
    <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
    <input id="email" name="email" type="email" value="{{ old('email', $supplier->email ?? '') }}"
           class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
  </div>
</div>
<div class="mt-6 flex justify-end">
  <a href="{{ route('suppliers.index') }}" class="mr-3 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancelar</a>
  <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">Guardar</button>
</div>
