<?php
// database/migrations/2025_09_10_000000_create_warehouses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('warehouses', function (Blueprint $table) {
      $table->id();
      $table->string('name');          // Ej: "Almacén Central"
      $table->string('code')->unique(); // Ej: "ALM-01"
      $table->string('location')->nullable(); // Dirección/referencia
      $table->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('warehouses'); }
};