<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('valuation_year');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'valuation_year']);
            $table->index('valuation_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_valuations');
    }
};
