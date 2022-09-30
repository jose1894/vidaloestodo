<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCombo extends Model
{
    protected $guarded = ['id'];    

    public function productcombo()
    {
        return $this->belongsTo(Product::class, 'products_combo', 'id', 'product_id');
    }

    public function comboproduct()
    {
        return $this->belongsTo(Product::class, 'products_combo', 'id', 'product_combo');
    }
}
