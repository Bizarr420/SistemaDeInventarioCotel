<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_id',
        'item_number',
        'serial_number',
        'mac_address',
        'internal_code',
        'patrimonial_code',
        'liquidation_status',
        'final_status',
        'result_reason',
        'note',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }
}
