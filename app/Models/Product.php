<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Product extends Model
{
    protected $fillable = [
        'internal_code','part_number','item','name_item','cnd','unit','mac',
        'description','note','category_id','supplier_id'
    ];



    public function category(){ return $this->belongsTo(Category::class); }
    public function supplier(){ return $this->belongsTo(Supplier::class); }

    public function stocks(){ return $this->hasMany(ProductStock::class); }
    // Stock total (suma de todos los almacenes)
    public function getTotalStockAttribute(){
        return $this->stocks()->sum('current_stock');
    }
}
