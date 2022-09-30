<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $guarded = ['id'];

    public function order()
    {
        return $this->hasOne(Order::class, 'shipping_method_id', 'id');
    }
}
