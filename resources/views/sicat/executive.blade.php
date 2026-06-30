<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.18em] text-slate-500 font-semibold">Gerencia</p>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    {{ __('Panel Ejecutivo SICAT') }}
                </h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('sicat.overview') }}" class="px-4 py-2 rounded bg-slate-900 text-white">Detalle operativo</a>
                <a href="{{ route('sicat.overview.export') }}" class="px-4 py-2 rounded bg-red-600 text-white">PDF</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 p-8 text-white shadow-2xl border border-slate-700">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <p class="text-sm uppercase tracking-[0.22em] text-slate-300">Control interno y control patrimonial</p>
                        <h1 class="mt-3 text-3xl md:text-4xl font-bold leading-tight">Visión gerencial del deterioro, obsolescencia y bajas de activos</h1>
                        <p class="mt-4 text-slate-300 max-w-2xl">Este panel consolida el estado técnico y contable de los activos, mostrando en forma ejecutiva el avance del flujo SICAT: detección, dictamen, reconocimiento contable y disposición final.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-white/10 p-4 backdrop-blur border border-white/10">
                            <p class="text-slate-300 text-sm">Activos fijos</p>
                            <p class="text-3xl font-bold">{{ $fixedAssetCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 backdrop-blur border border-white/10">
                            <p class="text-slate-300 text-sm">Obsoletos</p>
                            <p class="text-3xl font-bold">{{ $assetObsoleteCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 backdrop-blur border border-white/10">
                            <p class="text-slate-300 text-sm">Dictámenes aprobados</p>
                            <p class="text-3xl font-bold">{{ $approvedDictamens }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 backdrop-blur border border-white/10">
                            <p class="text-slate-300 text-sm">Reconocimientos</p>
                            <p class="text-3xl font-bold">{{ $postedAdjustments }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500">Brecha patrimonial</p>
                    <p class="mt-2 text-3xl font-bold {{ $assetPatrimonialGap >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($assetPatrimonialGap, 2) }}</p>
                    <p class="mt-2 text-sm text-gray-600">Diferencia entre valor técnico y contable del parque de activos.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500">Obsolescencia activa</p>
                    <p class="mt-2 text-3xl font-bold text-red-700">{{ $assetObsoleteCount }} <span class="text-base text-gray-500">({{ number_format($assetObsoleteRate, 2) }}%)</span></p>
                    <p class="mt-2 text-sm text-gray-600">Activos ya clasificados como obsoletos en la operación.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500">Bajas finales</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $disposalAdjustments }}</p>
                    <p class="mt-2 text-sm text-gray-600">Activos con disposición vendida o destruida.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Semáforo de control</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="text-gray-700">Activos operativos</span>
                            <span class="font-semibold text-green-700">{{ $assetOperationalCount }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="text-gray-700">Activos con falla</span>
                            <span class="font-semibold text-orange-700">{{ $assetFailureCount }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="text-gray-700">Activos deteriorados</span>
                            <span class="font-semibold text-amber-700">{{ $assetDeterioratedCount }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="text-gray-700">Reconocimientos pendientes</span>
                            <span class="font-semibold text-slate-900">{{ $pendingAdjustments }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones gerenciales</h3>
                    <p class="text-sm text-gray-600 mb-4">Desde esta vista se prioriza la decisión sobre activos con impacto patrimonial, la aprobación de dictámenes y el seguimiento de bajas.</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('fixed-assets.index') }}" class="px-4 py-2 rounded bg-green-600 text-white">Activos</a>
                        <a href="{{ route('dictamens.index') }}" class="px-4 py-2 rounded bg-indigo-600 text-white">Dictámenes</a>
                        <a href="{{ route('accounting-adjustments.index') }}" class="px-4 py-2 rounded bg-emerald-700 text-white">Reconocimientos</a>
                        <a href="{{ route('reports.financial-notes') }}" class="px-4 py-2 rounded bg-slate-900 text-white">Notas financieras</a>
                        <a href="{{ route('sicat.overview') }}" class="px-4 py-2 rounded bg-amber-600 text-white">Detalle completo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>