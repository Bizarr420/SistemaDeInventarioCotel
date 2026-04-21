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
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('risk_category');
            $table->text('description');
            $table->integer('probability')->comment('1=remote, 5=highly probable');
            $table->integer('impact')->comment('1=minimal, 5=critical');
            $table->integer('risk_score')->computed('probability * impact');
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical']);
            $table->text('mitigation_action');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('risk_level');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
