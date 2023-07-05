<?php

namespace App\Http\Controllers;

use App\AssignProductAttribute;
use App\GeneralSetting;
use App\Brand;
use App\Language;
use App\Offer;
use App\User;
use App\Product;
use App\Subscriber;
use App\SupportAttachment;
use App\SupportMessage;
use App\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Category;
use App\Exports\OrderExport;
use App\Frontend;
use App\Order;
use App\OrderDetail;
use App\ProductReview;
use App\ProductStock;
use App\PlanDetails;
use App\PlanUsers;
use App\UserShipping;
use App\Rates;
use DB;

class SiteController extends Controller
{
    public function __construct()
    {
        $this->activeTemplate = activeTemplate();
    }

    public function test()
    {
        $data = Category::with(
            [
                'products.stocks' => function ($q) {
                    //$q->whereHas('categories');
                    $q->where('quantity', '>', 0);
                },
                'products.reviews',
                'products.offers',
            ]
        )->whereHas('products')->get();

        $cp = [];
        foreach ($data as $key => $item) {
            foreach ($item->products as $product) {
                if ((!is_null($product->stocks)) && (count($product->stocks) > 0)) {
                    $cp[$key] = $item;
                }
            }
        }

        return $cp;
    }

    public function plans_expires()
    {
        $plans = PlanUsers::where('expiration_date', '>=', Carbon::now()->format('Y-m-d'))->where('email_sent', 0)->with('product')->get();

        $date2 = new \DateTime(Carbon::now()->format('Y-m-d H:i:s'));
        // dd($plans);
        $gnl = GeneralSetting::first();

        foreach ($plans as $key) {
            $user = User::find($key->user_id);
            // dd($user);
            $date1 = new \DateTime($key->expiration_date);
            $t = $date1->diff($date2)->d;

            if ($t <= 1) {
                $short_code = [
                    'plan_name' => $key->product->name,
                    'user_name' => $user->user_name
                ];
                try {
                    $user->ev = $gnl->ev ? 0 : 1;
                    $user->sv = $gnl->sv ? 0 : 1;
                    notify($user, 'PLAN_EXPIRE', [
                        'plan_name' => $key->product->name
                    ]);
                } catch (\Exception $exp) {
                    return $exp;
                }
                $key->email_sent = 1;
                $key->save();
            }
        }
    }

    public function get_shipping_user()
    {
        $user_id = auth()->user()->id ?? null;

        $data = UserShipping::where('user_id', $user_id)->get();
        $index = 1;

        // return response()->json(['shipping' => $shipping]);
        return view('partials.shipping_user', ['data' => $data, 'index' => $index]);
    }

    public function post_shipping_user(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'address' => 'required',
            'mobile' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required',
        ], [
            'city.required' => 'Especifique una Ciudad para la dirección',
            'state.required' => 'Especifique un Estado para la dirección',
            'zip.required' => 'Especifique un Código Postal para la dirección',
            'addres.required' => 'Transcriba una Dirección válida',
            'mobile.required' => 'Digita un número de teléfono movil',
            'firstname.required' => 'Digite un nombre',
            'lastname.required' => 'Digite un apellido',
            'email.required' => 'Digita una dirección de correo electrónico',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $shipping_address = [
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'email'  => $request->email,
            'mobile'    =>  $request->mobile, //auth()->user()->mobile,
            'country'   => 'Venezuela, Bolivarian Republic of Venezuela',
            'city'      => $request->city,
            'state'     => $request->state,
            'zip'       => $request->zip,
            'address'   => $request->address,
        ];

        $user_id = auth()->user()->id ?? null;

        $search = UserShipping::where('user_id', $user_id)->where('shipping_address', json_encode($shipping_address))->count();
        if ($search == 0) {
            $UserShipping = new UserShipping;
            $UserShipping->user_id = $user_id;
            $UserShipping->shipping_address = json_encode($shipping_address);
            $UserShipping->save();
        }

        $data = UserShipping::where('user_id', $user_id)->get();
        //$usershipping = UserShipping::where('user_id', auth()->user()->id)->get();
        $index = 1;

        return response()->json(['shipping' => $data]);
        // return view('partials.shipping_user', ['data' => $data, 'index' => $index]);
    }

    public function delete_shipping_user(Request $request)
    {
        $data = UserShipping::find($request->id)->delete();
        $user_id = auth()->user()->id ?? null;
        $datas = UserShipping::where('user_id', $user_id)->get();
        //$usershipping = UserShipping::where('user_id', auth()->user()->id)->get();
        $index = 1;

        return response()->json(['shipping' => $datas]);
        // return view('partials.shipping_user', ['data' => $data
        // return true;
    }

