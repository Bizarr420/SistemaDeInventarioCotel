<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crear Dictamen para ') . $product->name_item }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('dictamens.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700">Contenido del Dictamen</label>
                            <textarea name="content" id="content" rows="10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                        </div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Crear Dictamen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>