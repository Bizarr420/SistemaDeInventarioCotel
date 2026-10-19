<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Alta de activo fijo 2026</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950">
                Alta de simulación con fecha efectiva explícita. El registro impacta Nota 11 y dashboard desde esa fecha; los cierres 2023–2025 no se alteran. Verifique los datos antes de usar fuera del escenario.
            </div>
            <form method="POST" action="{{ route('fixed-assets.acquisitions.store') }}" class="grid grid-cols-1 gap-5 rounded-lg bg-white p-6 shadow md:grid-cols-2">
                @csrf
                <label class="text-sm font-medium">Código interno
                    <input name="internal_code" value="{{ old('internal_code') }}" maxlength="100" class="mt-1 w-full rounded border-gray-300">
                    @error('internal_code')<span class="text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium">Nombre del activo *
                    <input name="name_item" value="{{ old('name_item') }}" required maxlength="150" class="mt-1 w-full rounded border-gray-300">
                    @error('name_item')<span class="text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium md:col-span-2">Descripción
                    <textarea name="description" class="mt-1 w-full rounded border-gray-300">{{ old('description') }}</textarea>
                </label>
                <label class="text-sm font-medium">Cuenta de activo *
                    <select name="category_id" required class="mt-1 w-full rounded border-gray-300">
                        <option value="">Seleccione cuenta</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->account_code }} — {{ $category->description }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<span class="text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium">Fecha efectiva de alta *
                    <input type="date" name="effective_date" required min="2026-01-01" max="{{ now()->toDateString() }}" value="{{ old('effective_date', now()->toDateString()) }}" class="mt-1 w-full rounded border-gray-300">
                    @error('effective_date')<span class="text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium">Costo de alta (Bs) *
                    <input type="number" name="amount" required min="0.01" step="0.01" value="{{ old('amount') }}" class="mt-1 w-full rounded border-gray-300">
                    @error('amount')<span class="text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium">Sucursal
                    <input name="location_branch" value="{{ old('location_branch') }}" maxlength="120" class="mt-1 w-full rounded border-gray-300">
                </label>
                <label class="text-sm font-medium">Oficina / ubicación
                    <input name="location_office" value="{{ old('location_office') }}" maxlength="120" class="mt-1 w-full rounded border-gray-300">
                </label>
                <label class="text-sm font-medium">Responsable
                    <input name="assigned_to" value="{{ old('assigned_to') }}" maxlength="150" class="mt-1 w-full rounded border-gray-300">
                </label>
                <label class="text-sm font-medium">Departamento
                    <input name="assigned_department" value="{{ old('assigned_department') }}" maxlength="150" class="mt-1 w-full rounded border-gray-300">
                </label>
                <label class="text-sm font-medium">Vida útil simulada (años; vacío = pendiente de configurar)
                    <input type="number" name="useful_life_years" min="1" max="100" value="{{ old('useful_life_years') }}" class="mt-1 w-full rounded border-gray-300">
                    @error('useful_life_years')<span class="text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium">Valor residual (Bs)
                    <input type="number" name="residual_value" min="0" step="0.01" value="{{ old('residual_value', '0.00') }}" class="mt-1 w-full rounded border-gray-300">
                    @error('residual_value')<span class="text-red-700">{{ $message }}</span>@enderror
                </label>
                <div class="flex gap-2 md:col-span-2">
                    <button class="rounded bg-emerald-700 px-4 py-2 font-semibold text-white">Registrar alta</button>
                    <a href="{{ route('fixed-assets.index') }}" class="rounded bg-slate-200 px-4 py-2 font-semibold text-slate-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
