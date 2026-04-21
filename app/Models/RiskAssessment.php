<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAssessment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'product_id',
        'risk_category',
        'description',
        'probability',
        'impact',
        'risk_level',
        'mitigation_action',
        'responsible_user_id',
        'status',
        'due_date',
        'closed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function getRiskScoreAttribute(): int
    {
        return $this->probability * $this->impact;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $score = $model->probability * $model->impact;
            if ($score >= 20) {
                $model->risk_level = 'critical';
            } elseif ($score >= 12) {
                $model->risk_level = 'high';
            } elseif ($score >= 6) {
                $model->risk_level = 'medium';
            } else {
                $model->risk_level = 'low';
            }
        });
    }
}
