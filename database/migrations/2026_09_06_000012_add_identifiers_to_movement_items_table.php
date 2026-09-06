<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movement_items', function (Blueprint $table) {
            if (! Schema::hasColumn('movement_items', 'mac_address')) {
                $table->string('mac_address', 50)->nullable()->after('serial_number');
            }

            if (! Schema::hasColumn('movement_items', 'internal_code')) {
                $table->string('internal_code', 100)->nullable()->after('mac_address');
            }

            if (! Schema::hasColumn('movement_items', 'patrimonial_code')) {
                $table->string('patrimonial_code', 100)->nullable()->after('internal_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movement_items', function (Blueprint $table) {
            foreach (['patrimonial_code', 'internal_code', 'mac_address'] as $column) {
                if (Schema::hasColumn('movement_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
