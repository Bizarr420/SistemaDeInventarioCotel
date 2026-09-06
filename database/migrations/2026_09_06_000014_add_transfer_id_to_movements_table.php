<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('movements', 'transfer_id')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->foreignId('transfer_id')->nullable()->after('parent_movement_id')->constrained('stock_transfers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('movements', 'transfer_id')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->dropForeign(['transfer_id']);
                $table->dropColumn('transfer_id');
            });
        }
    }
};
