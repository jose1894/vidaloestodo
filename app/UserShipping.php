<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserShipping extends Model
{
    protected $table='user_shipping';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
