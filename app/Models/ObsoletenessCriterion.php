<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObsoletenessCriterion extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'type',
        'parameters',
        'is_active',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function matches(Product $product): bool
    {
        switch ($this->type) {
            case 'end_of_support_days':
                $threshold = $this->parameters['days'] ?? 30;
                return $product->end_of_support && $product->end_of_support->diffInDays(now()) <= $threshold;

            case 'operational_capacity_min':
                $min = $this->parameters['min'] ?? 50;
                return is_numeric($product->operational_capacity) && $product->operational_capacity < $min;

            case 'useful_life_below':
                $limit = $this->parameters['limit'] ?? 20;
                return $product->calculateUsefulLife() <= $limit;

            default:
                return false;
        }
    }
}