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
        if (! Schema::hasColumn('product_stocks', 'location')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->string('location', 120)->nullable()->after('current_stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('product_stocks', 'location')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->dropColumn('location');
            });
        }
    }
};

