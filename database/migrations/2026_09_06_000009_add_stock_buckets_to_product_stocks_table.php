<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            if (! Schema::hasColumn('product_stocks', 'in_use_stock')) {
                $table->unsignedInteger('in_use_stock')->default(0)->after('current_stock');
            }

            if (! Schema::hasColumn('product_stocks', 'reserved_stock')) {
                $table->unsignedInteger('reserved_stock')->default(0)->after('in_use_stock');
            }

            if (! Schema::hasColumn('product_stocks', 'repair_stock')) {
                $table->unsignedInteger('repair_stock')->default(0)->after('reserved_stock');
            }

            if (! Schema::hasColumn('product_stocks', 'damaged_stock')) {
                $table->unsignedInteger('damaged_stock')->default(0)->after('repair_stock');
            }

            if (! Schema::hasColumn('product_stocks', 'lost_stock')) {
                $table->unsignedInteger('lost_stock')->default(0)->after('damaged_stock');
            }

            if (! Schema::hasColumn('product_stocks', 'disposed_stock')) {
                $table->unsignedInteger('disposed_stock')->default(0)->after('lost_stock');
            }

            if (! Schema::hasColumn('product_stocks', 'other_stock')) {
                $table->unsignedInteger('other_stock')->default(0)->after('disposed_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $columns = [
                'in_use_stock',
                'reserved_stock',
                'repair_stock',
                'damaged_stock',
                'lost_stock',
                'disposed_stock',
                'other_stock',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('product_stocks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
