<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'type')) {
                $table->string('type')->default('service')->after('name_item');
                // 'service' for inventory items (routers, cables, etc.)
                // 'asset' for fixed assets (computers, equipment, etc.)
            }
            
            if (!Schema::hasColumn('products', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained()->cascadeOnDelete();
            }
            
            if (!Schema::hasColumn('products', 'quantity')) {
                $table->integer('quantity')->default(0);
            }
            
            if (!Schema::hasColumn('products', 'unit_cost')) {
                $table->decimal('unit_cost', 12, 2)->nullable();
            }
            
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->unique();
            }
            
            if (!Schema::hasColumn('products', 'expected_useful_life')) {
                $table->date('expected_useful_life')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('products', 'warehouse_id')) {
                $table->dropForeignIdFor(Warehouse::class);
            }
            if (Schema::hasColumn('products', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('products', 'unit_cost')) {
                $table->dropColumn('unit_cost');
            }
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropColumn('sku');
            }
            if (Schema::hasColumn('products', 'expected_useful_life')) {
                $table->dropColumn('expected_useful_life');
            }
        });
    }
};
