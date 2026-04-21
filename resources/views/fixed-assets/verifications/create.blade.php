<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva Verificacion de Activo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('fixed-assets.verifications.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700">Activo Fijo</label>
                                <select name="product_id" id="product_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="">Seleccionar activo</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('product_id', $selectedAssetId) == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name_item }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="verified_at" class="block text-sm font-medium text-gray-700">Fecha de Verificacion</label>
                                <input type="date" name="verified_at" id="verified_at" value="{{ old('verified_at', now()->toDateString()) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500" />
                                @error('verified_at') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                                <select name="status" id="status" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="">Seleccionar estado</option>
                                    <option value="operativo" {{ old('status') == 'operativo' ? 'selected' : '' }}>Operativo</option>
                                    <option value="falla" {{ old('status') == 'falla' ? 'selected' : '' }}>Con falla</option>
                                    <option value="deteriorado" {{ old('status') == 'deteriorado' ? 'selected' : '' }}>Deteriorado</option>
                                    <option value="obsoleto" {{ old('status') == 'obsoleto' ? 'selected' : '' }}>Obsoleto</option>
                                </select>
                                @error('status') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="deterioration_level" class="block text-sm font-medium text-gray-700">Nivel de Deterioro (%)</label>
                                <input type="number" name="deterioration_level" id="deterioration_level" min="0" max="100" value="{{ old('deterioration_level', 0) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500" />
                                @error('deterioration_level') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="next_verification_at" class="block text-sm font-medium text-gray-700">Proxima Fecha de Verificacion</label>
                                <input type="date" name="next_verification_at" id="next_verification_at" value="{{ old('next_verification_at') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500" />
                                @error('next_verification_at') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Observaciones / Detalle de Falla</label>
                                <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500">{{ old('notes') }}</textarea>
                                @error('notes') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('fixed-assets.verifications.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                                Guardar Verificacion
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
