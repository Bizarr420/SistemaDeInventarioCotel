<?php
// database/migrations/2025_09_10_000003_update_movements_add_warehouse.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('movements', function (Blueprint $table) {
      $table->foreignId('warehouse_id')->after('product_id')->constrained()->cascadeOnDelete();
    });
  }
  public function down(): void {
    Schema::table('movements', function (Blueprint $table) {
      $table->dropConstrainedForeignId('warehouse_id');
    });
  }
};
