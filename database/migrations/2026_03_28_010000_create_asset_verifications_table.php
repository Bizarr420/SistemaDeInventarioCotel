<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('verified_at');
            $table->string('status'); // operativo | falla | deteriorado | obsoleto
            $table->unsignedTinyInteger('deterioration_level')->default(0); // 0..100
            $table->text('notes')->nullable();
            $table->date('next_verification_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_verifications');
    }
};
