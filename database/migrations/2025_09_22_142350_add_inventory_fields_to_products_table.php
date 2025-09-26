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
            // Agrega sólo si no existen (protege entornos ya migrados)
            if (!Schema::hasColumn('products', 'internal_code')) $table->string('internal_code', 100)->nullable()->unique();
            if (!Schema::hasColumn('products', 'part_number'))   $table->string('part_number', 120)->nullable();
            if (!Schema::hasColumn('products', 'item'))          $table->string('item', 120)->nullable();
            if (!Schema::hasColumn('products', 'name_item'))     $table->string('name_item', 150);
            if (!Schema::hasColumn('products', 'cnd'))           $table->string('cnd', 80)->nullable();
            if (!Schema::hasColumn('products', 'unit'))          $table->string('unit', 20)->nullable();
            if (!Schema::hasColumn('products', 'mac'))           $table->string('mac', 50)->nullable();
            if (!Schema::hasColumn('products', 'description'))   $table->text('description')->nullable();
            if (!Schema::hasColumn('products', 'note'))          $table->string('note', 500)->nullable();

            if (!Schema::hasColumn('products', 'category_id'))   $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            if (!Schema::hasColumn('products', 'supplier_id'))   $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Quita sólo lo agregado arriba
            if (Schema::hasColumn('products', 'internal_code')) $table->dropColumn('internal_code');
            if (Schema::hasColumn('products', 'part_number'))   $table->dropColumn('part_number');
            if (Schema::hasColumn('products', 'item'))          $table->dropColumn('item');
            if (Schema::hasColumn('products', 'name_item'))     $table->dropColumn('name_item');
            if (Schema::hasColumn('products', 'cnd'))           $table->dropColumn('cnd');
            if (Schema::hasColumn('products', 'unit'))          $table->dropColumn('unit');
            if (Schema::hasColumn('products', 'mac'))           $table->dropColumn('mac');
            if (Schema::hasColumn('products', 'description'))   $table->dropColumn('description');
            if (Schema::hasColumn('products', 'note'))          $table->dropColumn('note');

            if (Schema::hasColumn('products', 'category_id'))   $table->dropConstrainedForeignId('category_id');
            if (Schema::hasColumn('products', 'supplier_id'))   $table->dropConstrainedForeignId('supplier_id');
        });
    }
};
