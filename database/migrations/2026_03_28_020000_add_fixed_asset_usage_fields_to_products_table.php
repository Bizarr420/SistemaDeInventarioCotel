<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'location_branch')) {
                $table->string('location_branch')->nullable()->after('warehouse_id');
            }

            if (!Schema::hasColumn('products', 'location_floor')) {
                $table->string('location_floor')->nullable()->after('location_branch');
            }

            if (!Schema::hasColumn('products', 'location_office')) {
                $table->string('location_office')->nullable()->after('location_floor');
            }

            if (!Schema::hasColumn('products', 'assigned_to')) {
                $table->string('assigned_to')->nullable()->after('location_office');
            }

            if (!Schema::hasColumn('products', 'assigned_department')) {
                $table->string('assigned_department')->nullable()->after('assigned_to');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'location_branch',
                'location_floor',
                'location_office',
                'assigned_to',
                'assigned_department',
            ]);
        });
    }
};
