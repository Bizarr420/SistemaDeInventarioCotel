<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('balance_year');
            $table->string('account_code', 20);
            $table->string('detail');
            $table->decimal('amount', 15, 2);
            $table->boolean('is_estimated')->default(false);
            $table->string('source', 100)->default('conciliacion_inventario');
            $table->timestamps();

            $table->unique(['balance_year', 'account_code']);
            $table->index('balance_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balance_snapshots');
    }
};
