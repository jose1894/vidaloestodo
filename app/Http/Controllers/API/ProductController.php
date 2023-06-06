<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\Category;
use App\Rates;
use App\GeneralSetting;
use App\Brand;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->with(['categories', 'brand', 'stocks', 'productImages', 'productIva'])
            ->whereHas('categories')
            ->whereHas('brand')
            ->paginate(getPaginate());

        foreach ($products as $item) {
            if (!is_null($item->prime_price)) {
                $item->prime_price = str_replace(',', '.', $item->prime_price);
            }
        }

        return response()->json($products);
    }

    public function productsOffers()
    {
        $all_products       = Product::with(
            [
                'categories',
                'offer',
                'offer.activeOffer',
                'reviews',
                'brand',
                'productIva',
                'stocks' => function ($q) {
                    $q->where('quantity', '>', 0);
                },
            ]
        )
            ->where('is_plan', 0)
            ->whereHas('categories')
            ->whereHas('offer.activeOffer')
            ->get();

        return response()->json($all_products);
    }

    public function productsBestsellers()
    {
        $brands                 = Brand::latest()->get();
        $categories             = Category::where('parent_id', null)->latest()->get();
        $perpage = 30;
        $all_products       = Product::topSales(30);
        $productCollection  = $all_products;
        //$products       = Product::topSales(30);
        /* $arrayProducts = [];
        foreach ($productCollection as $product) {
            $product['urlimage'] = getImage(imagePath()['product']['path'] . '/thumb_' . @$product['main_image'], imagePath()['product']['size']);
            $arrayProducts[] = $product;
        }*/
        $products           =  paginate($productCollection, $perpage, $page = null, $options = []);
        $products = $this->arrayProduct($products);
        // $products           =  paginate($products, $perpage, $page = null, $options = []);
        return response()->json($products);
    }

    public function productsCategories()
    {
        $categories = Category::has('specialProuducts')->where('in_filter_menu',  '1')
            ->orderByRaw(
                "case when position is null then 1 else 0 end, position"
            )
            ->paginate(4);

        $cur_sym = $this->generalSetting()->cur_sym;
        $rates = $this->rates()->tasa_del_dia;
        $arrayProducts = [];
        $arrayCatories = [];
        foreach ($categories as $category) {
            $category['rates'] = $rates;
            if (count($category->specialProuducts) > 0) {
                foreach ($category->specialProuducts as $product) {
                    $product['selected'] = 'N';
                    $product['cartACt'] = ['quantity' => 0];

                    $discount = $this->calculateDiscount($product);
                    $product['primePriceRates'] = getAmount($product['prime_price'] * $rates, 2);
                    $product['precioPrimeIvaRates'] = getAmount($product['precioPrimeIva'] * $rates, 2);
                    $product['precioBaseIvaRates'] = getAmount($product['precioBaseIva'] * $rates, 2);
                    $product['cur_sym'] = $cur_sym;
                    $product['discount'] = $discount;
                    $precioBaseIvaDiscount = $discount > 0 ? getAmount($product->precioBaseIva - $discount, 2) : 0;
                    $product['precioBaseIvaDiscount'] = $precioBaseIvaDiscount;
                    $product['precioBaseIvaDiscountRates'] = getAmount($precioBaseIvaDiscount * $rates, 2);
                    $product['urlimage'] = getImage(imagePath()['product']['path'] . '/thumb_' . @$product['main_image'], imagePath()['product']['size']);
                    // $product['quantityList'] = $this->quantityList(intval($product->stocks[0]->quantity));
                    $product['quantityList'] = intval($product->stocks[0]->quantity);
                    $product['categoryMain'] = $category->id;

                    $arrayProducts[] = $product;
                }
                $category->productsSpecial = $arrayProducts;
                unset($arrayProducts);
                $arrayCatories[] = $category;
            }
        }

        return response()->json($categories);
    }

    private function generalSetting()
    {
        return GeneralSetting::first();
    }

    private function calculateDiscount($item)
    {
        $discount = 0;
        if ($item->offer && $item->offer->activeOffer) {
            $discount = calculateDiscount(
                $item->offer->activeOffer->amount,
                $item->offer->activeOffer->discount_type,
                $item->base_price
            );
        }
        return $discount;
    }

    private function quantityList($quantity)
    {
        return range(1, $quantity);
    }

    private function rates()
    {
        $rate = Rates::select('tasa_del_dia')
            ->where('status', '1')
            //->where('type', session()->get('moneda'))
            ->orderBy('id', 'desc')
            ->first();

        return  $rate;
    }

    public function productDetails($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_plan', 0)
            ->with(
                'categories',
                'assignAttributes',
                'offer',
                'offer.activeOffer',
                'reviews',
                'productImages',
                'productPreviewImages',
                'productVariantImages',
                'stocks',
                'tags'
            )
            ->whereHas('categories')
            ->first();

        $product['selected'] = 'N';
        $product['cartACt'] = ['quantity' => 0];
        $product['urlimage'] = getImage(imagePath()['product']['path'] . '/' . @$product->main_image, imagePath()['product']['size']);
        // $product['quantityList'] = $this->quantityList(intval($product->stocks[0]->quantity));
        $product['quantityList'] = intval($product->stocks[0]->quantity);
        
        //productos relacionados
        $rProducts = $product->categories()
            ->with(
                [
                    'products',
                    'products.reviews', 'products.offer', 'products.offer.activeOffer',
                    'products.stocks' => function ($q) {
                        $q->where('quantity', '>', 0);
                    },
                ]
            )
            ->get()
            ->map(function ($item) use ($product) {
                return $item->products->where('id', '!=', $product->id)
                    ->take(12);
            });

        //Meto los rProducts no duplicados en un solo array
        $array = [];
        foreach ($rProducts as $rp) {
            $array = $rp;
        }

        $related_products = [];

        foreach ($array as $value) {
            if (isset($value->stocks) && (!$value->stocks->isEmpty())) {
                foreach ($value->stocks as $stock) {
                    if ($stock->quantity > 0) {
                        $value['urlimage'] = getImage(imagePath()['product']['path'] . '/thumb_' . @$value['main_image'], imagePath()['product']['size']);
                        $related_products[] = $value;
                    }
                }
            }
        }

        return response()->json(['detail' => $product, 'related' => $related_products]);
    }

    public function productsRecents()
    {
        $products = [];
        $products       = Product::with(
            'categories',
            'offer',
            'offer.activeOffer',
            'reviews',
            'brand',
            'productIva',
            'stocks',
            'tags'
        )
            ->where('is_plan', 0)
            ->whereHas('categories')
            ->orderBy('id', 'desc')
            ->whereHas('stocks', function ($p) {
                $p->where('quantity', '>', '0');
            })
            ->get();


        $arrayProducts = $this->arrayProduct($products);

        return response()->json($arrayProducts);
    }

    private function arrayProduct($products)
    {
        $cur_sym = $this->generalSetting()->cur_sym;
        $rates = $this->rates()->tasa_del_dia;

        $arrayProducts = [];
        foreach ($products as $product) {
            $discount = $this->calculateDiscount($product);
            $product['selected'] = 'N';
            $product['cartACt'] = ['quantity' => 0];
            $product['primePriceRates'] = getAmount($product['prime_price'] * $rates, 2);
            $product['precioPrimeIvaRates'] = getAmount($product['precioPrimeIva'] * $rates, 2);
            $product['precioBaseIvaRates'] = getAmount($product['precioBaseIva'] * $rates, 2);
            $product['cur_sym'] = $cur_sym;
            $product['discount'] = $discount;
            $precioBaseIvaDiscount = $discount > 0 ? getAmount($product->precioBaseIva - $discount, 2) : 0;
            $product['precioBaseIvaDiscount'] = $precioBaseIvaDiscount;
            $product['precioBaseIvaDiscountRates'] = getAmount($precioBaseIvaDiscount * $rates, 2);
            $product['urlimage'] = getImage(imagePath()['product']['path'] . '/thumb_' . @$product['main_image'], imagePath()['product']['size']);
            // $product['quantityList'] = $this->quantityList(intval($product->stocks[0]->quantity));
            $product['quantityList'] = intval($product->stocks[0]->quantity);

            $arrayProducts[] = $product;
        }
        return $arrayProducts;
    }

    public function search_bar_home(Request $request)
    {
        $search = $request->search;

        $search = explode(" ", $search);

        $search = implode("%", $search);

        $products_like = Product::with([
            'categories',
            'offer',
            'offer.activeOffer',
            'reviews',
            'brand',
            'productIva',
            'stocks',
            /*'stocks' => function ($query) {
            $query->where('quantity', '>', 0)->latest()->get(); //el ultimo stock registrado
        },*/
            'tags'
        ])
            ->where(function ($product) use ($search) {
                $product->where('name', 'like', "%" . $search . "%");
            })->whereHas('stocks', function ($q) {
                $q->where('quantity', '>', '0');
            })->paginate(10);

        //DB::statement("ALTER TABLE products ADD FULLTEXT(name, description)");

        $products_match = Product::select('*')
            ->selectRaw('
                            match(name, description) 
                            against(? in natural language mode) as score
                        ', [$search])
            ->whereRaw('
                            match(name, description) 
                            against(? in natural language mode) > 0.0000001
                        ', [$search])
            ->with(
                [
                    'stocks' => function ($query) {
                        $query->where('quantity', '>', 0)->latest()->get(); //el ultimo stock registrado
                    },
                    'categories',
                    'offer',
                    'offer.activeOffer',
                    'reviews',
                    'brand',
                    'productIva',
                    'tags'
                ]
            )->whereHas('stocks', function ($q) {
                $q->where('quantity', '>', '0');
            })
            ->paginate(10);

        $categories = Category::where(function ($category) use ($search) {
            $category->where('name', 'like', "%" . $search . "%");
        })->paginate(10);

        $products = $products_match->count() > 0 ? $products_match : $products_like;
        $arrayProducts = [];

        $cur_sym = $this->generalSetting()->cur_sym;
        $rates = $this->rates()->tasa_del_dia;

        foreach ($products as $product) {
            $product['cur_sym'] = $cur_sym;
            $product['selected'] = 'N';
            $product['cartACt'] = ['quantity' => 0];
            $product['precioBaseIvaRates'] = getAmount($product['precioBaseIva'] * $rates, 2);
            $product['urlimage'] = getImage(imagePath()['product']['path'] . '/' . $product->main_image, imagePath()['product']['size']);
            // $product['quantityList'] = isset($product->stocks[0]) ? $this->quantityList(intval($product->stocks[0]->quantity)) : []; //$product->stocks[0]->quantity $this->quantityList(intval($product->stocks[0]->quantity));
            $product['quantityList'] = intval($product->stocks[0]->quantity); //$product->stocks[0]->quantity $this->quantityList(intval($product->stocks[0]->quantity));
            $arrayProducts[] = $product;
        }

        return response()->json($arrayProducts);
    }

    public function productsByCategory($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $page = null;
        $perpage = 30;

        $all_products           = $category->products()
            ->with(
                'categories',
                'offer',
                'offer.activeOffer',
                'brand',
                'reviews'
            )
            ->whereHas('categories')
            ->get();

        $productCollection  = $all_products;

        $$productCollection = $this->arrayProduct($productCollection);

        $products = paginate($productCollection, $perpage, $page, $options = []);

        $subcategory = Category::where('parent_id', $category->id)
            ->whereHas('products', function ($q) {
                $q->with(
                    'offer',
                    'offer.activeOffer',
                    'brand',
                    'reviews',
                    'stocks'
                );
            })
            ->has('products')
            ->get();


        return response()->json(
            [
                'category' => $category,
                'products' => $products,
                'subcategory' => $subcategory
            ]
        );
    }

    public function more_products(Request $request)
    {
        if (!isset(request()->perpage)) {
            $perpage    = 15;
        } else {
            $perpage    = request()->perpage;
        }

        $page = $request->page;
        $index = $request->page + 1;

        $categories = Category::with(
            [
                'products' => function ($q) {
                    //$q->take(4)
                    return $q->whereHas('categories');
                },
                'products.reviews',
                'products.offers',
                'products.stocks' => function ($q) {
                    $q->where('quantity', '>', 0);
                },
            ]
        )
        ->where('in_filter_menu',  '1')
        //->orderBy('position', 'asc')
        ->orderByRaw(
            "case when position is null then 1 else 0 end, position"
        )
        ->paginate(3);

    }

}
