<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $guarded  = ['id'];
    protected $casts = [
        'meta_keywords' => 'array'
    ];

    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    public function allSubcategories()
    {
        return $this->subcategories()->with('allSubcategories');
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupons_categories', 'category_id', 'coupon_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'products_categories', 'category_id', 'product_id')
        ->with('offer', 'offer.activeOffer', 'reviews')
        ->whereHas('categories')
        //->whereHas('stocks')
        ->whereHas('stocks', function ($p) {
            //$p->whereHas('amounts', function ($t) {
            $p->where('quantity','>','0');
            //});
        });
        //->whereHas('brand')
       // ->take(4)
       //->inRandomOrder();
        //->orderBy('id', 'desc')
       // ->limit(24);
    }


    public function specialProuducts()
    {
        return $this->belongsToMany(Product::class, 'products_categories', 'category_id', 'product_id')
        ->with('offer', 'offer.activeOffer', 'reviews')
        ->whereHas('categories')
        //->whereHas('stocks')
        ->whereHas('stocks', function ($p) {
            //$p->whereHas('amounts', function ($t) {
            $p->where('quantity','>','0');
            //});
        })
        //->whereHas('brand')
       // ->take(4)
       ->inRandomOrder()
        //->orderBy('id', 'desc')
        ->limit(6);
    }
}
