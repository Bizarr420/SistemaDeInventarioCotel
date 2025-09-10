<?php
// app/Models/ProductStock.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;   // <-- IMPORTANTE
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductStock extends Model
{
    protected $fillable = ['product_id','warehouse_id','current_stock'];

    public function product(){ return $this->belongsTo(Product::class); }
    public function warehouse(){ return $this->belongsTo(Warehouse::class); }
}

