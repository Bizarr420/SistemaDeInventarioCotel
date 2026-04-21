<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cumplimiento COSO 2013') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-6">Marco de Control Interno COSO 2013</h3>
                    
                    <!-- Componentes COSO Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- 1. Ambiente de Control -->
                        <div class="border-2 border-blue-200 rounded-lg p-4 hover:shadow-lg transition">
                            <h4 class="font-bold text-blue-600 mb-2">1. Ambiente de Control</h4>
                            <p class="text-sm text-gray-700 mb-4">Establece el fundamento del sistema de control interno mediante integridad, valores éticos y competencia.</p>
                            <a href="{{ route('coso.controls.index', ['component' => 'environment_control']) }}" class="text-blue-600 hover:underline">Ver controles →</a>
                        </div>

                        <!-- 2. Evaluación de Riesgos -->
                        <div class="border-2 border-yellow-200 rounded-lg p-4 hover:shadow-lg transition">
                            <h4 class="font-bold text-yellow-600 mb-2">2. Evaluación de Riesgos</h4>
                            <p class="text-sm text-gray-700 mb-4">Identifica y evalúa riesgos relevantes para lograr los objetivos organizacionales.</p>
                            <a href="{{ route('coso.risks.index') }}" class="text-yellow-600 hover:underline">Ver riesgos →</a>
                        </div>

                        <!-- 3. Actividades de Control -->
                        <div class="border-2 border-green-200 rounded-lg p-4 hover:shadow-lg transition">
                            <h4 class="font-bold text-green-600 mb-2">3. Actividades de Control</h4>
                            <p class="text-sm text-gray-700 mb-4">Se establecen políticas y procedimientos para mitigar riesgos identificados.</p>
                            <a href="{{ route('coso.controls.index', ['component' => 'control_activities']) }}" class="text-green-600 hover:underline">Ver controles →</a>
                        </div>

                        <!-- 4. Información y Comunicación -->
                        <div class="border-2 border-purple-200 rounded-lg p-4 hover:shadow-lg transition">
                            <h4 class="font-bold text-purple-600 mb-2">4. Información y Comunicación</h4>
                            <p class="text-sm text-gray-700 mb-4">Comunica responsabilidades de control y facilita gestión de información.</p>
                            <a href="{{ route('coso.controls.index', ['component' => 'information_communication']) }}" class="text-purple-600 hover:underline">Ver controles →</a>
                        </div>

                        <!-- 5. Supervisión -->
                        <div class="border-2 border-red-200 rounded-lg p-4 hover:shadow-lg transition">
                            <h4 class="font-bold text-red-600 mb-2">5. Supervisión</h4>
                            <p class="text-sm text-gray-700 mb-4">Evalúa la efectividad y validez del sistema de control interno.</p>
                            <a href="{{ route('coso.controls.index', ['component' => 'monitoring']) }}" class="text-red-600 hover:underline">Ver controles →</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas COSO -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Total Controles</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalControls }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Controles Activos</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $activeControls }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Efectivos (Alto)</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $effectiveControls }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-800">% Conformidad</h3>
                    <p class="text-3xl font-bold text-orange-600">{{ $totalControls > 0 ? round(($effectiveControls / $totalControls) * 100) : 0 }}%</p>
                </div>
            </div>

            <!-- Controles por Componente -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-4">Controles por Componente</h3>
                        <div class="space-y-3">
                            @foreach($controlsByComponent as $item)
                                <div class="flex justify-between items-center border-b pb-3">
                                    <span class="font-medium">{{ \App\Models\CosoControl::getComponentLabel($item->component) }}</span>
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-bold">{{ $item->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-4">Controles por Estado</h3>
                        <div class="space-y-3">
                            @foreach($controlsByStatus as $item)
                                <div class="flex justify-between items-center border-b pb-3">
                                    <span class="font-medium capitalize">{{ $item->status }}</span>
                                    <span class="px-3 py-1 rounded-full font-bold
                                        @if($item->status == 'active') bg-green-100 text-green-800
                                        @elseif($item->status == 'inactive') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">{{ $item->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>