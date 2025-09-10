<?php
namespace App\Models;
// app/Models/Warehouse.php
use Illuminate\Database\Eloquent\Model;   // <-- IMPORTANTE
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Warehouse extends Model
{
    protected $fillable = ['name','code','location'];

    public function stocks(){ return $this->hasMany(ProductStock::class); }
}
