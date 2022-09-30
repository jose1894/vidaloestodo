<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modules extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function roles()
    {
        return $this->belongsToMany(Roles::class);
    }

}
