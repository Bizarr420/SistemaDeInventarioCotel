<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'tracking_mode')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('tracking_mode')->default('quantity')->after('current_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'tracking_mode')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('tracking_mode');
            });
        }
    }
};
