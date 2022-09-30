<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rates extends Model
{
    use SoftDeletes;

    protected $table='rates';
    protected $fillable= ['id','tasa_del_dia','status'];
    protected $guarded = ['id'];
}
