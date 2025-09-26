<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'stock')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'stock')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0);
        });
    }
};
