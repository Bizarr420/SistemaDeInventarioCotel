<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'product_id',
        'type',
        'message',
        'severity',
        'is_read',
        'triggered_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'triggered_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}