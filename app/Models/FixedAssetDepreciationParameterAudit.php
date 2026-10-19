<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAssetDepreciationParameterAudit extends Model
{
    protected $fillable = [
        'depreciation_parameter_id',
        'change_type',
        'old_values',
        'new_values',
        'source',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_at' => 'datetime',
    ];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(FixedAssetDepreciationParameter::class, 'depreciation_parameter_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
