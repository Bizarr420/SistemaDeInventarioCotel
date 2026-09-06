<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'current_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('current_status')->default('available')->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'current_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('current_status');
            });
        }
    }
};
