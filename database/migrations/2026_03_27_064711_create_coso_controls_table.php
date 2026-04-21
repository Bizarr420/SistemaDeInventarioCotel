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
        Schema::create('coso_controls', function (Blueprint $table) {
            $table->id();
            $table->enum('component', [
                'environment_control',
                'risk_assessment',
                'control_activities',
                'information_communication',
                'monitoring'
            ]);
            $table->string('control_objective');
            $table->text('description');
            $table->enum('control_type', ['preventive', 'detective']);
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'under_review'])->default('active');
            $table->enum('effectiveness', ['high', 'medium', 'low'])->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamp('next_test_date')->nullable();
            $table->json('evidence')->nullable();
            $table->timestamps();

            $table->index('component');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coso_controls');
    }
};
