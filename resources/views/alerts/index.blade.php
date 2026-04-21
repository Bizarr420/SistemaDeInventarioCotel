<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Alertas de Obsolescencia') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 flex space-x-2">
                        <a href="{{ route('alerts.index') }}" class="px-4 py-2 {{ !request('status') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }} rounded">Todas</a>
                        <a href="{{ route('alerts.index', ['status' => 'unread']) }}" class="px-4 py-2 {{ request('status') === 'unread' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }} rounded">No leídas</a>
                    </div>
                    @foreach($alerts as $alert)
                        <div class="border-b border-gray-200 dark:border-gray-700 p-4 {{ $alert->is_read ? 'bg-gray-50' : 'bg-yellow-50' }}">
                            <div class="flex justify-between">
                                <div>
                                    <strong>{{ $alert->product->name_item }}</strong>
                                    <p>{{ $alert->message }}</p>
                                    <small class="text-gray-500">{{ $alert->triggered_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div>
                                    @if(!$alert->is_read)
                                        <form action="{{ route('alerts.markAsRead', $alert) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-blue-600 hover:text-blue-900">Marcar como leída</button>
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