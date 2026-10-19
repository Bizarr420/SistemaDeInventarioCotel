<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAssetAccount extends Model
{
    protected $fillable = [
        'category_id',
        'account_code',
        'description',
        'account_type',
        'source',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function valuations(): HasMany
    {
        return $this->hasMany(FixedAssetValuation::class);
    }
}
