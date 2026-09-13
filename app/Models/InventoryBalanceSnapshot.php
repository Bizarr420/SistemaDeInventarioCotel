<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBalanceSnapshot extends Model
{
    protected $fillable = [
        'balance_year',
        'account_code',
        'detail',
        'amount',
        'is_estimated',
        'source',
    ];

    protected $casts = [
        'balance_year' => 'integer',
        'amount' => 'decimal:2',
        'is_estimated' => 'boolean',
    ];
}
