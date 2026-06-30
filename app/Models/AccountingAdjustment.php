<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingAdjustment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'product_id',
        'dictamen_id',
        'generated_by',
        'approved_by',
        'adjustment_type',
        'status',
        'technical_value',
        'current_accounting_value',
        'recognized_amount',
        'description',
        'posted_at',
        'traceability',
    ];

    protected $casts = [
        'technical_value' => 'decimal:2',
        'current_accounting_value' => 'decimal:2',
        'recognized_amount' => 'decimal:2',
        'posted_at' => 'datetime',
        'traceability' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function dictamen(): BelongsTo
    {
        return $this->belongsTo(Dictamen::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}