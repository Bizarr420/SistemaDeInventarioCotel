<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ObsoletenessCriterion;

class ObsoletenessService
{
    public function evaluate(Product $product): array
    {
        $isObsolete = $product->isObsolete();
        $usefulLife = $product->calculateUsefulLife();
        $deterioration = $product->calculateDeterioration();
        $patrimonialDeviation = $product->calculatePatrimonialDeviation();

        $status = 'active';
        if ($isObsolete) {
            $status = 'obsolete';
        } elseif ($product->end_of_support && $product->end_of_support->diffInDays(now()) <= 30) {
            $status = 'critical';
        }

        return [
            'is_obsolete' => $isObsolete,
            'useful_life' => $usefulLife,
            'deterioration' => $deterioration,
            'patrimonial_deviation' => $patrimonialDeviation,
            'status' => $status,
        ];
    }

    public function apply(Product $product): void
    {
        $evaluated = $this->evaluate($product);

        $product->obsolescence_status = $evaluated['status'];
        if ($evaluated['status'] === 'obsolete') {
            $product->asset_status = 'obsoleto';
            if (empty($product->obsolete_disposition_status)) {
                $product->obsolete_disposition_status = 'pendiente';
            }
        }

        if (($product->asset_status ?? null) !== 'obsoleto') {
            $product->obsolete_disposition_status = null;
        }
        $product->save();

        if ($evaluated['is_obsolete']) {
            $product->alerts()->create([
                'type' => 'obsolescence',
                'message' => "El producto {$product->name_item} es obsoleto según análisis técnico.",
                'severity' => 'warning',
                'triggered_at' => now(),
            ]);
        }

        if ($product->end_of_support && $product->end_of_support->diffInDays(now()) <= 30) {
            $product->alerts()->create([
                'type' => 'critical_threshold',
                'message' => "El producto {$product->name_item} se aproxima al fin de soporte.",
                'severity' => 'critical',
                'triggered_at' => now(),
            ]);
        }
    }

    public function getCriteria(): \Illuminate\Database\Eloquent\Collection
    {
        return ObsoletenessCriterion::active()->get();
    }
}
