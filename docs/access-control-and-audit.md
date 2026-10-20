# Control de acceso y auditoría SICAT

## Instalación y primer acceso administrativo

1. Respaldar la base de datos y revisar que `APP_ENV`, `APP_URL`, conexión, correo y almacenamiento de sesiones correspondan al entorno correcto.
2. Aplicar las migraciones normales del proyecto con `php artisan migrate`. Esta migración añade RBAC, campos de estado y la cadena de auditoría; no reinicia ni elimina registros existentes.
3. Crear o habilitar una cuenta por el flujo institucional de registro/restablecimiento. El seeder `AdminUserSeeder` solo crea una cuenta si se definieron `SICAT_INITIAL_ADMIN_EMAIL` y `SICAT_INITIAL_ADMIN_PASSWORD`; no modifica cuentas existentes y no contiene una contraseña predeterminada.
4. Desde la consola confiable del servidor, asignar el rol inicial de superadministrador: `php artisan users:grant-superadmin correo@institucion.bo`. Confirmar el cambio en la consola. El comando revoca las sesiones anteriores de esa cuenta y registra el evento.
5. Eliminar las variables iniciales de entorno luego del aprovisionamiento y almacenar secretos de producción fuera del repositorio.

La creación pública de cuentas no acepta una selección de rol privilegiado. El rol `admin` no puede cambiar permisos del rol `superadmin`, asignarlo ni administrar la configuración de seguridad.

## Permisos, sesiones y red

Los permisos efectivos se gestionan desde **Roles y permisos**. Cada endpoint de módulo debe comprobar el permiso en servidor; ocultar botones en Blade no sustituye esa comprobación. Los valores iniciales de inactividad y retención son configuración, no un proceso de depuración automática.

El campo `TRUSTED_PROXIES` debe contener exclusivamente direcciones o rangos de proxies controlados por la institución. Déjelo vacío si la aplicación recibe tráfico directamente; no confíe en cabeceras de cliente arbitrarias. En producción, configure HTTPS, cookies seguras, almacenamiento de sesiones respaldado y correo de restablecimiento.

La aplicación registra IP observada, agente de usuario y datos estimados del navegador/dispositivo. No obtiene el nombre físico del equipo. MFA no está implementado porque el proyecto no tiene proveedor habilitado.

## Eventos y límites de integridad

Los eventos cubren autenticación, cambios en modelos auditables, operaciones administrativas y exportaciones protegidas. Las modificaciones realizadas por procesos con SQL directo deben emitir un evento explícito mediante `AuditEventService` dentro de la misma transacción.

Verifique la consistencia con `php artisan audit:verify`. El hash encadenado detecta modificaciones o eliminaciones dentro de la secuencia disponible, pero no es almacenamiento inmutable: un administrador con acceso de escritura a la base de datos puede reescribir toda la cadena o restaurar una copia anterior. Para conservar evidencia, exporte y archive periódicamente fuera del host con controles de acceso independientes. No purgue filas auditadas automáticamente hasta implementar y aprobar ese archivado.

Los valores patrimoniales y de depreciación existentes que estén identificados como simulados continúan siendo escenarios de prueba, no contabilidad oficial ni auditoría financiera.
