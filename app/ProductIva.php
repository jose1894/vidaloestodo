<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductIva extends Model
{
    use SoftDeletes;

    protected $guarded  = ['id'];

    public function products()
    {
        return $this->hasMany(Product::class, 'iva_id','id');
    }
}
