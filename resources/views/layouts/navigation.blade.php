<nav x-data="{ open: false }"
    class="app-nav sticky top-0 z-50 bg-white backdrop-blur supports-[backdrop-filter]:bg-white border-b border-gray-200"
>
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-x-8">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-lg">
                    <img src="{{ asset('images/Cotel.png') }}" alt="Cotel" class="h-6 w-auto sm:h-7 md:h-8 lg:h-8">
                </a>

                <!-- Navigation Links -->
                <div class="hidden sm:flex items-center gap-x-8">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Inicio') }}
                    </x-nav-link>

                    <div x-data="{ openInventory:false }" class="relative">
                        <button type="button"
                                @click="openInventory = !openInventory"
                                @keydown.escape="openInventory=false"
                                @click.outside="openInventory=false"
                                :aria-expanded="openInventory"
                                aria-haspopup="menu"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-cotel-orange focus:outline-none">
                            Inventario
                            <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.05l-4.24 4.46a.75.75 0 01-1.1 0L5.21 8.26a.75.75 0 01.02-1.05z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-cloak x-show="openInventory" x-transition
                             class="absolute left-0 mt-2 w-64 rounded-xl border border-gray-200 bg-white shadow-lg py-2 z-50"
                             role="menu">
                            <x-dropdown-link :href="route('inventory.index')" :active="request()->routeIs('inventory.index')">
                                Productos
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('inventory.create')" :active="request()->routeIs('inventory.create')">
                                Crear Producto
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('movements.index')" :active="request()->routeIs('movements.index')">
                                Salida y Devolucion de Stock
                            </x-dropdown-link>
                        </div>
                    </div>

                    <div x-data="{ openAssets:false }" class="relative">
                        <button type="button"
                                @click="openAssets = !openAssets"
                                @keydown.escape="openAssets=false"
                                @click.outside="openAssets=false"
                                :aria-expanded="openAssets"
                                aria-haspopup="menu"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-cotel-orange focus:outline-none">
                            SICAT
                            <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.05l-4.24 4.46a.75.75 0 01-1.1 0L5.21 8.26a.75.75 0 01.02-1.05z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-cloak x-show="openAssets" x-transition
                             class="absolute left-0 mt-2 w-64 rounded-xl border border-gray-200 bg-white shadow-lg py-2 z-50"
                             role="menu">
                            <x-dropdown-link :href="route('fixed-assets.index')" :active="request()->routeIs('fixed-assets.index')">
                                Listado de Activos
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('fixed-assets.create')" :active="request()->routeIs('fixed-assets.create')">
                                Crear Activo Fijo
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('fixed-assets.migration.create')" :active="request()->routeIs('fixed-assets.migration.*')">
                                Migracion
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('fixed-assets.verifications.index')" :active="request()->routeIs('fixed-assets.verifications.*')">
                                Verificacion
                            </x-dropdown-link>
                        </div>
                    </div>

                    <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                        {{ __('Proveedores') }}
                    </x-nav-link>

                    <div x-data="{ openReports:false }" class="relative">
                        <button type="button"
                                @click="openReports = !openReports"
                                @keydown.escape="openReports=false"
                                @click.outside="openReports=false"
                                :aria-expanded="openReports"
                                aria-haspopup="menu"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-cotel-orange focus:outline-none">
                            Reportes
                            <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.05l-4.24 4.46a.75.75 0 01-1.1 0L5.21 8.26a.75.75 0 01.02-1.05z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-cloak x-show="openReports" x-transition
                             class="absolute left-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white shadow-lg py-2 z-50"
                             role="menu">
                            <x-dropdown-link :href="route('reports.summary')" :active="request()->routeIs('reports.summary')">
                                Resumen
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.kardex')" :active="request()->routeIs('reports.kardex')">
                                Kardex
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.deterioration')" :active="request()->routeIs('reports.deterioration')">
                                Deterioro
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.comparative')" :active="request()->routeIs('reports.comparative')">
                                Comparativo
                            </x-dropdown-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usuario -->
            <div class="hidden sm:flex items-center gap-x-3">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button"
                                class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md text-gray-900 hover:text-cotel-orange">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburguesa -->
            <div class="sm:hidden">
                <button type="button"
                        @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none"
                        aria-controls="mobile-menu"
                        :aria-expanded="open">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú responsive -->
    <div id="mobile-menu" :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Inicio') }}
            </x-responsive-nav-link>

            <details class="group">
                <summary class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Inventario</summary>
                <div class="pl-4">
                    <x-responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.index')">
                        Productos
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('inventory.create')" :active="request()->routeIs('inventory.create')">
                        Crear Producto
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('movements.index')" :active="request()->routeIs('movements.index')">
                        Salida y Devolucion de Stock
                    </x-responsive-nav-link>
                </div>
            </details>

            <details class="group">
                <summary class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">SICAT</summary>
                <div class="pl-4">
                    <x-responsive-nav-link :href="route('fixed-assets.index')" :active="request()->routeIs('fixed-assets.index')">
                        Listado de Activos
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('fixed-assets.create')" :active="request()->routeIs('fixed-assets.create')">
                        Crear Activo Fijo
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('fixed-assets.migration.create')" :active="request()->routeIs('fixed-assets.migration.*')">
                        Migracion
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('fixed-assets.verifications.index')" :active="request()->routeIs('fixed-assets.verifications.*')">
                        Verificacion
                    </x-responsive-nav-link>
                </div>
            </details>

            <x-responsive-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                {{ __('Proveedores') }}
            </x-responsive-nav-link>

            <details class="group">
                <summary class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Reportes</summary>
                <div class="pl-4">
                    <x-responsive-nav-link :href="route('reports.summary')" :active="request()->routeIs('reports.summary')">
                        Resumen
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.kardex')" :active="request()->routeIs('reports.kardex')">
                        Kardex
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.deterioration')" :active="request()->routeIs('reports.deterioration')">
                        Deterioro
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.comparative')" :active="request()->routeIs('reports.comparative')">
                        Comparativo
                    </x-responsive-nav-link>
                </div>
            </details>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
