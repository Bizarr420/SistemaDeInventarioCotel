<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username')->nullable()->unique();
            $table->string('phone', 40)->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('access_expires_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_logout_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('session_version')->default(0);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module', 80);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('system_settings', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->text('value');
            $table->timestamps();
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->string('module', 80)->nullable()->index();
            $table->string('action', 80)->nullable()->index();
            $table->string('entity_type', 160)->nullable()->index();
            $table->string('entity_id', 80)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('result', 20)->default('success')->index();
            $table->text('reason')->nullable();
            $table->string('browser', 120)->nullable();
            $table->string('operating_system', 120)->nullable();
            $table->string('device_type', 40)->nullable();
            $table->string('correlation_id', 80)->nullable()->index();
            $table->string('route_name', 160)->nullable();
            $table->json('context')->nullable();
            $table->string('previous_hash', 64)->nullable();
            $table->string('event_hash', 64)->nullable()->index();
        });

        $roleDefinitions = [
            ['name' => 'Superadministrador', 'slug' => 'superadmin', 'description' => 'Administración global del sistema.'],
            ['name' => 'Administrador', 'slug' => 'admin', 'description' => 'Administración de módulos expresamente autorizados.'],
            ['name' => 'Contabilidad', 'slug' => 'contable', 'description' => 'Consultas y operaciones contables autorizadas.'],
            ['name' => 'Responsable de activos', 'slug' => 'tecnico', 'description' => 'Gestión operativa de activos fijos.'],
            ['name' => 'Almacén', 'slug' => 'almacen', 'description' => 'Gestión de inventario de productos.'],
            ['name' => 'Auditor', 'slug' => 'auditor', 'description' => 'Consulta de auditoría y reportes autorizados.'],
            ['name' => 'Consulta', 'slug' => 'consulta', 'description' => 'Acceso de consulta sin operaciones de escritura.'],
        ];
        foreach ($roleDefinitions as $role) {
            DB::table('roles')->insert([
                ...$role,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permissionDefinitions = [
            'dashboard.view' => ['Ver dashboard', 'dashboard'],
            'assets.view' => ['Consultar activos', 'assets'],
            'assets.create' => ['Crear activos', 'assets'],
            'assets.update' => ['Editar activos', 'assets'],
            'assets.delete' => ['Desactivar activos', 'assets'],
            'assets.approve' => ['Aprobar operaciones de activos', 'assets'],
            'assets.export' => ['Exportar activos', 'assets'],
            'assets.criteria.manage' => ['Administrar criterios de obsolescencia', 'assets'],
            'dictamens.create' => ['Crear dictámenes técnicos', 'assets'],
            'dictamens.approve' => ['Aprobar dictámenes técnicos', 'accounting'],
            'inventory.view' => ['Consultar inventario', 'inventory'],
            'inventory.create' => ['Crear registros de inventario', 'inventory'],
            'inventory.update' => ['Editar inventario', 'inventory'],
            'inventory.delete' => ['Desactivar registros de inventario', 'inventory'],
            'inventory.approve' => ['Aprobar movimientos de inventario', 'inventory'],
            'inventory.export' => ['Exportar inventario', 'inventory'],
            'reports.view' => ['Consultar reportes', 'reports'],
            'reports.financial' => ['Consultar reportes financieros', 'reports'],
            'reports.export' => ['Exportar reportes', 'reports'],
            'reports.print' => ['Imprimir reportes', 'reports'],
            'sensitive.view' => ['Consultar información sensible', 'security'],
            'audit.view' => ['Consultar logs de auditoría', 'audit'],
            'audit.export' => ['Exportar logs de auditoría', 'audit'],
            'users.manage' => ['Administrar usuarios', 'security'],
            'roles.manage' => ['Administrar roles y permisos', 'security'],
            'security.manage' => ['Administrar configuración de seguridad', 'security'],
            'periods.closed.write' => ['Operar sobre períodos cerrados mediante autorización', 'accounting'],
            'coso.manage' => ['Administrar controles y riesgos COSO', 'security'],
        ];
        foreach ($permissionDefinitions as $slug => [$name, $module]) {
            DB::table('permissions')->insert([
                'name' => $name,
                'slug' => $slug,
                'module' => $module,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $allPermissions = DB::table('permissions')->pluck('id', 'slug');
        $grants = [
            'superadmin' => array_keys($permissionDefinitions),
            'admin' => array_values(array_diff(array_keys($permissionDefinitions), ['security.manage', 'periods.closed.write'])),
            'contable' => ['dashboard.view', 'assets.view', 'assets.create', 'assets.update', 'assets.approve', 'assets.export', 'assets.criteria.manage', 'dictamens.approve', 'reports.view', 'reports.financial', 'reports.export', 'reports.print', 'inventory.view'],
            'tecnico' => ['dashboard.view', 'assets.view', 'assets.create', 'assets.update', 'assets.export', 'assets.criteria.manage', 'dictamens.create', 'reports.view'],
            'almacen' => ['dashboard.view', 'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.approve', 'inventory.export', 'reports.view'],
            'auditor' => ['dashboard.view', 'assets.view', 'assets.export', 'assets.criteria.manage', 'dictamens.approve', 'inventory.view', 'reports.view', 'reports.financial', 'reports.export', 'reports.print', 'audit.view', 'audit.export', 'sensitive.view', 'coso.manage'],
            'consulta' => ['dashboard.view', 'assets.view', 'inventory.view', 'reports.view', 'reports.financial'],
        ];
        foreach ($grants as $roleSlug => $permissions) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');
            foreach ($permissions as $permissionSlug) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $allPermissions[$permissionSlug],
                ]);
            }
        }
        foreach ([
            ['key' => 'session_idle_minutes', 'value' => '120'],
            ['key' => 'audit_retention_days', 'value' => '3650'],
        ] as $setting) {
            DB::table('system_settings')->insert([...$setting, 'created_at' => now(), 'updated_at' => now()]);
        }

        DB::table('users')->whereNull('status')->update(['status' => 'active']);
        DB::table('users')->where('role', 'user')->update(['role' => 'consulta']);
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropColumn([
                'module', 'action', 'entity_type', 'entity_id', 'description', 'result',
                'reason', 'browser', 'operating_system', 'device_type', 'correlation_id',
                'route_name', 'context', 'previous_hash', 'event_hash',
            ]);
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('system_settings');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'username', 'phone', 'job_title', 'department', 'status', 'access_expires_at',
                'last_login_at', 'last_logout_at', 'created_by', 'updated_by', 'session_version',
            ]);
        });
    }
};
