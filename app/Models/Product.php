<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ObsoletenessCriterion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'internal_code',
        'part_number',
        'item',
        'name_item',
        'type',
        'asset_status',
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

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function dictamens(): HasMany
    {
        return $this->hasMany(Dictamen::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(AssetVerification::class, 'product_id');
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->stocks()->sum('current_stock');
    }

    public function isObsolete(): bool
    {
        if ($this->end_of_support && $this->end_of_support->isPast()) {
            return true;
        }

        if ($this->compatibility_status && strtolower($this->compatibility_status) === 'incompatible') {
            return true;
        }

        if ($this->operational_capacity !== null && is_numeric($this->operational_capacity) && $this->operational_capacity < 50) {
            return true;
        }

        // Aplica criterios configurables adicionales
        foreach (ObsoletenessCriterion::active()->get() as $criterion) {
            if ($criterion->matches($this)) {
                return true;
            }
        }

        return false;
    }

    public function calculateUsefulLife(): int
    {
        if ($this->acquisition_date && $this->useful_life_years) {
            $usedYears = $this->acquisition_date->diffInYears(now());
            if ($this->useful_life_years > 0) {
                return min(100, (int) round(($usedYears * 100) / $this->useful_life_years));
            }
        }

        return 0;
    }

    public function calculateDeterioration(): float
    {
        // Nueva escala solicitada: 100% al inicio de vida útil y disminuye con el tiempo.
        if ($this->acquisition_date && $this->expected_useful_life) {
            $totalDays = max(1, $this->acquisition_date->diffInDays($this->expected_useful_life, false));
            $remainingDays = max(0, now()->diffInDays($this->expected_useful_life, false));
            return max(0.0, min(100.0, ($remainingDays * 100.0) / $totalDays));
        }

        if ($this->acquisition_date && $this->useful_life_years && $this->useful_life_years > 0) {
            $totalDays = max(1, (int) round($this->useful_life_years * 365));
            $elapsedDays = max(0, $this->acquisition_date->diffInDays(now()));
            $remainingDays = max(0, $totalDays - $elapsedDays);
            return max(0.0, min(100.0, ($remainingDays * 100.0) / $totalDays));
        }

        return 100.0;
    }

    public function calculatePatrimonialDeviation(): float
    {
        if ($this->technical_value !== null && $this->current_accounting_value !== null) {
            return (float) $this->technical_value - (float) $this->current_accounting_value;
        }

        return 0.0;
    }
}
