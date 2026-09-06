<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            if (! Schema::hasColumn('movements', 'technician_id')) {
                $table->foreignId('technician_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('movements', 'reason')) {
                $table->string('reason', 120)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            if (Schema::hasColumn('movements', 'technician_id')) {
                $table->dropForeign(['technician_id']);
                $table->dropColumn('technician_id');
            }

            if (Schema::hasColumn('movements', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
};
