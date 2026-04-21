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
        Schema::table('products', function (Blueprint $table) {
            $table->date('end_of_support')->nullable(); // Fin de soporte del fabricante
            $table->string('compatibility_status')->nullable(); // Compatibilidad tecnológica
            $table->string('operational_capacity')->nullable(); // Capacidad operativa
            $table->integer('useful_life_years')->nullable(); // Vida útil estimada en años
            $table->date('acquisition_date')->nullable(); // Fecha de adquisición
            $table->decimal('acquisition_value', 15, 2)->nullable(); // Valor de adquisición
            $table->decimal('current_accounting_value', 15, 2)->nullable(); // Valor contable actual
            $table->decimal('technical_value', 15, 2)->nullable(); // Valor técnico estimado
            $table->string('obsolescence_status')->default('active'); // Estado de obsolescencia: active, obsolete, critical
            $table->json('obsolescence_criteria')->nullable(); // Parámetros técnicos (umbrales)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'end_of_support',
                'compatibility_status',
                'operational_capacity',
                'useful_life_years',
                'acquisition_date',
                'acquisition_value',
                'current_accounting_value',
                'technical_value',
                'obsolescence_status',
                'obsolescence_criteria'
            ]);
        });
    }
};