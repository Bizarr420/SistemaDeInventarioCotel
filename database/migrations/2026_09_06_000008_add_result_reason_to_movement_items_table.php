<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('movement_items', 'result_reason')) {
            Schema::table('movement_items', function (Blueprint $table) {
                $table->string('result_reason', 120)->nullable()->after('final_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('movement_items', 'result_reason')) {
            Schema::table('movement_items', function (Blueprint $table) {
                $table->dropColumn('result_reason');
            });
        }
    }
};
