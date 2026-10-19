<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_valuation_originals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_valuation_id');
            $table->unsignedBigInteger('fixed_asset_id');
            $table->unsignedSmallInteger('valuation_year');
            $table->decimal('amount', 15, 2);
            $table->date('as_of_date')->nullable();
            $table->string('source', 255)->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->timestamp('archived_at');
            $table->string('archive_reason', 120);
            $table->unique('original_valuation_id');
        });

        DB::table('fixed_asset_valuations')->orderBy('id')->chunk(500, function ($valuations): void {
            $archives = $valuations->map(fn ($valuation): array => [
                'original_valuation_id' => $valuation->id,
                'fixed_asset_id' => $valuation->fixed_asset_id,
                'valuation_year' => $valuation->valuation_year,
                'amount' => $valuation->amount,
                'as_of_date' => $valuation->as_of_date,
                'source' => $valuation->source,
                'original_created_at' => $valuation->created_at,
                'original_updated_at' => $valuation->updated_at,
                'archived_at' => now(),
                'archive_reason' => 'Pre-SICAT-ledger snapshot',
            ])->all();

            DB::table('fixed_asset_valuation_originals')->insert($archives);
        });

        Schema::create('fixed_asset_profile_originals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixed_asset_id');
            $table->json('original_values');
            $table->timestamp('archived_at');
            $table->string('archive_reason', 120);
            $table->unique('fixed_asset_id');
        });

        DB::table('fixed_assets')->orderBy('id')->chunk(250, function ($assets): void {
            $archives = $assets->map(fn ($asset): array => [
                'fixed_asset_id' => $asset->id,
                'original_values' => json_encode((array) $asset, JSON_THROW_ON_ERROR),
                'archived_at' => now(),
                'archive_reason' => 'Pre-SICAT-ledger profile snapshot',
            ])->all();

            DB::table('fixed_asset_profile_originals')->insert($archives);
        });

        Schema::create('fixed_asset_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('account_code', 16)->unique();
            $table->string('description', 180);
            $table->string('account_type', 40);
            $table->string('source', 80);
            $table->timestamps();
            $table->unique(['category_id', 'account_type']);
        });

        Schema::table('fixed_asset_valuations', function (Blueprint $table) {
            $table->foreignId('fixed_asset_account_id')
                ->nullable()
                ->after('fixed_asset_id')
                ->constrained('fixed_asset_accounts')
                ->restrictOnDelete();
        });

        Schema::create('fixed_asset_periods', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->string('status', 20);
            $table->string('source', 80);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('fixed_asset_depreciation_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('depreciation_account_id')->constrained('fixed_asset_accounts')->restrictOnDelete();
            $table->string('method', 30);
            $table->date('depreciation_start_date')->nullable();
            $table->unsignedSmallInteger('useful_life_years')->nullable();
            $table->decimal('depreciable_base', 15, 2)->default(0);
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->boolean('is_depreciable')->default(true);
            $table->string('source', 80);
            $table->string('profile', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('fixed_asset_depreciation_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_account_id')->constrained('fixed_asset_accounts')->restrictOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->date('effective_date');
            $table->string('entry_type', 30);
            $table->decimal('amount', 15, 2);
            $table->string('source_key', 160)->unique();
            $table->string('source', 80);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['period_year', 'effective_date']);
            $table->index(['fixed_asset_account_id', 'effective_date']);
        });

        Schema::create('fixed_asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_account_id')->constrained('fixed_asset_accounts')->restrictOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->date('effective_date');
            $table->string('movement_type', 30);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 30)->nullable();
            $table->string('location', 180)->nullable();
            $table->string('source_key', 160)->unique();
            $table->string('source', 80);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['period_year', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_movements');
        Schema::dropIfExists('fixed_asset_depreciation_entries');
        Schema::dropIfExists('fixed_asset_depreciation_parameters');
        Schema::dropIfExists('fixed_asset_periods');

        Schema::table('fixed_asset_valuations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fixed_asset_account_id');
        });

        Schema::dropIfExists('fixed_asset_accounts');
        Schema::dropIfExists('fixed_asset_profile_originals');
        Schema::dropIfExists('fixed_asset_valuation_originals');
    }
};
