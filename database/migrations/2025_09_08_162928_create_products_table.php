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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('internal_code', 100)->nullable()->unique();
            $table->string('part_number', 120)->nullable();
            $table->string('item', 120)->nullable();
            $table->string('name_item', 150);
            $table->string('cnd', 80)->nullable();
            $table->string('unit', 20)->nullable();
            $table->string('mac', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('note', 500)->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
