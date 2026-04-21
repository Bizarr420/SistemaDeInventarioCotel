<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dictamen extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'product_id',
        'user_id',
        'content',
        'status',
        'approved_by',
        'approved_at',
        'traceability',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'traceability' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}