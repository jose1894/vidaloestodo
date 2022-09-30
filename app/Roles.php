<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasOne(User::class, 'role_id', 'id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'role_id', 'id');
    }

    public function modules()
    {
        return $this->belongsToMany(Modules::class);
    }
}
