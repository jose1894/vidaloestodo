<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    //
    public function estados(){
        $this->belongsTo(State::class);
    }
}
