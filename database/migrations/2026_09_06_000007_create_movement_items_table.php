<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained('movements')->cascadeOnDelete();
            $table->unsignedInteger('item_number');
            $table->string('serial_number')->nullable();
            $table->string('liquidation_status')->default('pending');
            $table->string('final_status')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['movement_id', 'item_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_items');
    }
};
