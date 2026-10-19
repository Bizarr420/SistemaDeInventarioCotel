<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_depreciation_parameter_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depreciation_parameter_id')
                ->constrained('fixed_asset_depreciation_parameters')
                ->cascadeOnDelete();
            $table->string('change_type', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values');
            $table->string('source', 80);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->timestamps();
            $table->index(['depreciation_parameter_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_depreciation_parameter_audits');
    }
};
