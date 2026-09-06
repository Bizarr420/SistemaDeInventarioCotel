<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('movements', 'status')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->string('status')->default('available')->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('movements', 'status')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
