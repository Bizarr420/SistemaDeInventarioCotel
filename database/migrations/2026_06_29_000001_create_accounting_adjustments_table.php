<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dictamen_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('adjustment_type')->default('deterioration');
            $table->string('status')->default('pending');
            $table->decimal('technical_value', 15, 2)->nullable();
            $table->decimal('current_accounting_value', 15, 2)->nullable();
            $table->decimal('recognized_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->json('traceability')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_adjustments');
    }
};