<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAssetValuation extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'fixed_asset_account_id',
        'valuation_year',
        'amount',
        'as_of_date',
        'source',
    ];

    protected $casts = [
        'valuation_year' => 'integer',
        'amount' => 'decimal:2',
        'as_of_date' => 'date',
    ];

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FixedAssetAccount::class, 'fixed_asset_account_id');
    }
}
