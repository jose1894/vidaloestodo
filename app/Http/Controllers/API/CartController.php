<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\AssignProductAttribute;
use App\Cart;
use App\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Product;
use App\ProductStock;
use App\Rates;
use App\Coupon;
use App\PlanUsers;
use App\GatewayCurrency;
use App\Gateway;
use Carbon\Carbon;
use App\Order;
use App\OrderDetail;
use App\User;
use App\UserShipping;
use Session;
use App\GeneralSetting;

if (!defined('ACTIVE')) define('ACTIVE', 1);
if (!defined('INACTIVE')) define('INACTIVE', 0);

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        //$product = $request->item;
        $product = Product::where('id', $request->id)->with('productIva', 'productcombo')->first();

       

        //$user_id = auth()->user()->id ?? null;
        $user_id = json_decode($request->user)->id ?? null;

        //Si se agrega un plan y no se esta loggeado lo mando al login
        if ($product->is_plan == 1 && $user_id == null) {
            //return response()->json(['error' => 'Debe iniciar sesión antes de agregar un plan al carrito.']);
            $message = 'Debe iniciar sesión antes de agregar un plan al carrito.';
        }

        $attributes     = AssignProductAttribute::where('product_id', $request->id)
            ->distinct('product_attribute_id')->with('productAttribute')
            ->get(['product_attribute_id']);

        $selected_attr = [];

        $s_id = $request->sessioncart; //session()->get('session_id');

        if ($s_id == null) {
            session()->put('session_id', uniqid());
            $s_id = session()->get('session_id');
        }

        $selected_attr = $request['attributes'] ?? null;

        if ($selected_attr != null) {
            sort($selected_attr);
            $selected_attr = (json_encode($selected_attr));
        }

        if ($user_id != null) {
            $cart = Cart::where('user_id', $user_id)
                ->where('product_id', $request->id)
                ->where('attributes', $selected_attr)
                ->with('product.planDetails')
                ->first();
            $cartCompleto = Cart::where('user_id', $user_id)
                ->with('product.planDetails')
                ->get();
        } else {
            $cart = Cart::where('session_id', $s_id)
                ->where('product_id', $request->id)
                ->where('attributes', $selected_attr)
                ->with('product.planDetails')
                ->first();
            $cartCompleto = Cart::where('session_id', $s_id)
                ->with('product.planDetails')->get();
        }

        if ($cartCompleto->count() > 0) {
            foreach ($cartCompleto as $item) {
                //check de si ya hay un plan prime en el carrito
                if ($item->product->is_plan == 1) {
                    //return response()->json(['error' => 'Finalice el proceso de compra del plan prime antes de seguir agregando productos al carrito.']);
                    $message = 'Finalice el proceso de compra del plan prime antes de seguir agregando productos al carrito.';
                }

                //si ya hay un producto (ejmplo: pera) y se agrega un plan prime
                if ($product->is_plan == 1) {
                    //return response()->json(['error' => 'No puede adquirir un plan prime mientras posea productos en su carrito.']);
                    $message = 'No puede adquirir un plan prime mientras posea productos en su carrito.';
                }
            }
        }

        //Check Stock Status
        if ($product->track_inventory) {
            $stock_qty = showAvailableStock($request->id, $selected_attr);
            if ($request->quantity > $stock_qty) {
                // return response()->json(['error' => 'Lo sentimos, la cantidad solicitada no está disponible en nuestro stock.']);
                $message = 'Lo sentimos, la cantidad solicitada no está disponible en nuestro stock.';
            }
        }

        if ($cart) {
            //check de si ya hay un plan prime en el carrito
            if ($cart->product->is_plan == 1) {
                //return response()->json(['error' => 'Finalice el proceso de compra del plan prime antes de seguir agregando productos al carrito.']);
                $message = 'Finalice el proceso de compra del plan prime antes de seguir agregando productos al carrito.';
            }

            if (isset($stock_qty) && $cart->quantity > $stock_qty) {
                //return response()->json(['error' => 'Lo sentimos, ya ha añadido la cantidad máxima de stock.']);
                $message = 'Lo sentimos, ya ha añadido la cantidad máxima de stock.';
            }

            $cart->quantity  = $request->quantity;
            $cart->save();

            //return response()->json(['success' => 'Modificada la Cantidad del Producto']);
            $message = 'Modificada la Cantidad del Producto';
        } else {

            $cart = new Cart();
            $cart->user_id    = $user_id;
            $cart->session_id = $s_id;
            $cart->attributes = json_decode($selected_attr);
            $cart->product_id = $request->id;
            $cart->quantity   = $request->quantity;
            $cart->save();
            $message = 'Añadido Al Carrito';
        }

        return response()->json([
            'item' => $product,
            'is_plan' => $product->is_plan,
            'user' => $user_id,
            's_id' => $s_id,
            'cart' => $cart,
            'cartCompleto' => $cartCompleto,
            'message' => $message,
            'getcart' => $this->getCart($s_id)
        ]);
    }

    public function getCart($s_id)
    {

        //  dd(Session::all());

        $subtotal = 0;
        $tasa = Rates::select('tasa_del_dia')->where('status', '1')->orderBy('id', 'desc')->first();
        $total = 0;
        $user_id    = auth()->user()->id ?? null;
        $is_prime = false;

        // return;

        if ($user_id != null) {
            $total_cart = Cart::where('user_id', $user_id)
                ->with([
                    'product.reviews',
                    'product.offers',
                    'product.categories',
                    'product.productPreviewImages',
                    'product.productVariantImages',
                    'product.stocks' => function ($q) {
                        $q->where('quantity', '>', 0);
                    },
                ])
                ->whereHas('product', function ($q) {
                    //return $q->whereHas('categories');//->whereHas('brand');
                })
                ->orderBy('id', 'desc')
                ->get();





            $hoy = Carbon::now()->format('Y-m-d');
            $prime = PlanUsers::where('user_id', $user_id)
                ->where('status', 1)
                ->whereDate('expiration_date', '>', $hoy)
                ->first();

            if ($prime) {
                $is_prime = true;
            }
        } else {
            //$s_id       = session()->get('session_id');
            $total_cart = Cart::where('session_id', $s_id)
                ->with(['product.planDetails', 'product.offer', 'product.productIva'])
                ->whereHas('product', function ($q) {
                    //return $q->whereHas('categories');//->whereHas('brand');
                })
                ->orderBy('id', 'desc')
                ->get();
        }

        //return;



        //COMPARAMOS LOS PRODUCTOS DEL INVENTARIO CON LA CANTIDAD EN EL CARRITO

        $compare = $this->compareQuantity($total_cart);

        //SI NO HAY SUFICIENTES
        if ($compare > 0) {
            if ($user_id != null) {
                $total_cart = Cart::where('user_id', $user_id)
                    ->with(['product', 'product.offer', 'product.productIva'])
                    ->whereHas('product', function ($q) {
                        return $q->whereHas('categories'); //->whereHas('brand');
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            } else {
                //$s_id       = session()->get('session_id');
                $total_cart = Cart::where('session_id', $s_id)
                    ->with(['product', 'product.offer', 'product.productIva'])
                    ->whereHas('product', function ($q) {
                        return $q->whereHas('categories'); //->whereHas('brand');
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        //$arrayProducts = [];




        if ($total_cart->count() > 100) {
            $latest = $total_cart->sortByDesc('id')->take(100);
        } else {
            $latest = $total_cart;
        }

        if ($total_cart->count() > 0) {
            foreach ($total_cart as $tc) {
                $amount = $tc->product->offer->activeOffer->amount ?? 0;
                $discount_type =  $tc->product->offer->activeOffer->discount_type ?? 0;
                if ($is_prime == true) {
                    $tc->is_prime = 1;
                    $base_price = $tc->product->precioPrimeIva > 0 ? $tc->product->precioPrimeIva : $tc->product->precioBaseIva;
                } else {
                    $tc->is_prime = 0;
                    $base_price = $tc->product->precioBaseIva ?? 0;
                }


                if ($tc->attributes != null) {
                    $s_price = priceAfterAttribute($tc->product, $tc->attributes);
                } else {
                    if (optional($tc->product)->offer) {
                        $s_price = $base_price - calculateDiscount($amount, $discount_type, $base_price);
                    } else {
                        $s_price = $base_price;
                    }
                }
                $subtotal += $s_price * $tc->quantity;
            }
            $total = ($subtotal * $tasa->tasa_del_dia);
        }

        $more           = $total_cart->count() - count($latest);
        $empty_message  = 'No Hay Productos en tu Carrito';
        $coupon         = null;

        if (session()->has('coupon')) {
            $coupon = session('coupon');
        }

        $arrayCart = [];
        $cur_sym = $this->generalSetting()->cur_sym;
        $rates = $this->rates()->tasa_del_dia;
        $counQtuantity = 0;
        foreach ($total_cart as $cart) {
            $cart['rates'] = $rates;
            $counQtuantity += $cart->quantity;
            if ($cart->product) {
                $cart->product['urlimage'] = getImage(imagePath()['product']['path'] . '/' . @$cart->product->main_image, imagePath()['product']['size']);
                $cart->product['cur_sym'] = $cur_sym;
                $cart->product['priceAfterAttribute'] = isset($cart->attributes) ? getAmount(priceAfterAttribute(json_decode($cart->product), $cart->attributes), 2) : getAmount(0, 2);
                $calculateDiscountPrime = isset($cart->product->offer) ? calculateDiscount(
                    $cart->product->offer->activeOffer->amount,
                    $cart->product->offer->activeOffer->discount_type,
                    $cart->product->precioPrimeIva ?? $cart->product->precioBaseIva
                ) : 0;
                $cart->product['calculateDiscountPrime'] = isset($cart->product->offer)
                    ?
                    getAmount($calculateDiscountPrime, 2)
                    : getAmount(0, 2);
                $pricePrimeDiscount = ($cart->product->precioPrimeIva ?? $cart->product->precioBaseIva) - $calculateDiscountPrime;
                $cart->product['pricePrimeDiscount'] = isset($cart->product->offer)  ? getAmount($pricePrimeDiscount, 2) : getAmount(0, 2);
                $cart->product['pricePrimeDiscountRates'] = isset($cart->product->offer)  ? getAmount($pricePrimeDiscount * $rates, 2) : getAmount(0, 2);

                $calculateDiscount = isset($cart->product->offer) ? calculateDiscount(
                    $cart->product->offer->activeOffer->amount,
                    $cart->product->offer->activeOffer->discount_type,
                    $cart->product->precioBaseIva
                ) : 0;
                $priceDiscount = $cart->product->precioBaseIva - $calculateDiscount;
                $cart->product['calculateDiscount'] = isset($cart->product->offer)
                    ?
                    getAmount($calculateDiscount, 2)
                    : getAmount(0, 2);
                $cart->product['priceDiscount'] = isset($cart->product->offer)  ? getAmount($priceDiscount, 2) : getAmount(0, 2);
                $cart->product['priceDiscountRates'] = isset($cart->product->offer)  ? getAmount($priceDiscount * $rates, 2) : getAmount(0, 2);

                //$s_price = $cart->product->precioBaseIva;
                $cart->product['precioBaseIvaDollar'] = getAmount($cart->product->precioBaseIva, 2);
                $cart->product['precioBaseIvaRates'] = getAmount($cart->product->precioBaseIva * $rates, 2);
                $precioPrimeIva = $cart->product->precioPrimeIva > 0 ? $cart->product->precioPrimeIva : $cart->product->precioBaseIva;
                $cart->product['precioPrimeIvaDollar'] = getAmount($precioPrimeIva, 2);
                $cart->product['precioPrimeIvaRates'] = getAmount($precioPrimeIva * $rates, 2);
                $arrayCart[] = $cart;
            }
        }

        return [
            'data' => $arrayCart,
            'counQtuantity' => $counQtuantity,
            'subtotal' => $subtotal,
            'empty_message' => $empty_message,
            'more' => $more,
            'coupon' => $coupon,
            'tasa' => $tasa,
            'total' => $total
        ];
    }
    private function generalSetting()
    {
        return GeneralSetting::first();
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

    public function compareQuantity($cart)
    {
        $num = 0;
        foreach ($cart as $item) {

            // $item->quantity = 33;
            $product = Product::findOrFail($item->product->id);
            $stock = ProductStock::where('product_id', $product->id)->first();
            $selected_attr = null;
            if ($product->track_inventory != null) {
                $stock_qty = showAvailableStock($item->product->id, $selected_attr);
                if ($stock_qty == 0) {
                    $stock_qty = 1;
                }
                if ($item->quantity > $stock_qty) {
                    $item->quantity = $stock_qty;
                    $num++;
                    $item->save();
                }
                if ($item->quantity == 0) {
                    $item->delete();
                }
                // dd($item->quantity);
            }
        }
        return $num;
    }

    public function checkout()
    {
        // $user = Auth::user();
        $user_id    = auth()->user()->id ?? null;
        $is_prime = false;

        if ($user_id) {

            $s_id = session()->get('session_id');

            $cart = Cart::where('session_id', $s_id)->get();
            foreach ($cart as $key) {
                $key->user_id = $user_id;
                $key->save();
            }


            $data = Cart::where('user_id', $user_id)->with([
                'product.offer',
                'attributes'
            ])->get();

            $hoy = Carbon::now()->format('Y-m-d');
            $prime = PlanUsers::where('user_id', $user_id)
                ->where('status', 1)
                ->whereDate('expiration_date', '>', $hoy)
                ->first();

            if ($prime) {
                $is_prime = true;
            }
        } else {
            $data = Cart::where('session_id', session('session_id'))->with([
                'product.offer',
                'attributes'
            ])->get();
        }

        if ($data->count() == 0) {
            $notify[] = ['success', 'Sin productos en el carrito'];
            // return redirect()->route('home')->withNotify($notify);
        }

        $subtotal = 0;
        $base_imponible = 0;
        $is_plan = false;
        $iva_total = 0;


        foreach ($data as $item) {
            $product = Product::where('id', $item->product_id)->with('productIva')->first();

            if ($product->track_inventory) {
                $stock_qty = showAvailableStock($item->product_id, $item->attributes);
                if ($item->quantity > $stock_qty) {
                    $notify[] = ['success', 'Lo sentimos, el stock producto ' . $product->name . ' ya no está disponible'];
                    return redirect()->route('home')->withNotify($notify);
                    // return response()->json(['error' => 'Lo sentimos, el stock producto '.$product->name.' ya no está disponible']);
                }
            }

            //si es un plan
            if ($product->is_plan == 1) $is_plan = true;

            // if($is_prime == true){
            //     $item->is_prime = 1;
            //     $subtotal += ($product->prime_price??$product->base_price * $item->quantity);
            // }else{
            //     $item->is_prime = 0;
            //     $subtotal += ($product->base_price * $item->quantity);
            // }

            $amount = $item->product->offer->activeOffer->amount ?? 0;
            $discount_type =  $item->product->offer->activeOffer->discount_type ?? 0;
            if ($is_prime == true) {
                $item->is_prime = 1;
                $base_price = $item->product->prime_price > 0 && $item->product->prime_price != null ? $item->product->prime_price : $item->product->base_price;

                //si tiene iva o no
                if ($item->product->iva == ACTIVE) {
                    $base_imponible += $item->product->prime_price > 0 && $item->product->prime_price != null ? $item->product->prime_price : $item->product->base_price;
                    if (!is_null($product->productIva)) {
                        $iva_total += ((($item->product->prime_price > 0 && $item->product->prime_price != null ? $item->product->prime_price : $item->product->base_price) * $item->quantity) * ($product->productIva->percentage / 100));
                    }
                }
            } else {
                $item->is_prime = 0;
                $base_price = $item->product->base_price ?? 0;

                //si tiene iva o no
                if ($item->product->iva == ACTIVE) {
                    $base_imponible += $item->product->base_price;
                    if (!is_null($product->productIva)) {
                        $iva_total += (($item->product->base_price * $item->quantity) * ($product->productIva->percentage / 100));
                    }
                }
            }


            if ($item->attributes != null) {
                $s_price = priceAfterAttribute($item->product, $item->attributes);
            } else {
                if (optional($item->product)->offer) {
                    $s_price = $base_price - calculateDiscount($amount, $discount_type, $base_price);
                } else {
                    $s_price = $base_price;
                }
            }
            $subtotal += $s_price * $item->quantity;
        }

        //dd($data);



        $base_imponible = str_replace(',', '.', $base_imponible);
        $iva = $iva_total; //calculateIva($base_imponible);
        $iva = str_replace(',', '.', $iva);
        $subtotal = str_replace(',', '.', $subtotal);

        //si es un plan creo una orden de una vez
        if ($is_plan == true) {
            return redirect()->route('user.checkout-to-payment-plan', (['subtotal' => $subtotal]));
        }

        /*return response()->json([
            'user_id' => $user_id,
            'data' => $data,
            'notify' => $notify
        ]);*/
        $coupons = \DB::table('carts')->select('coupons.*')
            ->join('products', 'products.id', '=', 'carts.product_id')
            ->join('coupons_products', 'coupons_products.product_id', '=', 'products.id')
            ->join('coupons', 'coupons.id', '=', 'coupons_products.coupon_id')
            ->where('coupons.end_date', '>=', date('Y-m-d'))
            ->groupBy('coupons.id')
            ->get();

        if ($subtotal == 0 || $subtotal == null) {
            $notify[] = ['error', 'Debe haber por lo menos una cantidad en los productos a comprar. '];
            return redirect()->route('home')->withNotify($notify);
        }

        $shipping_methods_delivery = ShippingMethod::where('status', 1)->where('shipping_type', 1)->where('is_plan', 0)->get();
        $shipping_methods_pickup = ShippingMethod::where('status', 1)->where('shipping_type', 2)->where('is_plan', 0)->get();
        $states = \DB::table('states')->where('country_id', 237)->get();
        $tasa = Rates::select('tasa_del_dia')->where('status', '1')->where('type', 'Bolívares')->orderBy('id', 'desc')->first() ?? 0;
        $tasa = number_format($tasa->tasa_del_dia, 2, '.', ' ');
        // dd($tasa);
        $iva = 0;
        $iva_total = 0;
        $base_imponible = 0;
        $excento = 0;


        foreach ($data as $cart) {
            $product = Product::where('id', $cart->product_id)->with('productIva')->first();

            //si es prime
            if ($is_prime == true) {
                $cart->is_prime = 1;
            } else {
                $cart->is_prime = 0;
            }

            if ($is_prime == true) {
                //si tiene iva o no
                if ($cart->product->iva == 1) {
                    $base_imponible += ($cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price);
                    if (!is_null($product->productIva)) {
                        $iva_total += ((($cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price) * $cart->quantity) * ($product->productIva->percentage / 100));
                    }
                } else {
                    $excento += ($cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price);
                }
            } else {
                //si tiene iva o no
                if ($cart->product->iva == 1) {
                    $base_imponible += $cart->product->base_price;
                    if (!is_null($product->productIva)) {
                        $iva_total += (($cart->product->base_price * $cart->quantity) * ($product->productIva->percentage / 100));
                    }
                } else {
                    $excento += $cart->product->base_price;
                }
            }
        }

        $base_imponible = str_replace(',', '.', $base_imponible);
        $iva = $iva_total; //calculateIva($base_imponible);
        $iva = str_replace(',', '.', $iva);
        $excento = str_replace(',', '.', $excento);

        // dd($tasa);
        //metodos de pago
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', 1);
        })->with('method')->orderby('method_code')->get();

        $gateways = Gateway::where('status', '1')
            ->orderBy('status', 'DESC')
            ->orderBy('name', 'ASC')
            ->with('currencies')->get();

        // dd($gateways);

        //fechas
        $fechas = [];
        $fechas[0]['name'] = 'Hoy';
        $fechas[0]['date'] = Carbon::now()->format('Y-m-d');
        $fechas[0]['fecha'] = Carbon::now()->toFormattedDateString();


        //return $fechas[1]['name'] = $this->getNameDaySpanish(Carbon::now()->addDay()->formatLocalized('%A'));     
        $fechas[1]['name'] = $this->getNameDaySpanish(Carbon::now()->addDay()->format('l'));
        $fechas[1]['date'] = Carbon::now()->addDay()->format('Y-m-d');
        $fechas[1]['fecha'] = Carbon::now()->addDay()->toFormattedDateString();

        //$fechas[2]['name'] = $this->getNameDaySpanish(Carbon::now()->addDays(2)->formatLocalized('%A'));
        $fechas[2]['name'] = $this->getNameDaySpanish(Carbon::now()->addDay(2)->format('l'));
        $fechas[2]['date'] = Carbon::now()->addDays(2)->format('Y-m-d');
        $fechas[2]['fecha'] = Carbon::now()->addDays(2)->toFormattedDateString();

        $page_title = 'Checkout';
        $order = Order::where('user_id', $user_id)->where('shipping_address', '<>', null)->orderBy('id', 'desc')->first();
        //$user_id =  ?? null;

        $usershipping = UserShipping::where('user_id', auth()->user()->id)->get();


        return response()->json([
            'user_id' => $user_id,
            'usershipping' => json_decode($usershipping),
            'shipping_methods_delivery' => $shipping_methods_delivery,
            'shipping_methods_pickup' => $shipping_methods_pickup,
            'data' => $data,
            'subtotal' => number_format($subtotal,2) ,
            'subtotalBs' => number_format($subtotal * $tasa,2) ,
            'coupons' => $coupons,
            'gatewayCurrency' => $gatewayCurrency,
            'states' => $states,
            'tasa' => $tasa,
            'iva' => $iva,
            'fechas' => $fechas,
            'gateways' => $gateways
        ]);
    }

    protected function getNameDaySpanish($name)
    {
        $name = strtolower($name);
        $spanish_name = '';
        switch ($name) {
            case 'monday':
            case 'lunes':
                $spanish_name = 'Lunes';
                break;
            case 'tuesday':
            case 'martes':
                $spanish_name = 'Martes';
                break;
            case 'wednesday':
            case 'miércoles':
                $spanish_name = 'Miércoles';
                break;
            case 'thursday':
            case 'jueves':
                $spanish_name = 'Jueves';
                break;
            case 'friday':
            case 'viernes':
                $spanish_name = 'Viernes';
                break;
            case 'saturday':
            case 'sábado':
                $spanish_name = 'Sábado';
                break;
            case 'sunday':
            case 'domingo':
                $spanish_name = 'Domingo';
                break;
        }
        return $spanish_name;
    }

    public function search_cities(Request $request)
    {

        $cities = \DB::table('cities')->where('state_id', $request->id)->get();

        return response()->json($cities);
    }

    public function post_shipping_user(Request $request)
    {
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
            'type'   => $request->type,
            'document'   => $request->document,
        ];

        $search = 0;
        $user_id = auth()->user()->id ?? null;
        $id = $request->id;
        $userShipping = UserShipping::find($id);
        // dd($userShipping);

        if ($userShipping) {
            $userShipping->shipping_address = json_encode($shipping_address);
            $userShipping->update();
        } else {
            $search = UserShipping::where('user_id', $user_id)->where('shipping_address', json_encode($shipping_address))->count();
            if ($search == 0) {
                $UserShipping = new UserShipping;
                $UserShipping->user_id = $user_id;
                $UserShipping->shipping_address = json_encode($shipping_address);
                $UserShipping->save();
            }
        }

        $data = UserShipping::where('user_id', $user_id)->get();
        return response()->json([
            'shipping_address' => $shipping_address,
            'user_id' => $user_id,
            'search' => $search ,
            'usershipping' => $data
        ]);
    }

    public function removeCartItem($id)
    {

        // if (session()->has('coupon')) {
        //     return response()->json(['error' => 'Ha aplicado un cupón en su carrito. Si desea eliminar algún artículo de su carrito, primero elimine el cupón.']);
        // }


        $cart_item = Cart::findorFail($id);
        $cart_item->delete();
        return response()->json(['success' => 'Producto eliminado correctamente']);
    }

    public function removeCartAll()
    {
        if (session()->has('coupon')) {
            return response()->json(['error' => 'Ha aplicado un cupón en su carrito. Si desea eliminar algún artículo de su carrito, primero elimine el cupón.']);
        }

        $user_id = auth()->user()->id ?? null;

        $s_id = session()->get('session_id');
        if ($s_id == null) {
            session()->put('session_id', uniqid());
            $s_id = session()->get('session_id');
        }

        if ($user_id != null) {
            $cart = Cart::where('user_id', $user_id)->orWhere('session_id', $s_id)->get();
        } else {
            $cart = Cart::where('session_id', $s_id)->get();
        }

        if ($cart) {
            foreach ($cart as $item) {
                $item->delete();
            }
            return response()->json([
                'success' => 'Productos eliminados correctamente',
                'user_id' => $user_id,
                's_id' => $s_id,
                'cart' => $cart
            ]);
        } else {
            return response()->json(['warning' => 'No se puedo eliminar.']);
        }
    }
}
