<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarrierOffice extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
        'carrier_id',
        'state_id',
        'city_id',
        'name',
        'code',
        'address'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function carrier()
    {
        return $this->hasOne(Carrier::class,'id', 'carrier_id');
    }
    public function state()
    {
        return $this->hasOne(State::class,'id', 'state_id');
    }
    public function city()
    {
        return $this->hasOne(City::class,  'id','city_id');
    }
}
