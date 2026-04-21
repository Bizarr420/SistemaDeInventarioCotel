<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dictámenes Técnicos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <a href="{{ route('products.index') }}" class="mb-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Ver Productos</a>
                    @foreach($dictamens as $dictamen)
                        <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex justify-between">
                                <div>
                                    <strong>{{ $dictamen->product->name_item }}</strong>
                                    <p>{{ Str::limit($dictamen->content, 100) }}</p>
                                    <small class="text-gray-500">Creado por {{ $dictamen->user->name }} el {{ $dictamen->created_at->format('d/m/Y') }}</small>
                                    <span class="ml-2 px-2 py-1 text-xs rounded {{ $dictamen->status === 'approved' ? 'bg-green-200' : 'bg-yellow-200' }}">{{ $dictamen->status }}</span>
                                </div>
                                <div>
                                    @if($dictamen->status === 'draft' && in_array(auth()->user()->role, ['contable', 'auditor']))
                                        <form action="{{ route('dictamens.approve', $dictamen) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-green-600 hover:text-green-900">Aprobar</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>