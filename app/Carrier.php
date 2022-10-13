<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Carrier extends Model
{
    use SoftDeletes;
    
    protected $table = 'carrier';
    
    protected $fillable=[
        'name',
        'logo'
    ];

    public function offices(){
        return $this->hasMany(CarrierOffice::class, 'carrier_id', 'id');
    }

}
