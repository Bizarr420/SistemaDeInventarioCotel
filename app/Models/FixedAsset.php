<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FixedAsset extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'internal_code',
        'part_number',
        'item',
        'name_item',
        'cnd',
        'unit',
        'mac',
        'description',
        'note',
        'category_id',
        'supplier_id',
        'warehouse_id',
        'location_branch',
        'location_floor',
        'location_office',
        'assigned_to',
        'assigned_department',
        'quantity',
        'unit_cost',
        'sku',
        'asset_status',
        'end_of_support',
        'compatibility_status',
        'operational_capacity',
        'useful_life_years',
        'expected_useful_life',
        'acquisition_date',
        'acquisition_value',
        'current_accounting_value',
        'technical_value',
        'obsolescence_status',
        'obsolete_disposition_status',
        'obsolescence_criteria',
    ];

    protected $casts = [
        'end_of_support' => 'date',
        'acquisition_date' => 'date',
        'acquisition_value' => 'decimal:2',
        'current_accounting_value' => 'decimal:2',
        'technical_value' => 'decimal:2',
        'obsolescence_criteria' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(AssetVerification::class);
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(AssetVerification::class)->latestOfMany('verified_at');
    }

    public function annualValuations(): HasMany
    {
        return $this->hasMany(FixedAssetValuation::class)->orderBy('valuation_year');
    }

    public function depreciationParameter(): HasOne
    {
        return $this->hasOne(FixedAssetDepreciationParameter::class);
    }

    public function depreciationEntries(): HasMany
    {
        return $this->hasMany(FixedAssetDepreciationEntry::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(FixedAssetMovement::class);
    }

    public function isObsolete(?Collection $criteria = null): bool
    {
        if ($this->end_of_support && $this->end_of_support->isPast()) {
            return true;
        }

        if ($this->compatibility_status && strtolower((string) $this->compatibility_status) === 'incompatible') {
            return true;
        }

        if (is_numeric($this->operational_capacity) && (float) $this->operational_capacity < 50) {
            return true;
        }

        foreach ($criteria ?? ObsoletenessCriterion::active()->get() as $criterion) {
            if ($criterion->matches($this)) {
                return true;
            }
        }

        return false;
    }

    public function calculateUsefulLife(): float
    {
        return (float) ($this->usefulLifeMetrics()['consumed_percentage'] ?? 0.0);
    }

    public function usefulLifeMetrics(?Carbon $asOf = null): array
    {
        $acquiredAt = $this->acquisition_date;
        $endOfLife = $this->expected_useful_life
            ? Carbon::parse($this->expected_useful_life)
            : ($acquiredAt && (int) $this->useful_life_years > 0
                ? $acquiredAt->copy()->addYears((int) $this->useful_life_years)
                : null);

        if (! $acquiredAt || ! $endOfLife) {
            return [
                'age_years' => null,
                'total_years' => null,
                'remaining_years' => null,
                'consumed_percentage' => null,
                'near_end' => false,
            ];
        }

        $asOf ??= now()->startOfDay();
        $totalDays = max(1, $acquiredAt->diffInDays($endOfLife, false));
        $elapsedDays = max(0, $acquiredAt->diffInDays($asOf, false));
        $remainingDays = max(0, $asOf->diffInDays($endOfLife, false));

        return [
            'age_years' => $elapsedDays / 365.25,
            'total_years' => $totalDays / 365.25,
            'remaining_years' => $remainingDays / 365.25,
            'consumed_percentage' => min(100, max(0, ($elapsedDays * 100) / $totalDays)),
            'near_end' => $endOfLife->lte($asOf->copy()->addYears(2)),
        ];
    }

    public function calculateDeterioration(): float
    {
        return 100.0 - $this->calculateUsefulLife();
    }

    public function calculatePatrimonialDeviation(): float
    {
        if ($this->technical_value !== null && $this->current_accounting_value !== null) {
            return (float) $this->technical_value - (float) $this->current_accounting_value;
        }

        return 0.0;
    }
}
