# Sistema de Inventario Cotel - Guía de Instalación

Sistema de gestión de inventario desarrollado con Laravel 12, Tailwind CSS y Alpine.js.

## Requisitos previos

Antes de instalar el proyecto, asegúrate de tener lo siguiente en tu PC:

- **Git**: para clonar el repositorio
- **PHP 8.2+**: lenguaje backend (incluye extensiones: openssl, fileinfo, zip, pdo_sqlite, sqlite3, mbstring)
- **Composer**: gestor de dependencias PHP
- **Node.js 20.x+**: para compilar assets frontend
- **SQLite 3** o **MySQL** (opcional): base de datos

## Instalación desde cero (PC nueva)

### 1. Descargar e instalar PHP 8.2

**Windows:**
- Descarga PHP 8.2 desde [windows.php.net](https://windows.php.net/downloads/releases/archives/)
- Extrae el zip a `C:\php8.2`
- Copia `C:\php8.2\php.ini-production` a `C:\php8.2\php.ini`
- Abre `C:\php8.2\php.ini` y descomenta/habilita:
  ```
  extension_dir = "ext"
  extension=openssl
  extension=fileinfo
  extension=zip
  extension=pdo_sqlite
  extension=sqlite3
  extension=mbstring
  ```
- Agrega `C:\php8.2` al PATH del sistema (Variables de entorno → PATH)
- Verifica: abre PowerShell y ejecuta `php -v` (debe mostrar PHP 8.2.x)

**Linux/macOS:**
```bash
sudo apt-get install php8.2 php8.2-cli php8.2-openssl php8.2-fileinfo php8.2-zip php8.2-sqlite3 php8.2-mbstring
```

### 2. Descargar e instalar Composer

**Windows:**
- Descarga desde [getcomposer.org](https://getcomposer.org/download/)
- Instala el ejecutable (o descarga el PHAR portable)
- Verifica: `composer --version`

**Linux/macOS:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 3. Descargar e instalar Node.js

**Windows/macOS:**
- Descarga desde [nodejs.org](https://nodejs.org/) (versión LTS 20.x o superior)
- Instala normalmente
- Verifica: `node -v` y `npm -v`

**Linux:**
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install nodejs
```

### 4. Clonar el repositorio

```bash
git clone https://github.com/Bizarr420/SistemaDeInventarioCotel.git
cd SistemaDeInventarioCotel
```

### 5. Copiar archivo de entorno

```bash
cp .env.example .env
```

Edita `.env` y configura:
```
APP_NAME="Sistema Inventario"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### 6. Instalar dependencias PHP

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
```

### 7. Generar clave de aplicación

```bash
php artisan key:generate
```

### 8. Crear base de datos SQLite

```bash
# Windows (PowerShell)
New-Item -ItemType File -Path .\database\database.sqlite -Force

# Linux/macOS
touch database/database.sqlite
```

### 9. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Esto creará las tablas y poblará datos de prueba (productos, almacenes, usuarios, etc.)

### 10. Instalar dependencias Node.js

```bash
npm install
```

### 11. Compilar assets frontend

```bash
# Desarrollo (con watch)
npm run dev

# Producción (build)
npm run build
```

### 12. Crear enlace para carpeta storage

```bash
php artisan storage:link
```

### 13. Iniciar servidor de desarrollo

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Luego abre en tu navegador: **http://127.0.0.1:8000**

## Credenciales por defecto

**Usuario admin:**
- Email: `admin@local.test`
- Contraseña: `secret1234`

## Estructura de carpetas clave

```
SistemaDeInventarioCotel/
├── app/                    # Clases PHP (Controllers, Models, etc.)
├── database/               # Migraciones y seeders
├── resources/              # Vistas Blade, CSS, JS
│   ├── views/             # Plantillas HTML
│   ├── css/               # Estilos (Tailwind)
│   └── js/                # JavaScript (Alpine.js)
├── routes/                # Definición de rutas
├── storage/               # Archivos subidos, logs
├── public/                # Archivos públicos (build de Vite)
├── .env                   # Configuración local (no subir a git)
├── composer.json          # Dependencias PHP
└── package.json           # Dependencias Node.js
```

## Desarrollo local

### Servidor en segundo plano

Para mantener el servidor corriendo sin usar la terminal:

**Windows (PowerShell):**
```powershell
Start-Process cmd -ArgumentList "/c php artisan serve --host=127.0.0.1 --port=8000" -NoNewWindow
```

**Linux/macOS:**
```bash
php artisan serve --host=127.0.0.1 --port=8000 &
```

### Compilar assets en tiempo real

Abre otra terminal en la carpeta del proyecto:
```bash
npm run dev
```

Esto vigilará cambios en archivos CSS/JS y recompilará automáticamente.

## Troubleshooting

**Error: "could not find driver" (SQLite)**
- Verifica que la extensión `pdo_sqlite` esté habilitada en `php.ini`
- Reinicia el servidor PHP

**Error: "Vite requires Node.js 20.x+"**
- Actualiza Node.js a la versión recomendada

**Error: "Class not found" después de cambios en código**
- Ejecuta: `composer dump-autoload`

**Puerto 8000 ya está en uso**
- Usa otro puerto: `php artisan serve --host=127.0.0.1 --port=8001`

## Características principales

- ✅ Gestión de productos y categorías
- ✅ Movimientos de inventario (entrada/salida)
- ✅ Múltiples almacenes
- ✅ Reportes Kardex
- ✅ Resumen de inventario
- ✅ Autenticación y autorización
- ✅ Interfaz responsiva con Tailwind CSS
- ✅ Componentes interactivos con Alpine.js


