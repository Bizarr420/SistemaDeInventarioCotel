<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('movements')
            ->whereIn('status', ['available', 'in_use', 'reserved', 'under_repair', 'damaged', 'lost', 'disposed', 'liquidated'])
            ->update(['status' => 'completed']);
    }

    public function down(): void
    {
        // Historical equipment statuses cannot be reconstructed from a completed movement.
    }
};
