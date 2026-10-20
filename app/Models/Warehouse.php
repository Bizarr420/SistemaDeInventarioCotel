<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ['name', 'code', 'location'];

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }
}
