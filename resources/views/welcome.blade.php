@vite(['resources/css/app.css', 'resources/js/app.js'])

<nav class="sticky top-0 z-50 border-b border-slate-200 dark:border-slate-800
            bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/70
            dark:bg-slate-900/80 text-slate-800 dark:text-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="h-16 flex items-center justify-between">
      <!-- Brand (más grande) -->
      <a href="{{ url('/') }}" class="flex items-center gap-2 font-bold text-xl sm:text-2xl tracking-tight">
        <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 2l9 4-9 4-9-4 9-4zm9 7-9 4-9-4v9l9 4 9-4V9z"/>
        </svg>
        Inventario
      </a>

      <!-- Desktop -->
      <ul class="hidden md:flex items-center gap-1 text-sm lg:text-base">
        <li>
          <a href="#" class="px-3 py-2 rounded-xl font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80">
            Inicio
          </a>
        </li>

        <!-- Productos -->
        <li class="relative group">
          <button class="px-3 py-2 rounded-xl font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80 flex items-center gap-1">
            Productos
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.127l3.71-3.896a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
          </button>
          <div class="invisible opacity-0 translate-y-1 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition
                      absolute left-0 mt-2 w-64 rounded-2xl border border-slate-200 dark:border-slate-800
                      bg-white dark:bg-slate-900 shadow-xl p-2 text-[0.95rem]">
            <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Listado</a>
            <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Crear producto</a>

            <div class="relative group/sub mt-1">
              <button class="flex w-full items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                Inventario
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.19 10 7.23 6.29a.75.75 0 111.06-1.06l4.46 4.24a.75.75 0 010 1.08l-4.46 4.24a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
              </button>
              <div class="invisible opacity-0 translate-x-1 group-hover/sub:visible group-hover/sub:opacity-100 group-hover/sub:translate-x-0 transition
                          absolute top-0 left-full ml-2 w-60 rounded-2xl border border-slate-200 dark:border-slate-800
                          bg-white dark:bg-slate-900 shadow-xl p-2">
                <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Ajuste de stock</a>
                <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Entradas</a>
                <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Salidas</a>
              </div>
            </div>

            <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Categorías</a>
          </div>
        </li>

        <!-- Reportes -->
        <li class="relative group">
          <button class="px-3 py-2 rounded-xl font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80 flex items-center gap-1">
            Reportes
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.127l3.71-3.896a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
          </button>
          <div class="invisible opacity-0 translate-y-1 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition
                      absolute left-0 mt-2 w-60 rounded-2xl border border-slate-200 dark:border-slate-800
                      bg-white dark:bg-slate-900 shadow-xl p-2 text-[0.95rem]">
            <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Resumen</a>
            <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Kardex</a>
            <a href="#" class="block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">Valuación</a>
          </div>
        </li>

        <!-- CTA (más notorio) -->
        <li>
          <a href="#" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm lg:text-base
                             bg-blue-600 text-white font-semibold shadow-sm hover:bg-blue-700 transition">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm7 9a7 7 0 10-14 0h14z"/></svg>
            Ingresar
          </a>
        </li>
      </ul>

      <!-- Móvil -->
      <button id="nav-toggle" class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" aria-controls="mobile-nav" aria-expanded="false">
        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>

  <!-- Móvil: tipografía un poco más grande para tocar -->
  <div id="mobile-nav" class="md:hidden hidden border-t border-slate-200 dark:border-slate-800 text-base">
    <div class="px-4 py-3 space-y-1">
      <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Inicio</a>

      <details class="group rounded-lg">
        <summary class="flex items-center justify-between px-3 py-2 cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
          <span>Productos</span>
          <svg class="w-4 h-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.05l-4.24 4.46a.75.75 0 01-1.1 0L5.21 8.26a.75.75 0 01.02-1.05z" clip-rule="evenodd"/></svg>
        </summary>
        <div class="mt-1 pl-4 space-y-1">
          <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Listado</a>
          <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Crear producto</a>

          <details class="group rounded-lg">
            <summary class="flex items-center justify-between px-3 py-2 cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
              <span>Inventario</span>
              <svg class="w-4 h-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.05l-4.24 4.46a.75.75 0 01-1.1 0L5.21 8.26a.75.75 0 01.02-1.05z" clip-rule="evenodd"/></svg>
            </summary>
            <div class="mt-1 pl-4 space-y-1">
              <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Ajuste de stock</a>
              <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Entradas</a>
              <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Salidas</a>
            </div>
          </details>

          <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Categorías</a>
        </div>
      </details>

      <details class="group rounded-lg">
        <summary class="flex items-center justify-between px-3 py-2 cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
          <span>Reportes</span>
          <svg class="w-4 h-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.05l-4.24 4.46a.75.75 0 01-1.1 0L5.21 8.26a.75.75 0 01.02-1.05z" clip-rule="evenodd"/></svg>
        </summary>
        <div class="mt-1 pl-4 space-y-1">
          <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Resumen</a>
          <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Kardex</a>
          <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Valuación</a>
        </div>
      </details>

      <a href="#" class="block px-3 py-2 rounded-lg text-center font-semibold bg-blue-600 text-white hover:bg-blue-700">
        Ingresar
      </a>
    </div>
  </div>
</nav>

@push('scripts')
<script>
  const toggleBtn = document.getElementById('nav-toggle');
  const mobileNav = document.getElementById('mobile-nav');
  if (toggleBtn && mobileNav) {
    toggleBtn.addEventListener('click', () => {
      const open = !mobileNav.classList.contains('hidden');
      mobileNav.classList.toggle('hidden');
      toggleBtn.setAttribute('aria-expanded', String(!open));
    });
  }
</script>
@endpush
