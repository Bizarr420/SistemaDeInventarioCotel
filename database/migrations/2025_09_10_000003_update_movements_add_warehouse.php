<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('movements', 'warehouse_id')) {
            return;
        }

        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->after('product_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('movements', 'warehouse_id')) {
            return;
        }

        Schema::table('movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
        });
    }
};
