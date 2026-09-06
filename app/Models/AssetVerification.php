<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\FixedAsset;

class AssetVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixed_asset_id',
        'verified_by',
        'verified_at',
        'status',
        'deterioration_level',
        'notes',
        'next_verification_at',
    ];

    protected $casts = [
        'verified_at' => 'date',
        'next_verification_at' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
