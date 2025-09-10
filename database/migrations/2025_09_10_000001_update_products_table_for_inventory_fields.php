<?php
// database/migrations/2025_09_10_000001_update_products_table_for_inventory_fields.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('products', function (Blueprint $table) {
      // Si ya existían otros, agrega sólo los faltantes:
      $table->string('internal_code')->nullable()->after('id'); // COD.INT.
      $table->string('part_number')->nullable()->after('internal_code'); // Nro de parte
      $table->string('item')->nullable()->after('part_number'); // Item (código o referencia corta)
      $table->string('name_item')->nullable()->after('item');   // Nombre de Item (descriptivo corto)
      $table->string('cnd')->nullable()->after('name_item');    // Condición/estado
      $table->string('unit', 20)->nullable()->after('cnd');     // Und (unidad)
      $table->string('mac')->nullable()->after('unit');         // Código MAC
      $table->text('description')->nullable()->change();        // Descripción
      $table->text('note')->nullable()->after('description');   // Observación
      // Quita/ignora 'stock' aquí: el stock ahora vive en product_stocks
      if (Schema::hasColumn('products', 'stock')) {
        $table->dropColumn('stock');
      }
    });
  }
  public function down(): void {
    Schema::table('products', function (Blueprint $table) {
      $table->dropColumn(['internal_code','part_number','item','name_item','cnd','unit','mac','note']);
      $table->integer('stock')->default(0); // si quieres volver
    });
  }
};
