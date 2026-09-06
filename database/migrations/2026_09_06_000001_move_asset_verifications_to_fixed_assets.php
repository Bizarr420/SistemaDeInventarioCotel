<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('asset_verifications') || Schema::hasColumn('asset_verifications', 'fixed_asset_id')) {
            return;
        }

        Schema::table('asset_verifications', function (Blueprint $table) {
            $table->foreignId('fixed_asset_id')->nullable()->after('id');
        });

        DB::statement(
            'UPDATE asset_verifications SET fixed_asset_id = '
            . '(SELECT fixed_assets.id FROM fixed_assets INNER JOIN products ON products.internal_code = fixed_assets.internal_code '
            . 'WHERE products.id = asset_verifications.product_id LIMIT 1)'
        );

        Schema::table('asset_verifications', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('asset_verifications') || ! Schema::hasColumn('asset_verifications', 'fixed_asset_id')) {
            return;
        }

        Schema::table('asset_verifications', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('id');
        });

        DB::statement(
            'UPDATE asset_verifications SET product_id = '
            . '(SELECT products.id FROM products INNER JOIN fixed_assets ON fixed_assets.internal_code = products.internal_code '
            . 'WHERE fixed_assets.id = asset_verifications.fixed_asset_id LIMIT 1)'
        );

        Schema::table('asset_verifications', function (Blueprint $table) {
            $table->dropForeign(['fixed_asset_id']);
            $table->dropColumn('fixed_asset_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
