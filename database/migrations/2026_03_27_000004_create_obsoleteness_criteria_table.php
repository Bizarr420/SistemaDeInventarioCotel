<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('obsoleteness_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del criterio, e.g., 'end_of_support_threshold'
            $table->string('type'); // Tipo: 'date', 'percentage', 'value'
            $table->json('parameters'); // Parámetros del criterio
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obsoleteness_criteria');
    }
};