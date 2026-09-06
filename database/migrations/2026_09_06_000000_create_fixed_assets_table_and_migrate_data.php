<?php

use App\Models\FixedAsset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fixed_assets')) {
            Schema::create('fixed_assets', function (Blueprint $table) {
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
                $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
                $table->string('location_branch')->nullable();
                $table->string('location_floor')->nullable();
                $table->string('location_office')->nullable();
                $table->string('assigned_to')->nullable();
                $table->string('assigned_department')->nullable();
                $table->integer('quantity')->default(0);
                $table->decimal('unit_cost', 12, 2)->nullable();
                $table->string('sku')->nullable()->unique();
                $table->string('asset_status')->nullable();
                $table->date('end_of_support')->nullable();
                $table->string('compatibility_status')->nullable();
                $table->decimal('operational_capacity', 5, 2)->nullable();
                $table->integer('useful_life_years')->nullable();
                $table->date('expected_useful_life')->nullable();
                $table->date('acquisition_date')->nullable();
                $table->decimal('acquisition_value', 12, 2)->nullable();
                $table->decimal('current_accounting_value', 12, 2)->nullable();
                $table->decimal('technical_value', 12, 2)->nullable();
                $table->string('obsolescence_status')->nullable();
                $table->string('obsolete_disposition_status')->nullable();
                $table->json('obsolescence_criteria')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('products')) {
            DB::table('products')->where('type', 'asset')->orderBy('id')->eachById(function ($product): void {
                $attributes = (array) $product;
                unset($attributes['id']);
                unset($attributes['type']);
                DB::table('fixed_assets')->updateOrInsert(
                    ['internal_code' => $product->internal_code],
                    $attributes
                );
            });

            DB::table('products')->where('type', 'asset')->delete();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fixed_assets')) {
            Schema::drop('fixed_assets');
        }
    }
};
