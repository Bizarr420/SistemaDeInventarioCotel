<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name','sku','category_id','supplier_id','stock','cost_price','sale_price'
    ];

    public function category(){ return $this->belongsTo(Category::class); }
    public function supplier(){ return $this->belongsTo(Supplier::class); }
    public function movements(){ return $this->hasMany(Movement::class); }
}
