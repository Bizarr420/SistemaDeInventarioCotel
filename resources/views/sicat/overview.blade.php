<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Resumen SICAT Integral') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-end">
                <a href="{{ route('sicat.executive') }}" class="px-4 py-2 rounded bg-slate-900 text-white mr-2">Panel ejecutivo</a>
                <a href="{{ route('sicat.overview.export') }}" class="px-4 py-2 rounded bg-red-600 text-white">Exportar PDF</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-sm text-gray-500">Activos fijos</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $fixedAssetCount }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-sm text-gray-500">Obsoletos</p>
                    <p class="text-3xl font-bold text-red-700">{{ $assetObsoleteCount }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-sm text-gray-500">Dictámenes aprobados</p>
                    <p class="text-3xl font-bold text-emerald-700">{{ $approvedDictamens }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-sm text-gray-500">Reconocimientos registrados</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $postedAdjustments }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Flujo SICAT</h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex justify-between"><span>Obsolescencia detectada</span><span class="font-semibold">{{ $assetObsoleteCount }}</span></div>
                        <div class="flex justify-between"><span>Dictámenes técnicos</span><span class="font-semibold">{{ $totalDictamens }}</span></div>
                        <div class="flex justify-between"><span>Pendientes de dictamen</span><span class="font-semibold">{{ $pendingDictamens }}</span></div>
                        <div class="flex justify-between"><span>Reconocimientos contables</span><span class="font-semibold">{{ $totalAdjustments }}</span></div>
                        <div class="flex justify-between"><span>Disposiciones finales</span><span class="font-semibold">{{ $disposalAdjustments }}</span></div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Trazabilidad patrimonial</h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex justify-between"><span>Valor técnico total</span><span class="font-semibold">{{ number_format($assetTechnicalTotal, 2) }}</span></div>
                        <div class="flex justify-between"><span>Valor contable total</span><span class="font-semibold">{{ number_format($assetAccountingTotal, 2) }}</span></div>
                        <div class="flex justify-between"><span>Brecha patrimonial</span><span class="font-semibold {{ $assetPatrimonialGap >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($assetPatrimonialGap, 2) }}</span></div>
                        <div class="flex justify-between"><span>Deterioros registrados</span><span class="font-semibold">{{ $deteriorationAdjustments }}</span></div>
                        <div class="flex justify-between"><span>Reconocidos pendientes</span><span class="font-semibold">{{ $pendingAdjustments }}</span></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Accesos directos</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('fixed-assets.index') }}" class="px-4 py-2 rounded bg-green-600 text-white">Activos</a>
                    <a href="{{ route('fixed-assets.verifications.index') }}" class="px-4 py-2 rounded bg-blue-600 text-white">Verificaciones</a>
                    <a href="{{ route('dictamens.index') }}" class="px-4 py-2 rounded bg-indigo-600 text-white">Dictámenes</a>
                    <a href="{{ route('accounting-adjustments.index') }}" class="px-4 py-2 rounded bg-emerald-700 text-white">Reconocimientos</a>
                    <a href="{{ route('reports.financial-notes') }}" class="px-4 py-2 rounded bg-slate-900 text-white">Notas financieras</a>
                    <a href="{{ route('reports.deterioration') }}" class="px-4 py-2 rounded bg-amber-600 text-white">Deterioro</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>