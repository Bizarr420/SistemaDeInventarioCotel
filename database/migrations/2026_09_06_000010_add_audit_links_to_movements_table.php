<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            if (! Schema::hasColumn('movements', 'movement_kind')) {
                $table->string('movement_kind')->default('original')->after('type');
            }

            if (! Schema::hasColumn('movements', 'movement_group_id')) {
                $table->uuid('movement_group_id')->nullable()->after('movement_kind')->index();
            }

            if (! Schema::hasColumn('movements', 'parent_movement_id')) {
                $table->foreignId('parent_movement_id')->nullable()->after('movement_group_id')->constrained('movements')->nullOnDelete();
            }

            if (! Schema::hasColumn('movements', 'reference_code')) {
                $table->string('reference_code', 30)->nullable()->unique()->after('parent_movement_id');
            }
        });

        DB::table('movements')->whereNull('movement_group_id')->orderBy('id')->eachById(function ($movement): void {
            DB::table('movements')->where('id', $movement->id)->update([
                'movement_group_id' => (string) \Illuminate\Support\Str::uuid(),
                'reference_code' => sprintf('%s-%06d', $movement->type === 'out' ? 'SAL' : 'ENT', $movement->id),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            if (Schema::hasColumn('movements', 'parent_movement_id')) {
                $table->dropForeign(['parent_movement_id']);
                $table->dropColumn('parent_movement_id');
            }

            foreach (['reference_code', 'movement_group_id', 'movement_kind'] as $column) {
                if (Schema::hasColumn('movements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
