<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('asset_status')->nullable()->after('type');
        });

        DB::table('products')
            ->where('type', 'asset')
            ->whereNull('asset_status')
            ->update(['asset_status' => 'operativo']);

        DB::table('products')
            ->where('obsolete_disposition_status', 'evaluacion')
            ->update(['obsolete_disposition_status' => 'pendiente']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('asset_status');
        });
    }
};
