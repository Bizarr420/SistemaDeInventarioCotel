<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'current_stock',
        'in_use_stock',
        'reserved_stock',
        'repair_stock',
        'damaged_stock',
        'lost_stock',
        'disposed_stock',
        'other_stock',
        'location',
    ];

    public function trackedStock(): int
    {
        return (int) $this->current_stock
            + (int) $this->in_use_stock
            + (int) $this->reserved_stock
            + (int) $this->repair_stock
            + (int) $this->damaged_stock
            + (int) $this->lost_stock
            + (int) $this->disposed_stock
            + (int) $this->other_stock;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
