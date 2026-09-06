<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('movements')->where('status', 'pending_liquidation')->update(['status' => 'pending']);
        DB::table('movements')->whereIn('status', ['available', 'liquidated', 'disposed'])->update(['status' => 'completed']);
    }

    public function down(): void
    {
        DB::table('movements')->where('status', 'pending')->update(['status' => 'pending_liquidation']);
    }
};
