<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tags extends Model
{
    protected $table = "tags";

	    use SoftDeletes;

	    protected $fillable = [
	    	'name'
	    ];

    //use SoftDeletes;
    //protected $guarded = ['id'];    

    /*public function products()
    {
        return $this->belongsToMany(Product::class, 'products_tags', 'tags_id', 'product_id');
    }*/
}
