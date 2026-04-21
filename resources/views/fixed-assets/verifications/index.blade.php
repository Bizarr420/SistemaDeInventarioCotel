<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Verificacion de Activos Fijos') }}
            </h2>
            <a href="{{ route('fixed-assets.verifications.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition ease-in-out duration-150">
                {{ __('Nueva Verificacion') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Verificacion</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deterioro</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proxima Verificacion</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verificado Por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($verifications as $verification)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $verification->asset->name_item ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ optional($verification->verified_at)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @php
                                            $statusClass = match($verification->status) {
                                                'operativo' => 'bg-green-100 text-green-800',
                                                'falla' => 'bg-red-100 text-red-800',
                                                'deteriorado' => 'bg-yellow-100 text-yellow-800',
                                                'obsoleto' => 'bg-gray-200 text-gray-900',
                                                default => 'bg-gray-100 text-gray-700'
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst($verification->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $verification->deterioration_level }}%</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ optional($verification->next_verification_at)->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $verification->verifier->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay verificaciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $verifications->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
