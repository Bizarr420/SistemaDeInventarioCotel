<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    {{-- Dashboard --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- Productos (dropdown con subniveles) --}}
                    <div x-data="{ openMenu:false }" class="relative">
                        <button @click="openMenu = !openMenu"
                                @keydown.escape="openMenu=false"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                            Productos
                            <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.05l-4.24 4.46a.75.75 0 01-1.1 0L5.21 8.26a.75.75 0 01.02-1.05z" clip-rule="evenodd"/></svg>
                        </button>

                        <div x-cloak x-show="openMenu" @click.away="openMenu=false"
                            x-transition
                            class="absolute left-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white shadow-lg py-2 z-50">
                            <x-dropdown-link :href="route('products.index')" :active="request()->routeIs('products.index')">
                                Listado
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('products.create')" :active="request()->routeIs('products.create')">
                                Crear producto
                            </x-dropdown-link>

                            {{-- Subnivel Inventario --}}
                            <div x-data="{ openSub:false }" class="relative">
                                <button @click="openSub = !openSub"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 inline-flex items-center justify-between">
                                    Inventario
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.19 10 7.23 6.29a.75.75 0 111.06-1.06l4.46 4.24a.75.75 0 010 1.08l-4.46 4.24a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                                </button>
                                <div x-cloak x-show="openSub" x-transition
                                    class="mt-1 ml-2 rounded-lg border border-gray-200 bg-white shadow">
                                    <x-dropdown-link :href="route('movements.index')" :active="request()->routeIs('movements.index')">
                                        Movimientos
                                    </x-dropdown-link>
                                    {{-- Si tienes rutas separadas de entradas/salidas, agrega aquí --}}
                                    {{-- <x-dropdown-link :href="route('movements.in')">Entradas</x-dropdown-link> --}}
                                    {{-- <x-dropdown-link :href="route('movements.out')">Salidas</x-dropdown-link> --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Categorías --}}
                    <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                        Categorías
                    </x-nav-link>

                    {{-- Proveedores --}}
                    <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                        Proveedores
                    </x-nav-link>

                    {{-- Reportes (dropdown simple) --}}
                    <div x-data="{ openRep:false }" class="relative">
                        <button @click="openRep = !openRep" @keydown.escape="openRep=false"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                            Reportes
                            <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.05l-4.24 4.46a.75.75 0 01-1.1 0L5.21 8.26a.75.75 0 01.02-1.05z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-cloak x-show="openRep" @click.away="openRep=false" x-transition
                            class="absolute left-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white shadow-lg py-2 z-50">
                            <x-dropdown-link :href="route('reports.summary')" :active="request()->routeIs('reports.summary')">
                                Resumen
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.kardex')" :active="request()->routeIs('reports.kardex')">
                                Kardex
                            </x-dropdown-link>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            {{-- Productos (acordeón simple usando details/summary para evitar más JS) --}}
            <details class="group">
                <summary class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Productos
                </summary>
                <div class="pl-4">
                    <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')">
                        Listado
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('products.create')" :active="request()->routeIs('products.create')">
                        Crear producto
                    </x-responsive-nav-link>
                    <details class="group">
                        <summary class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Inventario
                        </summary>
                        <div class="pl-4">
                            <x-responsive-nav-link :href="route('movements.index')" :active="request()->routeIs('movements.index')">
                                Movimientos
                            </x-responsive-nav-link>
                        </div>
                    </details>
                </div>
            </details>
            <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                Categorías
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                Proveedores
            </x-responsive-nav-link>
            <details class="group">
                <summary class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Reportes
                </summary>
                <div class="pl-4">
                    <x-responsive-nav-link :href="route('reports.summary')" :active="request()->routeIs('reports.summary')">
                        Resumen
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.kardex')" :active="request()->routeIs('reports.kardex')">
                        Kardex
                    </x-responsive-nav-link>
                </div>
            </details>
        </div>
        <!-- Responsive Settings Options -->
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
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
