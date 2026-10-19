<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAssetPeriod extends Model
{
    protected $primaryKey = 'year';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['year', 'status', 'source', 'closed_at', 'closed_by'];

    protected $casts = [
        'year' => 'integer',
        'closed_at' => 'datetime',
    ];
}
