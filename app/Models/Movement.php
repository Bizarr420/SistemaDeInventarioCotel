<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    protected $fillable = ['product_id','type','quantity','note','user_id'];
    public function product(){ return $this->belongsTo(\App\Models\Product::class); }
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}
