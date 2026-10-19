<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_asset_valuations', function (Blueprint $table) {
            $table->date('as_of_date')->nullable();
            $table->string('source', 255)->nullable();
        });

        DB::table('fixed_asset_valuations')
            ->whereIn(
                'fixed_asset_id',
                DB::table('fixed_assets')
                    ->where('note', 'Planilla maestra; valores expresados en Bs.')
                    ->select('id')
            )
            ->whereNull('source')
            ->update(['source' => 'Planilla_Maestra_50_Items_Todas_Las_Cuentas.xlsx (simulación)']);
    }

    public function down(): void
    {
        Schema::table('fixed_asset_valuations', function (Blueprint $table) {
            $table->dropColumn(['as_of_date', 'source']);
        });
    }
};
