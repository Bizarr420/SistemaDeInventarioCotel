<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('movements', 'origin')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->string('origin', 120)->nullable()->after('reason');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('movements', 'origin')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->dropColumn('origin');
            });
        }
    }
};
