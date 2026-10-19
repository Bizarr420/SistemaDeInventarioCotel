<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAssetDepreciationParameter extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'depreciation_account_id',
        'method',
        'depreciation_start_date',
        'useful_life_years',
        'depreciable_base',
        'residual_value',
        'is_depreciable',
        'source',
        'profile',
    ];

    protected $casts = [
        'depreciation_start_date' => 'date',
        'residual_value' => 'decimal:2',
        'depreciable_base' => 'decimal:2',
        'is_depreciable' => 'boolean',
        'useful_life_years' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function depreciationAccount(): BelongsTo
    {
        return $this->belongsTo(FixedAssetAccount::class, 'depreciation_account_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FixedAssetDepreciationEntry::class, 'fixed_asset_id', 'fixed_asset_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(FixedAssetDepreciationParameterAudit::class, 'depreciation_parameter_id');
    }
}