    public function search_bar_home(Request $request)
    {
        $perpage = 10;
        $search = $request->search;

        $search = explode(" ", $search);

		$search = implode("%", $search);

        // $products_like = Product::with('productIva')
        //     ->where(function ($product) use ($search) {
        //         $product->where('name', 'like', "%" . $search . "%");
        //     })->paginate(10);

        // //DB::statement("ALTER TABLE products ADD FULLTEXT(name, description)");

        // $products_match = Product::select('*')
        //     ->selectRaw('
        //                     match(name, description) 
        //                     against(? in natural language mode) as score
        //                 ', [$search])
        //     ->whereRaw('
        //                     match(name, description) 
        //                     against(? in natural language mode) > 0.0000001
        //                 ', [$search])
        //     ->with(
        //         [
        //             'stocks' => function ($query) {
        //                 $query->where('quantity', '>', 0)->latest()->get(); //el ultimo stock registrado
        //             },
        //             'productIva',
        //         ]
        //     )
        //     ->paginate(10);

        $products_like = Product::with(
            [
                'categories',
                'offer',
                'offer.activeOffer',
                'reviews',
                'brand',
                'stocks' => function ($q) {
                    $q->where('quantity', '>', 0);
                },
                'productIva'
            ]
        )
            ->where(function ($product) use ($search) {
                $product->where('name', 'like', "%" . $search . "%");
            })
            ->where('show_in_frontend',  '1')
            ->where('is_plan', 0)
            ->whereHas('categories')
            ->whereHas('stocks', function ($p) {
                //$p->whereHas('amounts', function ($t) {
                $p->where('quantity', '>', '0');
                //});
            })
            //->orderBy('name', 'desc')
            ->orderByRaw(
                "CASE WHEN name = '" . $search . "' THEN 0  
                           WHEN name LIKE '" . $search . "%' THEN 1  
                           WHEN name LIKE '%" . $search . "%' THEN 2  
                           WHEN name LIKE '%" . $search . "' THEN 3  
                           ELSE 4
                      END, name ASC"
            )
            ->paginate($perpage);

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
                    'categories',
                    'offer',
                    'offer.activeOffer',
                    'reviews',
                    'brand',
                    'stocks' => function ($q) {
                        $q->where('quantity', '>', 0);
                    },
                    'productIva'
                ]
            )
            ->where('show_in_frontend',  '1')
            ->where('is_plan', 0)
            ->whereHas('categories')
            ->whereHas('stocks', function ($p) {
                //$p->whereHas('amounts', function ($t) {
                $p->where('quantity', '>', '0');
                //});
            })
            //->orderBy('name', 'desc')
            ->orderByRaw(
                "CASE WHEN name = '" . $search . "' THEN 0  
                           WHEN name LIKE '" . $search . "%' THEN 1  
                           WHEN name LIKE '%" . $search . "%' THEN 2  
                           WHEN name LIKE '%" . $search . "' THEN 3  
                           ELSE 4
                      END, name ASC"
            )
            ->paginate($perpage);

        $categories = Category::where(function ($category) use ($search) {
            $category->where('name', 'like', "%" . $search . "%");
        })->paginate(10);

        $products = $products_match->count() > 0 ? $products_match : $products_like;

        return view('partials.search_products_categories', ['products' => $products, 'categories' => $categories]);
    }

    public function search_cities(Request $request)
    {
        $cities = \DB::table('cities')->where('state_id', $request->state_id)->get();

        return $cities;
    }

    public function index()
    {
        $date_now = Carbon::now()->format('Y-m-d H:i:s');
        $data['page_title']             = 'Inicio';
        
        $data['categories'] = Category::with(
            [
                'products' => function ($q) {
                    return $q->with('brand')->whereHas('categories');
                },
                'products.reviews',
                'products.offers',
                'products.stocks' => function ($q) {
                    $q->where('quantity', '>', 0);
                },
            ]

        )
            ->whereHas('products', function ($query) {
                $query->whereHas('stocks', function ($query) {
                    $query->where('quantity', '>', 0);
                });
            })
            ->where('in_filter_menu',  '1')
            ->orderByRaw(
                "case when position is null then 1 else 0 end, position"
            )
            ->paginate(6);
            
        $rate = $this->getRate();
        return view($this->activeTemplate . 'home', $data);
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
                    return $q->with('brand')->whereHas('categories');
                },
                'products.reviews',
                'products.offers',
                'products.stocks' => function ($q) {
                    $q->where('quantity', '>', 0);
                },
            ]

        )
            ->whereHas('products', function ($query) {
                $query->whereHas('stocks', function ($query) {
                    $query->where('quantity', '>', 0);
                });
            })
            ->where('in_filter_menu',  '1')
            ->orderByRaw(
                "case when position is null then 1 else 0 end, position"
            )
            ->paginate(6);

        // dd( $categories);

        return view(activeTemplate() . 'sections.carrusel_product_scroll_infinit', ['data' => $categories, 'index' => $index]);
    }

    public function more_products2021(Request $request)
    {
        if (!isset(request()->perpage)) {
            $perpage    = 15;
        } else {
            $perpage    = request()->perpage;
        }

        $page = $request->page;
        $index = $request->page + 3;
        $category_id = Category::select('id')->get();

        // if (count($category_id) >= $page) {
        $products = Product::with(
            [
                'categories',
                'offer',
                'offer.activeOffer',
                'reviews',
                'stocks' => function ($query) {
                    $query->latest()->get(); //el ultimo stock registrado
                },
                'productIva',
            ]
        )
        ->where('show_in_frontend',  '1')
            ->where('is_plan', 0)
            ->whereHas('categories')
            ->orderBy('id', 'desc')
            ->get();

        $data = Category::with(
            [
                'products' => function ($q) {
                    $q->whereHas('categories');
                },
                'products.reviews',
                'products.offers',
                'products.stocks' => function ($q) {
                    $q->where('quantity', '>', 0);
                },
            ]
        )
            ->where('in_filter_menu', 0)
            ->whereHas('products')->take($request->page + 3)->get();

        //Valido si los productos de las categorias tienen stock
        $categoryProductFilter = [];
        foreach ($data as $key => $item) {
            foreach ($item->products as $product) {
                if ((!is_null($product->stocks)) && (count($product->stocks) > 0)) {
                    $categoryProductFilter[$key] = $item;
                }
            }
        }

        return view(activeTemplate() . 'sections.categories_products', ['data' => $categoryProductFilter, 'index' => $index]);
        // }
    }

    public function productSearch(Request $request)
    {

        $date_now = Carbon::now()->format('Y-m-d H:i:s');
        $page_title     = 'Buscar Productos: ' . $request->search_key;
        $empty_message  = 'Sin resultados';
        $search_key     = $request->search_key;
        $category_id    = $request->category_id;

        if (!isset(request()->perpage)) {
            $perpage    = 30;
        } else {
            $perpage    = request()->perpage;
        }

        //DB::statement("ALTER TABLE products ADD FULLTEXT(name, description)");

        if ($category_id == 0 || $category_id == null) {
            $products_like = Product::with(
                [
                    'categories',
                    'offer',
                    'offer.activeOffer',
                    'reviews',
                    'brand',
                    'stocks' => function ($q) {
                        $q->where('quantity', '>', 0);
                    },
                    'productIva'
                ]
            )
                ->where(function ($product) use ($search_key) {
                    $product->where('name', 'like', "%" . $search_key . "%");
                })
                ->where('show_in_frontend',  '1')
                ->where('is_plan', 0)
                ->whereHas('categories')
                ->whereHas('stocks', function ($p) {
                    //$p->whereHas('amounts', function ($t) {
                    $p->where('quantity', '>', '0');
                    //});
                })
                //->orderBy('name', 'desc')
                ->orderByRaw(
                    "CASE WHEN name = '" . $search_key . "' THEN 0  
							   WHEN name LIKE '" . $search_key . "%' THEN 1  
							   WHEN name LIKE '%" . $search_key . "%' THEN 2  
							   WHEN name LIKE '%" . $search_key . "' THEN 3  
							   ELSE 4
						  END, name ASC"
                )
                ->paginate($perpage);

            $products_match = Product::select('*')
                ->selectRaw('
                            match(name, description) 
                            against(? in natural language mode) as score
                        ', [$search_key])
                ->whereRaw('
                            match(name, description) 
                            against(? in natural language mode) > 0.0000001
                        ', [$search_key])
                ->with(
                    [
                        'categories',
                        'offer',
                        'offer.activeOffer',
                        'reviews',
                        'brand',
                        'stocks' => function ($q) {
                            $q->where('quantity', '>', 0);
                        },
                        'productIva'
                    ]
                )
                ->where('show_in_frontend',  '1')
                ->where('is_plan', 0)
                ->whereHas('categories')
                ->whereHas('stocks', function ($p) {
                    //$p->whereHas('amounts', function ($t) {
                    $p->where('quantity', '>', '0');
                    //});
                })
                //->orderBy('name', 'desc')
                ->orderByRaw(
                    "CASE WHEN name = '" . $search_key . "' THEN 0  
							   WHEN name LIKE '" . $search_key . "%' THEN 1  
							   WHEN name LIKE '%" . $search_key . "%' THEN 2  
							   WHEN name LIKE '%" . $search_key . "' THEN 3  
							   ELSE 4
						  END, name ASC"
                )
                ->paginate($perpage);
        } else {
            $products_like   = Category::where('id', $category_id)
                // ->where('parent_id', $category_id)
                // ->orWhereNull('parent_id')
                ->firstOrFail()->products()
                ->with(
                    [
                        'categories',
                        'offer',
                        'offer.activeOffer',
                        'reviews',
                        'brand',
                        'stocks' => function ($q) {
                            $q->where('quantity', '>', 0);
                        },
                    ]
                )
                ->whereHas('categories')
                ->whereHas('stocks', function ($p) {
                    //$p->whereHas('amounts', function ($t) {
                    $p->where('quantity', '>', '0');
                    //});
                })
                ->where(function ($query) use ($search_key) {
                    return $query->where('name', 'like', "%{$search_key}%")
                        ->orWhere('summary', 'like', "%{$search_key}%")
                        ->orWhere('description', 'like', "%{$search_key}%");
                })
                ->paginate($perpage);
            $products_like->where('parent_id', $category_id);
        }

        if ($request->ajax()) {
            $view = 'partials.products_serarch_filter';
        } else {
            $view = 'products_search';
        }

        $products = isset($products_match) ? $products_match->count() > 0 ? $products_match : $products_like : $products_like;

        return view($this->activeTemplate . $view, compact('page_title', 'products', 'empty_message', 'search_key', 'category_id', 'perpage'));
    }

    public function more_product_search(Request $request)
    {

        $search_key     = $request->search;
        $category_id    = $request->id;
        $brand                  = $request->brand ? $request->brand : ['0'];

        if (!isset(request()->page)) {
            $page = null;
        } else {
            $page = request()->page;
        }

        if (!isset(request()->perpage)) {
            $perpage    = 30;
        } else {
            $perpage    = request()->perpage;
        }

        if ($category_id == 0 || $category_id == null) {
            $products_like = Product::with(
                [
                    'categories',
                    'offer',
                    'offer.activeOffer',
                    'reviews',
                    'brand',
                    'stocks' => function ($q) {
                        $q->where('quantity', '>', 0);
                    },
                    'productIva'
                ]
            )
                ->where(function ($product) use ($search_key) {
                    $product->where('name', 'like', "%" . $search_key . "%");
                })
                ->where('show_in_frontend',  '1')
                ->where('is_plan', 0)
                ->whereHas('categories')
                ->whereHas('stocks', function ($p) {
                    //$p->whereHas('amounts', function ($t) {
                    $p->where('quantity', '>', '0');
                    //});
                })
                ->orderBy('name', 'desc')
                ->paginate($perpage);

            $products_match = Product::select('*')
                ->selectRaw('
                            match(name, description) 
                            against(? in natural language mode) as score
                        ', [$search_key])
                ->whereRaw('
                            match(name, description) 
                            against(? in natural language mode) > 0.0000001
                        ', [$search_key])
                ->with(
                    [
                        'categories',
                        'offer',
                        'offer.activeOffer',
                        'reviews',
                        'brand',
                        'stocks' => function ($q) {
                            $q->where('quantity', '>', 0);
                        },
                        'productIva'
                    ]
                )
                ->where('show_in_frontend',  '1')
                ->where('is_plan', 0)
                ->whereHas('categories')
                ->whereHas('stocks', function ($p) {
                    //$p->whereHas('amounts', function ($t) {
                    $p->where('quantity', '>', '0');
                    //});
                })
                ->orderBy('name', 'desc')
                ->paginate($perpage);
        } else {
            $category = Category::whereId($request->id)->firstOrFail();

            $all_products           = $category->products()
                ->with('categories', 'offer', 'offer.activeOffer', 'brand', 'reviews')
                ->whereHas('categories')
                ->whereHas('stocks', function ($p) {
                    //$p->whereHas('amounts', function ($t) {
                    $p->where('quantity', '>', '0');
                    //});
                })
                ->where(function ($query) use ($search_key) {
                    return $query->where('name', 'like', "%{$search_key}%")
                        ->orWhere('summary', 'like', "%{$search_key}%")
                        ->orWhere('description', 'like', "%{$search_key}%");
                })
                ->get();

            if (in_array("0", $brand)) {
                $productCollection  = $all_products;
            } else {
                $productCollection  = $all_products->whereIn('brand.id', $brand);
            }

            $products_like =  paginate($productCollection, $perpage, $page, $options = []);
            //dd($category_id);
            /* $products_like   = Category::where('id', $category_id)
                // ->where('parent_id', $category_id)
                // ->orWhereNull('parent_id')
                ->firstOrFail()->products()
                ->with(
                    [
                        'categories',
                        'offer',
                        'offer.activeOffer',
                        'reviews',
                        'brand',
                        'stocks' => function ($q) {
                            $q->where('quantity', '>', 0);
                        },
                    ]
                )
                ->whereHas('categories')
                ->where(function ($query) use ($search_key) {
                    return $query->where('name', 'like', "%{$search_key}%")
                        ->orWhere('summary', 'like', "%{$search_key}%")
                        ->orWhere('description', 'like', "%{$search_key}%");
                })
                ->paginate($perpage,$page);
                dd( $products_like);
            $products_like->where('parent_id', $category_id);*/
        }

        $products = isset($products_match) ? $products_match->count() > 0 ? $products_match : $products_like : $products_like;

        $view = 'partials.products_scroll_infinite';

        return view($this->activeTemplate . $view, compact('products'));
    }

    public function products(Request $request)
    {
        $brands                 = Brand::latest()->get();
        $categories             = Category::where('parent_id', null)->latest()->get();
        $page_title             = 'Productos';
        $brand                  = $request->brand ? $request->brand : ['0'];
        $category_id            = $request->category_id ?? 0;
        $min                    = $request->min;
        $max                    = $request->max;

        if (!isset(request()->perpage)) {
            $perpage = 30;
        } else {
            $perpage = request()->perpage;
        }

        if ($category_id != 0) {
            $all_products       = Category::where('id', $category_id)
                ->first()
                ->products()
                ->with('categories', 'offer', 'offer.activeOffer', 'reviews', 'brand')
                ->whereHas('categories')
                //->whereHas('brand')
                ->get();
        } else {
            $all_products       = Product::with('categories', 'offer', 'offer.activeOffer', 'reviews', 'brand', 'productIva')
            ->where('show_in_frontend',  '1')    
            ->where('is_plan', 0)
                ->whereHas('categories')
                //->whereHas('brand')
                ->get();
        }

        $min_price              = $all_products->min('base_price') ?? 0;
        $max_price              = $all_products->max('base_price') ?? 0;
        if (in_array("0", $brand)) {
            $productCollection  = $all_products;
        } else {
            $productCollection  = $all_products->whereIn('brand.id', $brand);
        }

        if ($min && $max) {
            $productCollection = $productCollection->where('base_price', '>=', $min)->where('base_price', '<=', $max);
        } elseif ($min) {
            $productCollection = $productCollection->where('base_price', '>=', $min);
        } elseif ($max) {
            $productCollection = $productCollection->where('base_price', '<=', $max);
        }

        $products           =  paginate($productCollection, $perpage, $page = null, $options = []);

        if ($request->ajax()) {
            $view = 'partials.products_filter';
        } else {
            $view = 'products';
        }

        $empty_message = "Disculpe! Sin resultados.";

        return view($this->activeTemplate . $view, compact('products', 'perpage', 'brand', 'min_price', 'max_price', 'page_title', 'brands', 'min', 'max', 'category_id', 'empty_message'));
    }

    public function productsRecent(Request $request)
    {
        $brands                 = Brand::latest()->get();
        $categories             = Category::where('parent_id', null)->latest()->get();
        $page_title             = 'Producto Recientes';
        $brand                  = $request->brand ? $request->brand : ['0'];
        $category_id            = $request->category_id ?? 0;
        $min                    = $request->min;
        $max                    = $request->max;

        if (!isset(request()->perpage)) {
            $perpage = 30;
        } else {
            $perpage = request()->perpage;
        }

        if ($category_id != 0) {
            $all_products       = Category::where('id', $category_id)
                ->first()
                ->products()
                ->with('categories', 'offer', 'offer.activeOffer', 'reviews', 'brand')
                ->whereHas('categories')
                ->whereHas('stocks', function ($p) {
                    //$p->whereHas('amounts', function ($t) {
                    $p->where('quantity', '>', '0');
                    //});
                })
                ->orderBy('id', 'desc')
                //->whereHas('brand')
                ->get();
        } else {
            $all_products       = Product::with('categories', 'offer', 'offer.activeOffer', 'reviews', 'brand', 'productIva')
            ->where('show_in_frontend',  '1')    
            ->where('is_plan', 0)
                ->where('show_in_frontend',  '1')
                ->whereHas('categories')
                ->orderBy('id', 'desc')
                ->whereHas('stocks', function ($p) {
                    //$p->whereHas('amounts', function ($t) {
                    $p->where('quantity', '>', '0');
                    //});
                })
                //->whereHas('brand')
                ->get();
        }

        $min_price              = $all_products->min('base_price') ?? 0;
        $max_price              = $all_products->max('base_price') ?? 0;
        if (in_array("0", $brand)) {
            $productCollection  = $all_products;
        } else {
            $productCollection  = $all_products->whereIn('brand.id', $brand);
        }

        if ($min && $max) {
            $productCollection = $productCollection->where('base_price', '>=', $min)->where('base_price', '<=', $max);
        } elseif ($min) {
            $productCollection = $productCollection->where('base_price', '>=', $min);
        } elseif ($max) {
            $productCollection = $productCollection->where('base_price', '<=', $max);
        }

        $products           =  paginate($productCollection, $perpage, $page = null, $options = []);

        // if ($request->ajax()) {
        //     $view = 'partials.products_filter';
        //} else {
        $view = 'plugins.list_products_recent';
        // }

        $empty_message = "Disculpe! Sin resultados.";

        return view($this->activeTemplate . $view, compact('products', 'perpage', 'brand', 'min_price', 'max_price', 'page_title', 'brands', 'min', 'max', 'category_id', 'empty_message'));
    }

    public function productsBestsellers(Request $request)
    {
        $brands                 = Brand::latest()->get();
        $categories             = Category::where('parent_id', null)->latest()->get();
        $page_title             = 'Productos - Más Vendidos';
        $brand                  = $request->brand ? $request->brand : ['0'];
        $category_id            = $request->category_id ?? 0;
        $min                    = $request->min;
        $max                    = $request->max;

        if (!isset(request()->perpage)) {
            $perpage = 30;
        } else {
            $perpage = request()->perpage;
        }

        /*if ($category_id != 0) {
            $all_products       = Category::where('id', $category_id)
                ->first()
                ->products()
                ->with('categories', 'offer', 'offer.activeOffer', 'reviews', 'brand')
                ->whereHas('categories')
                //->whereHas('brand')
                ->get();
        } else {*/
        $all_products       = Product::topSales(30);
        // }

        $min_price              = $all_products->min('base_price') ?? 0;
        $max_price              = $all_products->max('base_price') ?? 0;
        if (in_array("0", $brand)) {
            $productCollection  = $all_products;
        } else {
            $productCollection  = $all_products->whereIn('brand.id', $brand);
        }

        if ($min && $max) {
            $productCollection = $productCollection->where('base_price', '>=', $min)->where('base_price', '<=', $max);
        } elseif ($min) {
            $productCollection = $productCollection->where('base_price', '>=', $min);
        } elseif ($max) {
            $productCollection = $productCollection->where('base_price', '<=', $max);
        }

        $products           =  paginate($productCollection, $perpage, $page = null, $options = []);

        //if ($request->ajax()) {
        //   $view = 'partials.products_filter';
        //} else {
        $view = 'plugins.list_products_bestsellers';
        // }

        $empty_message = "Disculpe! Sin resultados.";

        return view($this->activeTemplate . $view, compact('products', 'perpage', 'brand', 'min_price', 'max_price', 'page_title', 'brands', 'min', 'max', 'category_id', 'empty_message'));
    }

    public function productsOffers(Request $request)
    {
        $brands                 = Brand::latest()->get();
        $categories             = Category::where('parent_id', null)->latest()->get();
        $page_title             = 'Ofertas';
        $brand                  = $request->brand ? $request->brand : ['0'];
        $category_id            = $request->category_id ?? 0;
        $min                    = $request->min;
        $max                    = $request->max;

        $perpage = 30;


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
        ->where('show_in_frontend',  '1')
            ->where('is_plan', 0)
            ->whereHas('categories')
            ->whereHas('offer.activeOffer')
            ->get();


        $min_price              = $all_products->min('base_price') ?? 0;
        $max_price              = $all_products->max('base_price') ?? 0;
        if (in_array("0", $brand)) {

            $productCollection  = $all_products;
        } else {
            $productCollection  = $all_products->whereIn('brand.id', $brand);
        }

        if ($min && $max) {
            $productCollection = $productCollection->where('base_price', '>=', $min)->where('base_price', '<=', $max);
        } elseif ($min) {
            $productCollection = $productCollection->where('base_price', '>=', $min);
        } elseif ($max) {
            $productCollection = $productCollection->where('base_price', '<=', $max);
        }

        $products           =  paginate($productCollection, $perpage, $page = null, $options = []);

        if ($request->ajax()) {
            $view = 'partials.products_filter';
        } else {
            $view = 'products';
        }

        $empty_message = "Disculpe! Sin resultados.";


        return view($this->activeTemplate . $view, compact('products', 'perpage', 'brand', 'min_price', 'max_price', 'page_title', 'brands', 'min', 'max', 'category_id', 'empty_message'));
    }

    public function more_product_offers(Request $request)
    {

        $brands                 = Brand::latest()->get();
        $categories             = Category::where('parent_id', null)->latest()->get();
        $page_title             = 'Ofertas';
        $brand                  = $request->brand ? $request->brand : ['0'];
        $category_id            = $request->category_id ?? 0;
        $min                    = $request->min;
        $max                    = $request->max;

        if (!isset(request()->page)) {
            $page = null;
        } else {
            $page = request()->page;
        }

        if (!isset(request()->perpage)) {
            $perpage    = 30;
        } else {
            $perpage    = request()->perpage;
        }

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
        ->where('show_in_frontend',  '1')
            ->where('is_plan', 0)
            ->whereHas('categories')
            ->whereHas('offer.activeOffer')
            ->get();


        $min_price              = $all_products->min('base_price') ?? 0;
        $max_price              = $all_products->max('base_price') ?? 0;
        if (in_array("0", $brand)) {
            $productCollection  = $all_products;
        } else {
            $productCollection  = $all_products->whereIn('brand.id', $brand);
        }

        if ($min && $max) {
            $productCollection = $productCollection->where('base_price', '>=', $min)->where('base_price', '<=', $max);
        } elseif ($min) {
            $productCollection = $productCollection->where('base_price', '>=', $min);
        } elseif ($max) {
            $productCollection = $productCollection->where('base_price', '<=', $max);
        }

        $products           =  paginate($productCollection, $perpage, $page, $options = []);

        $view = 'plugins.list_products';

        $empty_message = "Disculpe! Sin resultados.";


        return view($this->activeTemplate . $view, compact('products'));
    }

    public function productsByCategory(Request $request, $id)
    {
        $category               = Category::whereId($id)->firstOrFail();
        $page_title             = 'Productos por Categoría - ' . $category->name;

        $categories             = Category::where('id', '<>', 0)->get();
        $brand                  = $request->brand ? $request->brand : ['0'];
        $min                    = $request->min;
        $max                    = $request->max;

        if (!isset(request()->page)) {
            $page = null;
        } else {
            $page = request()->page;
        }

        if (!isset(request()->perpage)) {
            $perpage = 30;
        } else {
            $perpage = request()->perpage;
        }
        // dd($perpage);

        $all_products           = $category->products()
            ->with('categories', 'offer', 'offer.activeOffer', 'brand', 'reviews')
            ->whereHas('categories')
            //->whereHas('brand')
            ->get();

        $min_price              = $all_products->min('base_price') ?? 0;
        $max_price              = $all_products->max('base_price') ?? 0;

        $brands                 = collect($all_products->pluck('brand'));

        if (in_array("0", $brand)) {
            $productCollection  = $all_products;
        } else {
            $productCollection  = $all_products->whereIn('brand.id', $brand);
        }

        if ($min && $max) {
            $productCollection = $productCollection->where('base_price', '>=', $min)->where('base_price', '<=', $max);
        } elseif ($min) {
            $productCollection = $productCollection->where('base_price', '>=', $min);
        } elseif ($max) {
            $productCollection = $productCollection->where('base_price', '<=', $max);
        }

        $products           =  paginate($productCollection, $perpage, $page, $options = []);

        if ($request->ajax()) {
            $view = 'partials.products_filter';
        } else {
            $view = 'products_by_category';
        }

        $empty_message = "¡Disculpe! Sin resultados.";

        $seo_contents['meta_title']         = $category->meta_title;
        $seo_contents['meta_description']   = $category->meta_description;
        $seo_contents['meta_keywords']      = $category->meta_keywords;
        $seo_contents['image']              = getImage(imagePath()['category']['path'] . '/' . $category->image);
        $seo_contents['image_size']         = imagePath()['category']['size'];

        $subcategory = Category::where('parent_id', $id)
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

        //   dd($products);

        return view($this->activeTemplate . $view, compact('products', 'perpage', 'brand', 'min_price', 'max_price', 'page_title', 'empty_message', 'min', 'max', 'category', 'brands', 'seo_contents', 'subcategory'));
    }

    public function more_products_category(Request $request)
    {

        $category = Category::whereId($request->id)->firstOrFail();
        $page_title             = 'Productos por Categoría - ' . $category->name;
        $categories             = Category::where('id', '<>', 0)->get();
        $brand                  = $request->brand ? $request->brand : ['0'];
        $min                    = $request->min;
        $max                    = $request->max;

        if (!isset(request()->page)) {
            $page = null;
        } else {
            $page = request()->page;
        }

        if (!isset(request()->perpage)) {
            $perpage = 30;
        } else {
            $perpage = request()->perpage;
        }

        $all_products           = $category->products()
            ->with('categories', 'offer', 'offer.activeOffer', 'brand', 'reviews')
            ->whereHas('categories')
            //->whereHas('brand')
            ->get();

        if (in_array("0", $brand)) {
            $productCollection  = $all_products;
        } else {
            $productCollection  = $all_products->whereIn('brand.id', $brand);
        }

        $products =  paginate($productCollection, $perpage, $page, $options = []);

        $view = 'partials.products_scroll_infinite';

        return view($this->activeTemplate . $view, compact('products'));
    }

    public function productsByBrand(Request $request, $id)
    {
        $brand                  = Brand::whereId($id)->firstOrFail();
        $page_title             = 'Productos por Marca - ' . $brand->name;

        $categories             = Category::where('parent_id', null)->latest()->get();
        $category_id            = $request->category_id ?? 0;
        $min                    = $request->min;
        $max                    = $request->max;

        if (!isset(request()->perpage)) {
            $perpage = 15;
        } else {
            $perpage = request()->perpage;
        }

        if ($category_id != 0) {
            $all_products       = Category::where('id', $category_id)->first()->products()->where('brand_id', $id)->with('categories', 'offer', 'offer.activeOffer', 'brand', 'reviews')
                ->whereHas('categories')
                //->whereHas('brand')
                ->get();
        } else {
            $all_products       = $brand->products()->where('brand_id', $id)->with('categories', 'offer', 'offer.activeOffer', 'brand', 'reviews')
                ->whereHas('categories')
                //->whereHas('brand')
                ->get();
        }

        $productCollection = $all_products;

        if ($min && $max) {
            $productCollection = $productCollection->where('base_price', '>=', $min)->where('base_price', '<=', $max);
        } elseif ($min) {
            $productCollection = $productCollection->where('base_price', '>=', $min);
        } elseif ($max) {
            $productCollection = $productCollection->where('base_price', '<=', $max);
        }


        $min_price              = $all_products->min('base_price') ?? 0;
        $max_price              = $all_products->max('base_price') ?? 0;

        $products           =  paginate($productCollection, $perpage, $page = null, $options = []);

        if ($request->ajax()) {
            $view = 'partials.products_filter';
        } else {
            $view = 'products_by_brand';
        }

        $empty_message = "Disculpe! Sin resultados.";

        $seo_contents['meta_title']         = $brand->meta_title;
        $seo_contents['meta_description']   = $brand->meta_description;
        $seo_contents['meta_keywords']      = $brand->meta_keywords;
        $seo_contents['image']              = getImage(imagePath()['brand']['path'] . '/' . $brand->logo);
        $seo_contents['image_size']         = imagePath()['brand']['size'];

        return view($this->activeTemplate . $view, compact('products', 'perpage', 'brand', 'min_price', 'max_price', 'page_title', 'empty_message', 'min', 'max', 'category_id', 'seo_contents'));
    }

    public function productDetails($id, $order_id = null)
    {
        $date_now = Carbon::now()->format('Y-m-d H:i:s');
        $review_available = false;

        if ($order_id) {
            $order = Order::where('order_number', $order_id)->where('user_id', auth()->id())->first();
            if ($order) {
                $od = OrderDetail::where('order_id', $order->id)->where('product_id', $id)->first();
                if ($od) {
                    $review_available = true;
                }
            }
        }

        $product = Product::where('id', $id)
        ->where('show_in_frontend',  '1')
            ->where('is_plan', 0)
            ->with('categories', 'assignAttributes', 'offer', 'offer.activeOffer', 'reviews', 'productImages')
            ->whereHas('categories')
            // ->whereHas('brand')
            ->first();

        if (!$product) {
            abort('404');
        }

        $images = $product->productPreviewImages;

        if ($images->count() == 0) {
            $images = $product->productVariantImages;
        }

        if (optional($product->offer)->activeOffer) {
            $discount = calculateDiscount($product->offer->activeOffer->amount, $product->offer->activeOffer->discount_type, $product->base_price);
        } else $discount = 0;

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
            ->map(function ($item) use ($id) {
                return $item->products->where('id', '!=', $id)
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
                        $related_products[] = $value;
                    }
                }
            }
        }

        // foreach ($array as $childArray){
        //     foreach ($childArray as $value){
        //         if(isset($value->stocks) && (!$value->stocks->isEmpty())){
        //             foreach($value->stocks as $stock){
        //                 if($stock->quantity > 0){
        //                     $related_products[] = $value;
        //                 }
        //             }                                   
        //         }
        //     }
        // }

        $attributes     = AssignProductAttribute::where('status', 1)->with('productAttribute')->where('product_id', $id)->distinct('product_attribute_id')->get(['product_attribute_id']);

        $seo_contents['meta_title']         = $product->meta_title;
        $seo_contents['meta_description']   = $product->meta_description;
        $seo_contents['meta_keywords']      = $product->meta_keywords;
        $seo_contents['image']              = getImage(imagePath()['product']['path'] . '/' . $product->main_image);
        $seo_contents['image_size']         = imagePath()['category']['size'];


        $page_title = 'Detalles del Producto';
        return view($this->activeTemplate . 'product_details', compact('product', 'page_title', 'review_available', 'related_products', 'discount', 'attributes', 'images', 'seo_contents'));
    }

    //plans
    public function plans(Request $request)
    {
        $brands                 = Brand::latest()->get();
        $categories             = Category::where('parent_id', null)->latest()->get();
        $page_title             = 'Planes';
        $brand                  = $request->brand ? $request->brand : ['0'];
        $category_id            = $request->category_id ?? 0;
        $min                    = $request->min;
        $max                    = $request->max;

        if (!isset(request()->perpage)) {
            $perpage = 15;
        } else {
            $perpage = request()->perpage;
        }

        if ($category_id != 0) {
            $all_products       = Category::where('id', $category_id)
                ->first()
                ->products()
                ->with('categories', 'offer', 'offer.activeOffer', 'reviews', 'brand')
                ->whereHas('categories')
                //->whereHas('brand')
                ->get();
        } else {
            $all_products       = Product::with(
                [
                    'planDetails' => function ($query) {
                        return $query->where('status', 1);
                    },
                    'stocks' => function ($query) {
                        $query->latest()->get(); //el ultimo stock registrado
                    },
                    'productIva',
                ]
            )
                ->where('is_plan', 1)
                ->get();
        }

        $min_price              = $all_products->min('base_price') ?? 0;
        $max_price              = $all_products->max('base_price') ?? 0;
        if (in_array("0", $brand)) {
            $productCollection  = $all_products;
        } else {
            $productCollection  = $all_products->whereIn('brand.id', $brand);
        }

        if ($min && $max) {
            $productCollection = $productCollection->where('base_price', '>=', $min)->where('base_price', '<=', $max);
        } elseif ($min) {
            $productCollection = $productCollection->where('base_price', '>=', $min);
        } elseif ($max) {
            $productCollection = $productCollection->where('base_price', '<=', $max);
        }

        $plans           =  paginate($productCollection, $perpage, $page = null, $options = []);

        if ($request->ajax()) {
            $view = 'plans'; //'partials.products_filter';
        } else {
            $view = 'plans'; //'products';
        }

        $empty_message = "Disculpe! Sin resultados.";

        return view($this->activeTemplate . $view, compact('plans', 'perpage', 'brand', 'min_price', 'max_price', 'page_title', 'brands', 'min', 'max', 'category_id', 'empty_message'));
    }

    public function quickView(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|gt:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $id = $request->id;
        $date_now = Carbon::now()->format('Y-m-d H:i:s');

        $review_available = false;

        $product = Product::where('id', $id)->where('show_in_frontend',  '1')->where('is_plan', 0)
            ->with('categories', 'offer', 'offer.activeOffer', 'reviews', 'productImages')
            ->whereHas('categories')
            //->whereHas('brand')
            ->first();

        if (!$product) {
            abort('404');
        }

        if (optional($product->offer)->activeOffer) {
            $discount = calculateDiscount($product->offer->activeOffer->amount, $product->offer->activeOffer->discount_type, $product->base_price);
        } else $discount = 0;


        $rProducts = $product->categories()->with('products', 'products.offer')->get()->map(function ($item) use ($id) {
            return $item->products->where('id', '!=', $id)->take(5);
        });

        $attributes     = AssignProductAttribute::where('status', 1)->where('product_id', $id)->distinct('product_attribute_id')->with('productAttribute')->get(['product_attribute_id']);


        $page_title = 'Product Details';
        return view($this->activeTemplate . 'partials.quick_view', compact('product', 'page_title', 'review_available', 'discount', 'attributes'));
    }

    public function brands()
    {
        $data['brands']         = Brand::latest()->paginate(30);
        $data['page_title']     = 'Marcas';
        $data['empty_message']  = 'Sin Marcas Encontradas';

        return view($this->activeTemplate . 'brands', $data);
    }

    public function categories()
    {
        $data['all_categories']      = Category::latest()->paginate(20);
        $data['page_title']     = 'Categorías';
        $data['empty_message']  = 'Sin Categorías Encontradas';

        return view($this->activeTemplate . 'categories', $data);
    }

    public function contact()
    {
        $data['page_title'] = "Contáctenos";
        return view($this->activeTemplate . 'contact', $data);
    }

    public function contactSubmit(Request $request)
    {
        $ticket = new SupportTicket();
        $message = new SupportMessage();
        $imgs = $request->file('attachments');
        $allowedExts = array('jpg', 'png', 'jpeg', 'pdf');

        $this->validate($request, [
            'attachments' => [
                'sometimes',
                'max:4096',
                function ($attribute, $value, $fail) use ($imgs, $allowedExts) {
                    foreach ($imgs as $img) {
                        $ext = strtolower($img->getClientOriginalExtension());
                        if (($img->getSize() / 1000000) > 2) {
                            return $fail("El Tamaño no debe superar los 2MB!");
                        }
                        if (!in_array($ext, $allowedExts)) {
                            return $fail("Solo png, jpg, jpeg, pdf son aceptadas");
                        }
                    }
                    if (count($imgs) > 5) {
                        return $fail("Se pueden cargar un máximo de 5 imágenes");
                    }
                },
            ],
            'name' => 'required|max:191',
            'email' => 'required|max:191',
            'subject' => 'required|max:100',
            'message' => 'required',
        ]);

        $random = getNumber();
        $ticket->user_id = auth()->id();
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status = 0;
        $ticket->save();
        $message->supportticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();
        $path = imagePath()['ticket']['path'];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $image) {
                try {
                    SupportAttachment::create([
                        'support_message_id' => $message->id,
                        'image' => uploadImage($image, $path),
                    ]);
                } catch (\Exception $exp) {
                    $notify[] = ['error', 'No se pudo cargar su ' . $image];
                    return back()->withNotify($notify)->withInput();
                }
            }
        }
        $notify[] = ['success', 'Ticket Creado Exitosamente!'];

        return redirect()->route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        if (!$language) $lang = 'es';
        session()->put('lang', $lang);
        return redirect()->back();
    }

    public function placeholderImage($size = null)
    {

        if ($size != 'undefined') {
            $size = $size;
            $imgWidth = explode('x', $size)[0];
            $imgHeight = explode('x', $size)[1];
            $text = $imgWidth . '×' . $imgHeight;
        } else {
            $imgWidth = 150;
            $imgHeight = 150;
            $text = 'Tamaño Indefinido';
        }
        $fontFile = realpath('assets/font') . DIRECTORY_SEPARATOR . 'RobotoMono-Regular.ttf';
        $fontSize = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgFill);
        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');


        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function trackOrder()
    {
        $page_title = 'Rastreo de orden';

        return view($this->activeTemplate . 'order_track', compact('page_title'));
    }

    public function getOrderTrackData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_number' => 'required|max:160',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $page_title = 'Rastreo de orden';


        $order_number   = $request->order_number;
        $order_data     = Order::where('order_number', $order_number)->first();
        if ($order_data) {
            $p_status   = $order_data->payment_status;
            $status     = $order_data->status;

            return response()->json(['success' => true, 'payment_status' => $p_status, 'status' => $status]);
        } else {
            $notify = 'Sin Órdenes Encontradas';
            return response()->json(['success' => false, 'message' => $notify]);
        }
    }

    public function addSubscriber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $if_exist = Subscriber::where('email', $request->email)->first();
        if (!$if_exist) {
            Subscriber::create([
                'email' => $request->email
            ]);
            return response()->json(['success' => 'Suscripción Realizada!']);
        } else {
            return response()->json(['error' => 'Ya está Suscrito!']);
        }
    }

    public function aboutUs()
    {
        $data            = getContent('about_page.content', true);
        $page_title      = $data->data_values->page_title ?? '';


        return view($this->activeTemplate . 'page', compact('page_title', 'data'));
    }

    public function faqs()
    {
        $data            = getContent('faq_page.content', true);
        $page_title      = $data->data_values->page_title ?? '';
        return view($this->activeTemplate . 'page', compact('page_title', 'data'));
    }

    public function addToCompare(Request $request)
    {
        $id         = $request->product_id;
        $product    = Product::where('id', $id)->where('show_in_frontend',  '1')->where('is_plan', 0)->with('categories')->first();

        $compare            = session()->get('compare');
        if ($compare) {
            $reset_compare      = reset($compare);
            $prev_product   = Product::where('id', $reset_compare['id'])->where('show_in_frontend',  '1')->where('is_plan', 0)->with('categories')->first();

            $not_same       = empty(array_intersect($product->categories->pluck('id')->toArray(), $prev_product->categories->pluck('id')->toArray()));

            if ($not_same) {
                return response()->json(['error' => 'Ya se ha agregado un tipo diferente de producto a su lista de comparación. Agregue un producto de tipo similar para comparar o borrar su lista de comparación']);
            }
            if (count($compare) > 2) {
                return response()->json(['error' => 'No puede agregar más de 3 productos en la lista de comparación']);
            }
        }

        if (!$compare) {

            $compare = [
                $id => [
                    "id" => $product->id
                ]
            ];
            session()->put('compare', $compare);
            return response()->json(['success' => 'Agregado a la lista de comparación']);
        }

        // if compare list is not empty then check if this product exist
        if (isset($compare[$id])) {
            return response()->json(['error' => 'Ya en la lista de comparación']);
        }
        $compare[$id] = [
            "id" => $product->id
        ];

        session()->put('compare', $compare);
        return response()->json(['success' => 'Agregado a la lista de comparación']);
    }

    public function compare()
    {
        $date_now = Carbon::now()->format('Y-m-d H:i:s');
        $data       = session()->get('compare');

        $products   = [];

        if ($data) {
            foreach ($data as $key => $val) {
                array_push($products, $key);
            }
        }

        $compare_data   = Product::with('categories', 'offer', 'offer.activeOffer', 'reviews', 'productIva')
        ->where('show_in_frontend',  '1')
            ->where('is_plan', 0)
            ->whereHas('categories')
            //->whereHas('brand')
            ->whereIn('id', $products)->get();

        $compare_items = $compare_data->take(4);

        $page_title = 'Product Comparison';
        $empty_message = 'Comparison list is empty';
        return view($this->activeTemplate . 'compare', compact('page_title', 'compare_items', 'empty_message'));
    }

    public function getCompare()
    {
        $date_now = Carbon::now()->format('Y-m-d H:i:s');
        $data       = session()->get('compare');

        if (!$data) {
            return response(['total' => 0]);
        }

        $products   = [];
        foreach ($data as $key => $val) {
            array_push($products, $key);
        }

        $compare_data   = Product::with('categories', 'offer', 'offer.activeOffer', 'reviews', 'productIva')
        ->where('show_in_frontend',  '1')
            ->where('is_plan', 0)
            ->whereHas('categories')
            //->whereHas('brand')
            ->whereIn('id', $products)->get();
        return response(['total' => count($compare_data)]);
    }

    public function removeFromcompare($id)
    {
        $compare = session()->get('compare');

        if (isset($compare[$id])) {
            unset($compare[$id]);
            session()->put('compare', $compare);
            $notify[] = ['success', 'Eliminado de la Lista de Comparación'];
            return response()->json(['message' => 'Removido']);
        }

        return response()->json(['error' => 'Algo salió mal']);
    }

    public function loadMore(Request $request)
    {
        $reviews = ProductReview::where('product_id', $request->pid)->latest()->paginate(5);
        return view($this->activeTemplate . 'partials.product_review', compact('reviews'));
    }

    public function getStockByVariant(Request $request)
    {
        $pid    = $request->product_id;
        $attr   = json_decode($request->attr_id);
        sort($attr);
        $attr = json_encode($attr);

        $stock  = ProductStock::where('product_id', $pid)->where('attributes', $attr)->first();

        return response()->json(['sku' => $stock->sku ?? 'No Disponible', 'quantity' => $stock->quantity ?? 0]);
    }

    public function getImageByVariant(Request $request)
    {
        $variant = AssignProductAttribute::whereId($request->id)->with('productImages')->firstOrFail();
        $images         = $variant->productImages;

        if ($images->count() > 0) {
            return view($this->activeTemplate . 'partials.variant_images', compact('images'));
        } else {
            return response()->json(['error' => true]);
        }
    }

    public function page(Frontend $id)
    {
        $data           = $id;
        $page_title     = $id->data_values->page_title ?? '';
        return view($this->activeTemplate . 'page', compact('page_title', 'data'));
    }

    public function printInvoice(Order $order)
    {
        $page_title = 'Imprimir factura';
        $order = Order::where('id', $order->id)->with('deposit', 'user.plan_users', 'orderDetail.product', 'shipping')->first();

        $discountPrime = 0;
        //si es usuario prime, calculamos el descuento de productos prime
        if (count($order->user->plan_users) > 0) {
            foreach ($order->user->plan_users as $plan) {
                if ($plan->status == 1) { //activo
                    $sum_base = 0;
                    $sum_prime = 0;
                    foreach ($order->orderDetail as $od) {
                        $qty = 1;
                        while ($qty <= $od->quantity) {
                            $sum_base += $od->base_price != $od->prime_price ? $od->base_price : $od->product->base_price;
                            $sum_prime += $od->prime_price > 0 ? $od->prime_price : $od->product->base_price;
                            $qty++;
                        }
                    }
                    $discountPrime = ($sum_base - $sum_prime);
                }
            }
        }



        return view('invoice.print', compact('page_title', 'order', 'discountPrime'));
    }

    public function exportInvoice(Order $order)
    {
        return (new OrderExport($order->id))->download('order-' . $order->id . '.xlsx');
    }

    public function setMoneda(Request $request)
    {
        $moneda = $request['moneda'];
       // $moneda = session()->get('moneda');

        switch ($moneda) {
            case 'Dolares':
                session()->put('moneda', 'Dolares');
                session()->save();
                $moneda = session()->get('moneda');
                break;

            case 'Bolívares':
                session()->put('moneda', 'Bolívares');
                session()->save();
                $moneda = session()->get('moneda');
                break;

            case 'Euros':
                session()->put('moneda', 'Euros');
                session()->save();
                $moneda = session()->get('moneda');
                break;
            default:
                session()->put('moneda', 'Dolares');
                session()->save();
                $moneda = session()->get('moneda');
                break;
        }

        return response()->json(['moneda' => $moneda]);
    }

    public function getMoneda(Request $request)
    {

        if (session()->has('moneda')) {
            $moneda = session()->get('moneda');
        } else {
            session()->put('moneda', 'Dolares');
            session()->save();
            $moneda = session()->get('moneda');
        }

        return response()->json(['moneda' => $moneda]);
    }

    public function getRate()
    {
        $rate = Rates::select('tasa_del_dia')->where('status', '1')->where('type', session()->get('moneda'))->orderBy('id', 'desc')->first();
        //dd($rate);
        if ($rate) {
            session()->put('rate', $rate->tasa_del_dia);
            session()->save();
            //dd(session()->get('rate'));
            return response()->json(['rate' => $rate->tasa_del_dia]);
        }
        return null;
    }

    public function route(Request $request)
    {
        // dd($request->all());
        return route('products.category', ['id' => $request->id, 'slug' => slug($request->slug)]);
    }

    public function megasoft(Request $request){
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://paytest.megasoft.com.ve/payment/action/paymentgatewayuniversal-prereg?cod_afiliacion=2606202301&monto=" . $request->total,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_COOKIE => "JSESSIONID=22F8EC805F50F4B1FD0ADE2E26311490",
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic d3BhZ29zOkNhcmFjYXMxLg=="
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // echo json_encode(['control' => $response]);
        // }

            // $curl = curl_init();

            session()->put('megasoft-control', $response);
            header("Location: https://paytest.megasoft.com.ve/payment/action/paymentgatewayuniversal-data?control=". $response);
            die();
            // curl_setopt_array($curl, [
            //     CURLOPT_URL => "https://paytest.megasoft.com.ve/payment/action/paymentgatewayuniversal-data?control=". $response,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => "",
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 30,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => "GET",
            //     CURLOPT_POSTFIELDS => "",
            //     CURLOPT_COOKIE => "JSESSIONID=22F8EC805F50F4B1FD0ADE2E26311490; JSESSIONID=5ED2A773BE4007132637A43C5303E2ED",
            // ]);

            // $response = curl_exec($curl);
            // $err = curl_error($curl);

            // curl_close($curl);

            // if ($err) {
            //     echo "cURL Error #:" . $err;
            // } else {
            //     echo $response;
            // }
    
        }
    }

    public function responseMegasoft(Request $request) {
        $control = session()->get('megasoft-control');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://paytest.megasoft.com.ve/payment/action/paymentgatewayuniversal-querystatus?control=". $request->control . "&version=3&tipo=". $request->tipo,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_COOKIE => "JSESSIONID=22F8EC805F50F4B1FD0ADE2E26311490; JSESSIONID=5ED2A773BE4007132637A43C5303E2ED",
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // echo $response;
            // Parsear la respuesta XML
            $respuesta = simplexml_load_string($response);

            // Mostrar la información del voucher
            echo '<pre>' . htmlentities($respuesta->voucher, ENT_XML1) . '</pre>';
        }
    }
}
