<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CosoControl extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'component',
        'control_objective',
        'description',
        'control_type',
        'responsible_user_id',
        'status',
        'effectiveness',
        'last_tested_at',
        'next_test_date',
        'evidence',
    ];

    protected $casts = [
        'evidence' => 'array',
        'last_tested_at' => 'datetime',
        'next_test_date' => 'datetime',
    ];

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public static function getComponentLabel($component): string
    {
        return match($component) {
            'environment_control' => 'Ambiente de Control',
            'risk_assessment' => 'Evaluación de Riesgos',
            'control_activities' => 'Actividades de Control',
            'information_communication' => 'Información y Comunicación',
            'monitoring' => 'Supervisión',
            default => $component,
        };
    }
}
