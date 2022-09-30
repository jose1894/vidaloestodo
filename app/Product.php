<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'extra_descriptions' => 'array',
        'specification' => 'array',
        'meta_keywords' => 'array'
    ];

    protected $appends = ['precioBaseIva', 'precioPrimeIva'];

    public function getPrecioBaseIvaAttribute()
    {
        $iva = 0;
        if($this->iva == 1){
            
            if(isset($this->productIva) && (!is_null($this->productIva) || (!$this->productIva->isEmpty()) )){
                $iva = ($this->base_price * ($this->productIva->percentage/100));            
            }
            
        }
        return $this->base_price + $iva; 
    }

    public function getPrecioPrimeIvaAttribute()
    {
        $iva = 0;
        $precioPrimeIva = 0;
        if($this->iva == 1){
            if(isset($this->productIva) && (!is_null($this->productIva) || (!$this->productIva->isEmpty()) )){
                if(!is_null($this->prime_price)){
                    $iva = (($this->prime_price) * ($this->productIva->percentage/100));
                    $precioPrimeIva = ($this->prime_price) + $iva; 
                }               
            }
        }
        
        if($precioPrimeIva > 0){
            return $precioPrimeIva;
        } 
        else{
            return $this->prime_price + $iva;
        }
    }

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'products_tags', 'product_id', 'tags_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'products_categories', 'product_id', 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsToMany(SubCategory::class, 'product_subcategory', 'product_id', 'subcategory_id');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offers_products', 'product_id', 'offer_id');
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupons_products', 'product_id', 'coupon_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class);
    }

    public function offer()
    {
        return $this->hasOne(OffersProduct::class, 'product_id', 'id');
    }

    public static function topSales($limit = 6){
        return self::leftJoin('order_details','products.id','=','order_details.product_id')
        ->leftJoin('orders','order_details.order_id','=','orders.id')
        ->selectRaw('products.*, COALESCE(sum(order_details.quantity),0) total')
        ->where('orders.payment_status', '!=', '0')
        ->groupBy('products.id')
        ->with('reviews')
        ->whereHas('stocks', function ($p) {
            //$p->whereHas('amounts', function ($t) {
            $p->where('quantity','>','0');
            //});
        })                
        ->orderBy('total','desc')
        ->limit($limit)
        ->get();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function assignAttributes()
    {
        return $this->hasMany(AssignProductAttribute::class, 'product_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function userReview()
    {
        return $this->hasOne(ProductReview::class, 'product_id')->where('user_id', auth()->user()->id);
    }

    //planes prime
    public function planDetails()
    {
        return $this->hasOne(PlanDetails::class, 'product_id');
    }

    //plan_users
    public function plans()
    {
        return $this->hasMany(PlanUsers::class, 'product_id');
    }
    public function userPlans()
    {
        return $this->hasOne(PlanUsers::class, 'product_id')->where('user_id', auth()->user()->id);
    }


    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function productPreviewImages()
    {
        return $this->hasMany(ProductImage::class)->where('assign_product_attribute_id', 0);
    }

    public function productVariantImages()
    {
        return $this->hasMany(ProductImage::class)->where('assign_product_attribute_id', '!=' ,0);
    }

    public function productIva()
    {
        return $this->belongsTo(ProductIva::class,'iva_id','id');
    }

    public function productcombo()
    {
        return $this->hasMany(ProductCombo::class,'product_id');
    }

    public function comboproduct()
    {
        return $this->hasMany(ProductCombo::class,'product_combo');
    }

}
