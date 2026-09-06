<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['dictamens', 'accounting_adjustments'] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'fixed_asset_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('fixed_asset_id')->nullable()->after('id');
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
                $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['dictamens', 'accounting_adjustments'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'fixed_asset_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('id');
                $table->dropForeign(['fixed_asset_id']);
                $table->dropColumn('fixed_asset_id');
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            });
        }
    }
};
