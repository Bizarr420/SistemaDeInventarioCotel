<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAssetDepreciationEntry extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'fixed_asset_account_id',
        'period_year',
        'effective_date',
        'entry_type',
        'amount',
        'source_key',
        'source',
        'description',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'amount' => 'decimal:2',
        'period_year' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FixedAssetAccount::class, 'fixed_asset_account_id');
    }
}
