<?php

namespace App\Http\Controllers;

use App\AppliedCoupon;
use App\AssignProductAttribute;
use App\User;
use App\Admin;
use App\Order;
use App\Cart;
use App\Coupon;
use App\Product;
use App\Deposit;
use App\GeneralSetting;
use App\OrderDetail;
use App\ProductStock;
use App\ShippingMethod;
use App\StockLog;
use App\PlanUsers;
use App\Rates;
use App\GatewayCurrency;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\UserShipping;

if (!defined('ACTIVE')) define('ACTIVE', 1);
if (!defined('INACTIVE')) define('INACTIVE', 0);

class OrderController extends Controller
{
    public function orders($type)
    {   

        if (ucfirst($type) == 'All') {
            $title = 'Todas las';
        }else{
            $title = ucfirst($type);
        }


        $page_title = $title.' Órdenes';
        $empty_message = 'Sin Órdenes';

        switch ($type) {
            case "incomplete-payment"   : $title = "Incompletas"; $p_status = 0; break;
            case "processing"           : $title = "En Proceso"; $status   = [1] ; break;
            case "dispatched"           : $title = "Enviados"; $status   = [2] ; break;
            case "completed"            : $title = "Finalizados"; $status   = [3]; break;
            case "canceled"             : $title = "Cancelados"; $status   = [4]; break;
            case "pending"              : $title = "Pendiente"; $status   = [0]; break;
            case "all"                  : $title = "Todos"; $orders   = Order::where('user_id', auth()->user()->id)->where('payment_status','!=' ,0)->latest()->paginate(getPaginate()); break;
            default                     : abort(403, 'Acción No Autorizada.');
        }

        if(isset($p_status)){
            $orders = Order::where('user_id', auth()->user()->id)->where('payment_status', 0)->latest()->paginate(getPaginate());
        }
        if(isset($status)){
            $orders = Order::where('user_id', auth()->user()->id)->whereIn('status', $status)->where('payment_status', 1)->paginate(getPaginate());
        }
        $type = $title;

        return view(activeTemplate() . 'user.orders.index', compact('page_title', 'orders', 'empty_message', 'type'));
    }

    public function orderDetails($order_number)
    {
        $page_title = 'Detalles de la Órden';
        $order = Order::where('order_number', $order_number)->where('user_id', auth()->user()->id)->with('deposit.gateway', 'orderDetail.product', 'appliedCoupon', 'shipping')->first();
        
        $discountPrime = 0;
        //si es usuario prime, calculamos el descuento de productos prime
        if(count($order->user->plan_users) > 0){ 
            foreach($order->user->plan_users as $plan){
                if($plan->status == 1){ //activo
                    $sum_base = 0;
                    $sum_prime = 0;
                    foreach($order->orderDetail as $od){
                        $qty = 1;
                        while($qty <= $od->quantity){
                            $sum_base += $od->base_price != $od->prime_price ? $od->base_price : $od->product->base_price;
                            $sum_prime += $od->prime_price > 0 ? $od->prime_price : $od->product->base_price;
                            $qty++;
                        }              
                    }
                    $discountPrime = ($sum_base - $sum_prime);
                }
            }
            
        }

        return view(activeTemplate() . 'user.orders.details', compact('order','page_title','discountPrime'));
    }

    public function confirmOrderPlan(Request $request){
        
        $user = User::find(auth()->user()->id);

        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id??null)
        ->with(
            [
                'product.offer', 
                'product.categories'
            ]
        )->get();

        // foreach ($carts_data as $cd) {
        //     $pid    = $cd->product_id;
        //     $attr   = $cd->attributes;
        //     $attr   = $cd->attributes?json_encode($cd->attributes):null;
        //     if($cd->product->track_inventory){
        //         $stock  = ProductStock::where('product_id', $pid)->where('attributes', $attr)->first();
        //         if($stock){
        //             $stock->quantity   -= $cd->quantity;
        //             $stock->save();
        //             $log = new StockLog();
        //             $log->stock_id  = $stock->id;
        //             $log->quantity  = $cd->quantity;
        //             $log->type      = 3; //comprometida (cuando el admin confirme la orden se marca como 2)
        //             $log->save();
        //         }
        //     }
        // }
    
        $shipping_address  = $user->address;
        $shipping_data  = ShippingMethod::where('is_plan', 1)->first();

        $order = new Order;
        $order->order_number        = getTrx();
        $order->user_id             = auth()->user()->id;
        $order->shipping_address    = json_encode($shipping_address);
        $order->shipping_method_id  = $shipping_data->id;
        $order->shipping_charge     = $shipping_data->charge;
        $order->order_type          = 1;
        $order->payment_status      = $payment_status??0;
        $order->propina = 0;
        $order->coupon_amount = 0;
        $order->total_amount = $request->subtotal;
        $order->save();
        $details = [];

        foreach($carts_data as $cart){
            $od = new OrderDetail();
            $od->order_id       = $order->id;
            $od->product_id     = $cart->product_id;
            $od->quantity       = $cart->quantity;
            //si es prime
            if($cart->is_prime == 1){
                $od->base_price     = $cart->product->prime_price??$cart->product->base_price;
            }
            else{
                $od->base_price     = $cart->product->base_price;
            }
            $od->prime_price = $cart->product->prime_price??0;

            $amount = $cart->product->offer->activeOffer->amount??0;
            $discount_type =  $cart->product->offer->activeOffer->discount_type??0;
            $offer_amount = calculateDiscount($amount, $discount_type, $od->base_price);
            if($cart->is_prime == 1){
                $od->base_price  = $cart->product->prime_price ?? $cart->product->base_price;
            }
            else{
                $od->base_price  = $cart->product->base_price??0;
            }
                

            if($cart->attributes != null){
                $attr_item                   = productAttributesDetails($cart->attributes);
                $attr_item['offer_amount'] = $offer_amount;
                //si es prime
                if($cart->is_prime == 1){
                    $sub_total                   = ((($cart->product->prime_price??$cart->product->base_price) + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                }
                else{
                    $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                }
                
                $od->total_price             = $sub_total;
                unset($attr_item['extra_price']);
                $od->details                 = json_encode($attr_item);
            }else{
                $details['variants']        = null;
                $details['offer_amount']    = $offer_amount;
                //si es prime
                if($cart->is_prime == 1){
                    $sub_total                  = ( ($cart->product->prime_price??$cart->product->base_price) - $offer_amount) * $cart->quantity;
                }
                else{
                    $sub_total                  = ($cart->product->base_price  - $offer_amount) * $cart->quantity;
                }
                
                $od->total_price            = $sub_total;
                $od->details                = json_encode($details);
            }

            $od->save();
        }

        session()->put('order_number', $order->order_number);

        return redirect()->route('user.deposit');

    }

    public function confirmOrderOLD(Request $request, $type)
    {
        //return $request->all();
        $general = GeneralSetting::first();
        $notify[] = [];
        $payment = 1;

        if(isset($payment)){
            if($payment !=1){
                abort(403, 'Acción No Autorizada.');
            }
        }else{
            $payment_status = 2;
            if(!$general->cod){
                $notify[]=['error','El pago en Efectivo no está disponible ahora'];
                return back()->withNotify($notify);
            }
            
            if($request->cash_on_delivery != 1){
                abort(403, 'Acción No Autorizada.');
            }
        }

        /*
        type 1 (order for user)
        type 2 (order as Gift)
        */
        $request->validate([
            'method_entrega'   => 'required|integer',
            'shippingUser'   => 'nullable|integer',
            //'address'           => 'required|max:125',
            //'city'              => 'required|max:125',
            //'state'             => 'required|max:125',
            //'zip'               => 'required|max:125',
            //'country'           => 'required|max:125',
            'fact_names'           => 'nullable|max:125',
            'fact_type_dni'           => 'nullable|string',
            'fact_dni'           => 'nullable|max:15',
            'fact_mobile'           => 'nullable|max:15',
            'propina_form'       => 'nullable|numeric',
            'order_time'       => 'nullable',
            'order_time_horario' => 'nullable|numeric',
        ],[
            'method_entrega.required' => 'Seleccione un Método de Entrega',
        ]);

        //return $request->all();
        $user_id = auth()->user()->id??null;

        $user=User::find($user_id);

        if (!is_null($user)) {


            $shipping = [
                'address' => $request->fact_address,
                'state' => $user->address->state??'',
                'zip' => $user->address->zip??'',
                'country' => $user->address->country??'',
                'city' => $user->address->city??'',
            ];
            // dd($shipping)

            $user->firstname = $request->fact_names;
            $user->lastname = $request->fact_lastname;
            $user->type_dni = $request->fact_type_dni;
            $user->dni  = $request->fact_dni;
            $user->address  = $shipping;
            $user->direction = $request->fact_address;
            $user->mobile = $request->fact_mobile;
            $user->save();
        }

        $invoice_information = [
            'names' => $request->fact_names.' '.$request->fact_lastname,
            'type_dni' => $request->fact_type_dni,
            'dni'  => $request->fact_dni,
            'address'    => $request->fact_address,
            'mobile' => $request->fact_mobile,
        ];

        $is_prime = false;
        if($user_id){
            //checkeo si es prime
            $hoy = Carbon::now()->format('Y-m-d');
            $prime = PlanUsers::where('user_id', $user_id)
                ->where('status', 1)
                ->whereDate('expiration_date','>',$hoy)
                ->first();
            
            if($prime){
                $is_prime = true;
            }
        }

        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id??null)
        ->with(
            [
                'product.offer', 
                'product.categories'
            ])->get();


        $coupon_amount  = 0;
        $coupon_code    = null;
        $cart_total     = 0;
        $product_categories = [];
        $base_imponible = 0;
        $excento = 0;
        $iva_total = 0;

        foreach ($carts_data as $cart) {
            $product = Product::where('id',$cart->product_id)->with('productIva')->first();

            //si es prime
            if($is_prime == true){
                $cart->is_prime = 1;
            }
            else{
                $cart->is_prime = 0;
            }

            if($cart->product->is_plan == 0){
                $product_categories[] = $cart->product->categories->pluck('id')->toArray();
            }

            $amount = $cart->product->offer->activeOffer->amount??0;
            $discount_type =  $cart->product->offer->activeOffer->discount_type??0;
            if($is_prime == true){
                $cart->is_prime = 1;
                $base_price = $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;

                //si tiene iva o no
                if($cart->product->iva == ACTIVE){
                    $base_imponible += $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;
                    if(!is_null($product->productIva)){
                        $iva_total += ((($cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price) * $cart->quantity) * ($product->productIva->percentage / 100));
                    }
                }
                else{
                    $excento += $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;
                }
            }
            else{
                $cart->is_prime = 0;
                $base_price = $cart->product->base_price??0;

                //si tiene iva o no
                if($cart->product->iva == ACTIVE){
                    $base_imponible += $cart->product->base_price;
                    if(!is_null($product->productIva)){
                        $iva_total += (($cart->product->base_price * $cart->quantity) * ($product->productIva->percentage / 100));
                    }
                }
                else{
                    $excento += $cart->product->base_price;
                }
            }
                

            if($cart->attributes != null){
                $s_price = priceAfterAttribute($cart->product, $cart->attributes);
            }else{
                $details['variants']        = null;
                $details['offer_amount']    = calculateDiscount($amount, $discount_type, $base_price);
                if(optional($cart->product)->offer){
                    $s_price = $base_price - calculateDiscount($amount, $discount_type, $base_price);
                }else{
                    $s_price = $base_price;
                }
            }
            $cart_total += $s_price * $cart->quantity;


        }
        $base_imponible = str_replace(',', '.', $base_imponible);
        $iva = $iva_total; //calculateIva($base_imponible);
        $iva = str_replace(',', '.', $iva);
        $excento = str_replace(',', '.', $excento);
        $cart_total = str_replace(',', '.', $cart_total);

        if(session('coupon')){
            $coupon = Coupon::where('coupon_code', session('coupon')['code'])->with('categories')->first();

            // Check Minimum Subtotal
            if($cart_total < $coupon->minimum_spend){
                return response()->json(['error' => "Lo sentimos, tiene que pedir una cantidad mínima de $coupon->minimum_spend $general->cur_text"]);
            }

            // Check Maximum Subtotal
            if($coupon->maximum_spend !=null && $cart_total > $coupon->maximum_spend){
                return response()->json(['error' => "Lo sentimos, tienes que pedir la cantidad máxima de $coupon->maximum_spend $general->cur_text"]);
            }

            //Check Limit Per Coupon
            if($coupon->appliedCoupons->count() >= $coupon->usage_limit_per_coupon){
                return response()->json(['error' => "Lo sentimos, su cupón ha excedido el límite máximo de uso"]);
            }

            //Check Limit Per User
            if($coupon->appliedCoupons->where('user_id', auth()->id())->count() >= $coupon->usage_limit_per_user){
                return response()->json(['error' => "Lo sentimos, ya alcanzó el límite de uso máximo para este cupón"]);
            }

            if($cart->product->is_plan == 0){
                $product_categories = array_unique(array_flatten($product_categories));
                if($coupon){
                    $coupon_categories = $coupon->categories->pluck('id')->toArray();
                    $coupon_products = $coupon->products->pluck('id')->toArray();

                    $cart_products = $carts_data->pluck('product_id')->unique()->toArray();

                    if(empty(array_intersect($coupon_products, $cart_products))){
                        if(empty(array_intersect($product_categories, $coupon_categories))){
                            $notify[]=['error', 'El cupón no está disponible en varios productos del carito.'];
                            return redirect()->back()->withNotify($notify);
                        }
                    }

                    if($coupon->discount_type == 1){
                        $coupon_amount = $coupon->coupon_amount;
                    }else{
                        $coupon_amount = $cart_total * $coupon->coupon_amount / 100;
                    }
                    $coupon_code    = $coupon->coupon_code;
                }
            }
            
        }

        // foreach ($carts_data as $cd) {
        //     $pid    = $cd->product_id;
        //     $attr   = $cd->attributes;
        //     $attr   = $cd->attributes?json_encode($cd->attributes):null;
        //     if($cd->product->track_inventory){
        //         $stock  = ProductStock::where('product_id', $pid)->where('attributes', $attr)->first();
        //         if($stock){
        //             $stock->quantity   -= $cd->quantity;
        //             $stock->save();
        //             $log = new StockLog();
        //             $log->stock_id  = $stock->id;
        //             $log->quantity  = $cd->quantity;
        //             $log->type      = 3; //comprometida (cuando el admin confirme la orden se marca como 2)
        //             $log->save();
        //         }
        //     }
        // }

        //OBTENGO LA DIRECCION DE ENVÍO SELECCIONADA
        if($request->method_entrega == 1){
            $shipping_address = UserShipping::where('id',$request->shippingUser)->where('user_id', $user_id)->first();
        }
        else if($request->method_entrega == 2){
            $shipping_address = Admin::where('id',1)->first();
            $shipping_address = [
                'names' => $shipping_address->name,
                'mobile'  => $shipping_address->mobile,
                'address'    => $shipping_address->address,
            ];
        }
        

        //OBTENGO LOS DATOS DEL TIPO DE ENVÍO
        $shipping_data  = ShippingMethod::find($request->checkbox_shipping);

        $order = new Order;
        $order->order_number        = getTrx();
        $order->user_id             = auth()->user()->id;
        $order->shipping_address    = json_encode($shipping_address);
        $order->shipping_method_id  = $request->checkbox_shipping;
        $order->shipping_charge     = $shipping_data->charge;
        $order->invoice_information = json_encode($invoice_information);
        $order->order_type          = $type;
        $order->payment_status      = $payment_status??0;
        isset($request->propina_form) ? $order->propina = $request->propina_form : $order->propina = 0;
        isset($request->coupon_amount) ? $order->coupon_amount = $request->coupon_amount : $order->coupon_amount = 0;
        $order->order_time          = isset($request->order_time) ? Carbon::parse($request->order_time)->format('d-m-Y') : null;
        $order->order_time_horario  = isset($request->order_time_horario) ? $request->order_time_horario : null;
        // $order->coupon_code = $coupon->coupon_code;
        $order->save();
        $details = [];

        foreach($carts_data as $cart){
            $od = new OrderDetail();
            $od->order_id       = $order->id;
            $od->product_id     = $cart->product_id;
            $od->quantity       = $cart->quantity;
            //si es prime
            if($cart->is_prime == 1){
                $od->base_price     = $cart->product->prime_price??$cart->product->base_price;
            }
            else{
                $od->base_price     = $cart->product->base_price;
            }
            $od->prime_price = $cart->product->prime_price??0;

            // if($cart->product->offer){
            //     $offer_amount       = calculateDiscount($cart->product->offer->activeOffer->amount??0, $cart->product->offer->activeOffer->discount_type??1, $cart->product->base_price);
            // }else $offer_amount = 0;


            // if($cart->attributes != null){
            //     $attr_item                   = productAttributesDetails($cart->attributes);
            //     $attr_item['offer_amount'] = $offer_amount;
            //     //si es prime
            //     if($cart->is_prime == 1){
            //         $sub_total                   = ((($cart->product->prime_price??$cart->product->base_price) + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
            //     }
            //     else{
            //         $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
            //     }
                
            //     $od->total_price             = $sub_total;
            //     unset($attr_item['extra_price']);
            //     $od->details                 = json_encode($attr_item);
            // }else{
            //     $details['variants']        = null;
            //     $details['offer_amount']    = $offer_amount;
            //     //si es prime
            //     if($cart->is_prime == 1){
            //         $sub_total                  = ( ($cart->product->prime_price??$cart->product->base_price) - $offer_amount) * $cart->quantity;
            //     }
            //     else{
            //         $sub_total                  = ($cart->product->base_price  - $offer_amount) * $cart->quantity;
            //     }
                
            //     $od->total_price            = $sub_total;
            //     $od->details                = json_encode($details);
            // }
            // $od->save();


            $amount = $cart->product->offer->activeOffer->amount??0;
            $discount_type =  $cart->product->offer->activeOffer->discount_type??0;
            $offer_amount = calculateDiscount($amount, $discount_type, $base_price);
            if($cart->is_prime == 1){
                $od->base_price  = $cart->product->prime_price ?? $cart->product->base_price;
            }
            else{
                $od->base_price  = $cart->product->base_price??0;
            }
                

            if($cart->attributes != null){
                $attr_item                   = productAttributesDetails($cart->attributes);
                $attr_item['offer_amount'] = $offer_amount;
                //si es prime
                if($cart->is_prime == 1){
                    $sub_total                   = ((($cart->product->prime_price??$cart->product->base_price) + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                }
                else{
                    $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                }
                
                $od->total_price             = $sub_total;
                unset($attr_item['extra_price']);
                $od->details                 = json_encode($attr_item);
            }else{
                $details['variants']        = null;
                $details['offer_amount']    = $offer_amount;
                //si es prime
                if($cart->is_prime == 1){
                    $sub_total                  = ( ($cart->product->prime_price??$cart->product->base_price) - $offer_amount) * $cart->quantity;
                }
                else{
                    $sub_total                  = ($cart->product->base_price  - $offer_amount) * $cart->quantity;
                }
                
                $od->total_price            = $sub_total;
                $od->details                = json_encode($details);
            }
            $od->save();


        }

        $order->base_imponible = getAmount($base_imponible);
        $order->excento = getAmount($excento);
        $order->iva = getAmount($iva);
        $order->total_amount =  getAmount(((($cart_total - $coupon_amount) + $shipping_data->charge ) + $order->propina - $order->coupon_amount ) + $iva);
        $order->save();
        session()->put('order_number', $order->order_number);
        //session()->put('order', $order);

        //Envío email al superadmin de q recibió un pedido, y al cliente
        $message_to_admin = 'Acaba de recibir una orden por parte del cliente ' . $user->firstname .' '. $user->lastname . 
        ' por un monto de: '.number_format($order->total_amount,2) .'$. Este atento para confirmar el pago.';
        $admin = Admin::where('role_id',3)->first();
        $message_to_client = '¡Felicidades! Acaba de realizar un pedido en alfogolarexpress por un monto de: ' .number_format($order->total_amount,2) .
        '$. Estamos a su servicio.' ;
        try {
            send_general_email($admin->email, '¡Alerta de Orden!', $message_to_admin, 'Admin');
            send_general_email($user->email, '¡Pedido realizado!', $message_to_client, $user->firstname .' '. $user->lastname);
        } catch (\Exception $exp) {
            $notify[] = ['error', 'Error de email'];
            return back()->withNotify($notify);
        }

        if($coupon_code != null){
            $applied_coupon = new AppliedCoupon();
            $applied_coupon->user_id    = auth()->id();
            $applied_coupon->coupon_id  = $coupon->id;
            $applied_coupon->order_id   = $order->id;
            $applied_coupon->amount     = $coupon_amount;
            $applied_coupon->save();
        }

        //Remove coupon from session
        if(session('coupon')){
            session()->forget('coupon');
        }

        if(isset($payment)){
            if ($request->ajax()) {
                return response()->json([
                    'result' => true,
                    'order_number' => $order->order_number
                ]);
            }
            else {
                return redirect()->route('user.deposit');
            }            
            
            // $order = Order::where('order_number', session('order_number'))->first();
            // $rate = Rates::where('status','1')->orderBy('id', 'ASC')->first();

            // $totalbs = 0;
            // $totalbs = ($order->total_amount * $rate->tasa_del_dia);
            // $order->totalbs = $totalbs;

            // if($order){
            //     if($order->payment_status ==1)
            //     return redirect('/');
            // }

            // $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            //     $gate->where('status', 1);
            // })->with('method')->orderby('method_code')->get();

            // if ($order->total_amount <= 0) {
            //     $notify[] = ['error', 'El Monto Total no puede ser menor a 0'];
            //     return back()->withNotify($notify);
            // }
            // // dd($order);
            // $page_title = 'Paso 4: Deposito';
            // $pagos = true;

            // return view(activeTemplate() . 'deposit', compact('gatewayCurrency', 'page_title', 'order','pagos'));
        }else{

            $depo['user_id']            = auth()->id();
            $depo['method_code']        = 0;
            $depo['order_id']           = $order->id;
            $depo['method_currency']    = $general->cur_text;
            $depo['amount']             = $order->total_amount;
            $depo['charge']             = 0;
            $depo['rate']               = 0;
            $depo['final_amo']          = getAmount($order->total_amount);
            $depo['btc_amo']            = 0;
            $depo['btc_wallet']         = "";
            $depo['trx']                = getTrx();
            $depo['try']                = 0;
            $depo['status']             = 2;
            $deposit                    = Deposit::where('order_id', $order->id)->first();

            if($deposit){
                $deposit->update($depo);
                $data = $deposit;
            }else{
                $data = Deposit::create($depo);
            }

            $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id??null)->get();

            foreach($carts_data as $cart){
                $cart->delete();
            }

            $notify[] = ['success', 'Su pedido se ha enviado correctamente, espere un correo electrónico de confirmación.'];
            return redirect()->route('user.home')->withNotify($notify);
        }

    }

    public function confirmOrder(Request $request){
        //return $request->all();
        $general = GeneralSetting::first();
        $notify[] = [];
        $payment = 1;
        $is_prime = false;

        if(isset($payment)){
            if($payment !=1){
                abort(403, 'Acción No Autorizada.');
            }
        }else{
            $payment_status = 2;
            if(!$general->cod){
                $notify[]=['error','El pago en Efectivo no está disponible ahora'];
                return back()->withNotify($notify);
            }
            
            if($request->cash_on_delivery != 1){
                abort(403, 'Acción No Autorizada.');
            }
        }

        /*
        type 1 (order for user)
        type 2 (order as Gift)
        */
        $request->validate([
            'method_entrega'   => 'required|integer',
            'shippingUser'   => 'nullable|integer',
            //'address'           => 'required|max:125',
            //'city'              => 'required|max:125',
            //'state'             => 'required|max:125',
            //'zip'               => 'required|max:125',
            //'country'           => 'required|max:125',
            'fact_names'           => 'nullable|max:125',
            'fact_type_dni'           => 'nullable|string',
            'fact_dni'           => 'nullable|max:15',
            'fact_mobile'           => 'nullable|max:15',
            'propina_form'       => 'nullable|numeric',
            'order_time'       => 'nullable',
            'order_time_horario' => 'nullable|numeric',
        ],[
            'method_entrega.required' => 'Seleccione un Método de Entrega',
        ]);

        //return $request->all();
        $user_id = auth()->user()->id ?? null;

        $user = User::find($user_id);

        if (!is_null($user)) {

            $shipping = [
                'address' => $request->fact_address,
                'state' => $user->address->state ?? '',
                'zip' => $user->address->zip ?? '',
                'country' => $user->address->country ?? '',
                'city' => $user->address->city ?? '',
            ];
            // dd($shipping)

            $user->firstname = $request->fact_names;
            $user->lastname = $request->fact_lastname;
            $user->type_dni = $request->fact_type_dni;
            $user->dni  = $request->fact_dni;
            $user->address  = $shipping;
            $user->direction = $request->fact_address;
            $user->mobile = $request->fact_mobile;
            $user->save();
        }


        $invoice_information = [
            'names' => $request->fact_names.' '.$request->fact_lastname,
            'type_dni' => $request->fact_type_dni,
            'dni'  => $request->fact_dni,
            'address'    => $request->fact_address,
            'mobile' => $request->fact_mobile,
        ];        

        if($user_id){
            //checkeo si es prime
            $hoy = Carbon::now()->format('Y-m-d');
            $prime = PlanUsers::where('user_id', $user_id)
                ->where('status', 1)
                ->whereDate('expiration_date','>',$hoy)
                ->first();
            
            if($prime){
                $is_prime = true;
            }
        }

        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id??null)
        ->with(
            [
                'product.offer', 
                'product.categories',
                'product.productIva'
            ])->get();

        $coupon_amount  = 0;
        $coupon_code    = null;
        $cart_total     = 0;
        $product_categories = [];
        $base_imponible = 0;
        $excento = 0;
        $iva_total = 0;

        foreach ($carts_data as $cart) {
            $product = Product::where('id',$cart->product_id)->with('productIva')->first();

            //si es prime
            if($is_prime == true){
                $cart->is_prime = 1;
            }
            else{
                $cart->is_prime = 0;
            }

            if($cart->product->is_plan == 0){
                $product_categories[] = $cart->product->categories->pluck('id')->toArray();
            }

            $amount = $cart->product->offer->activeOffer->amount??0;
            $discount_type =  $cart->product->offer->activeOffer->discount_type??0;
            if($is_prime == true){
                $cart->is_prime = 1;
                $base_price = $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;

                //si tiene iva o no
                if($cart->product->iva == ACTIVE){
                    $base_imponible += $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;
                    if(!is_null($product->productIva)){
                        $iva_total += ((($cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price) * $cart->quantity) * ($product->productIva->percentage / 100));
                    }
                }
                else{
                    $excento += $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;
                }
            }
            else{
                $cart->is_prime = 0;
                $base_price = $cart->product->base_price??0;

                //si tiene iva o no
                if($cart->product->iva == ACTIVE){
                    $base_imponible += $cart->product->base_price;
                    if(!is_null($product->productIva)){
                        $iva_total += (($cart->product->base_price * $cart->quantity) * ($product->productIva->percentage / 100));
                    }
                }
                else{
                    $excento += $cart->product->base_price;
                }
            }
                

            if($cart->attributes != null){
                $s_price = priceAfterAttribute($cart->product, $cart->attributes);
            }else{
                $details['variants']        = null;
                $details['offer_amount']    = calculateDiscount($amount, $discount_type, $base_price);
                if(optional($cart->product)->offer){
                    $s_price = $base_price - calculateDiscount($amount, $discount_type, $base_price);
                }else{
                    $s_price = $base_price;
                }
            }
            $cart_total += $s_price * $cart->quantity;


        }
        $base_imponible = str_replace(',', '.', $base_imponible);
        $iva = $iva_total; //calculateIva($base_imponible);
        $iva = str_replace(',', '.', $iva);
        $excento = str_replace(',', '.', $excento);
        $cart_total = str_replace(',', '.', $cart_total);

        if(session('coupon')){
            $coupon = Coupon::where('coupon_code', session('coupon')['code'])->with('categories')->first();

            // Check Minimum Subtotal
            if($cart_total < $coupon->minimum_spend){
                return response()->json(['error' => "Lo sentimos, tiene que pedir una cantidad mínima de $coupon->minimum_spend $general->cur_text"]);
            }

            // Check Maximum Subtotal
            if($coupon->maximum_spend !=null && $cart_total > $coupon->maximum_spend){
                return response()->json(['error' => "Lo sentimos, tienes que pedir la cantidad máxima de $coupon->maximum_spend $general->cur_text"]);
            }

            //Check Limit Per Coupon
            if($coupon->appliedCoupons->count() >= $coupon->usage_limit_per_coupon){
                return response()->json(['error' => "Lo sentimos, su cupón ha excedido el límite máximo de uso"]);
            }

            //Check Limit Per User
            if($coupon->appliedCoupons->where('user_id', auth()->id())->count() >= $coupon->usage_limit_per_user){
                return response()->json(['error' => "Lo sentimos, ya alcanzó el límite de uso máximo para este cupón"]);
            }

            if($cart->product->is_plan == 0){
                $product_categories = array_unique(array_flatten($product_categories));
                if($coupon){
                    $coupon_categories = $coupon->categories->pluck('id')->toArray();
                    $coupon_products = $coupon->products->pluck('id')->toArray();

                    $cart_products = $carts_data->pluck('product_id')->unique()->toArray();

                    if(empty(array_intersect($coupon_products, $cart_products))){
                        if(empty(array_intersect($product_categories, $coupon_categories))){
                            return response()->json(['error' => "El cupón no está disponible en varios productos del carrito."]);
                           // $notify[]=['error', 'El cupón no está disponible en varios productos del carrito.'];
                           // return redirect()->back()->withNotify($notify);
                        }
                    }

                    if($coupon->discount_type == 1){
                        $coupon_amount = $coupon->coupon_amount;
                    }else{
                        $coupon_amount = $cart_total * $coupon->coupon_amount / 100;
                    }
                    $coupon_code    = $coupon->coupon_code;
                }
            }
            
        }

        //OBTENGO LA DIRECCION DE ENVÍO SELECCIONADA
        if($request->method_entrega == 1)
        {
            $shipping_address = UserShipping::where('id',$request->shippingUser)->where('user_id', $user_id)->first();
        }else if($request->method_entrega == 2)
        {
            $shipping_address = Admin::where('id',1)->first();
            $shipping_address = [
                'names' => $shipping_address->name,
                'mobile'  => $shipping_address->mobile,
                'address'    => $shipping_address->address,
            ];
        }

        //OBTENGO LOS DATOS DEL TIPO DE ENVÍO
        $shipping_data  = ShippingMethod::find($request->checkbox_shipping);

        $order = new Order;
        $order->order_number        = getTrx();
        $order->user_id             = auth()->user()->id;
        $order->shipping_address    = json_encode($shipping_address);
        $order->shipping_method_id  = $request->checkbox_shipping;
        $order->shipping_charge     = $shipping_data->charge;
        $order->invoice_information = json_encode($invoice_information);
        $order->order_type          = 1;
        $order->payment_status      = 1;
        isset($request->propina_form) ? $order->propina = $request->propina_form : $order->propina = 0;
        isset($request->coupon_amount) ? $order->coupon_amount = $request->coupon_amount : $order->coupon_amount = 0;
        $order->order_time          = isset($request->order_time) ? Carbon::parse($request->order_time)->format('d-m-Y') : null;
        $order->order_time_horario  = isset($request->order_time_horario) ? $request->order_time_horario : null;
        // $order->coupon_code = $coupon->coupon_code;
        $order->save();
        $details = [];

        foreach($carts_data as $cart){
            $od = new OrderDetail();
            $od->order_id       = $order->id;
            $od->product_id     = $cart->product_id;
            $od->quantity       = $cart->quantity;
            //si es prime
            if($cart->is_prime == 1){
                $od->base_price     = $cart->product->prime_price??$cart->product->base_price;
            }
            else{
                $od->base_price     = $cart->product->base_price;
            }
            $od->prime_price = $cart->product->prime_price??0;

            $amount = $cart->product->offer->activeOffer->amount??0;
            $discount_type =  $cart->product->offer->activeOffer->discount_type??0;
            $offer_amount = calculateDiscount($amount, $discount_type, $base_price);
            if($cart->is_prime == 1){
                $od->base_price  = $cart->product->prime_price ?? $cart->product->base_price;
            }
            else{
                $od->base_price  = $cart->product->base_price??0;
            }                

            if($cart->attributes != null){
                $attr_item                   = productAttributesDetails($cart->attributes);
                $attr_item['offer_amount'] = $offer_amount;
                //si es prime
                if($cart->is_prime == 1){
                    $sub_total                   = ((($cart->product->prime_price??$cart->product->base_price) + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                }
                else{
                    $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                }
                
                $od->total_price             = $sub_total;
                unset($attr_item['extra_price']);
                $od->details                 = json_encode($attr_item);
            }else{
                $details['variants']        = null;
                $details['offer_amount']    = $offer_amount;
                //si es prime
                if($cart->is_prime == 1){
                    $sub_total                  = ( ($cart->product->prime_price??$cart->product->base_price) - $offer_amount) * $cart->quantity;
                }
                else{
                    $sub_total                  = ($cart->product->base_price  - $offer_amount) * $cart->quantity;
                }
                
                $od->total_price            = $sub_total;
                $od->details                = json_encode($details);
            }
            $od->save();


        }

        $order->base_imponible = getAmount($base_imponible);
        $order->excento = getAmount($excento);
        $order->iva = getAmount($iva);
        $order->total_amount =  getAmount(((($cart_total - $coupon_amount) + $shipping_data->charge ) + $order->propina - $order->coupon_amount ) + $iva);
        $order->save();
        session()->put('order_number', $order->order_number);
        //session()->put('order', $order);


        //Envío email al superadmin de q recibió un pedido, y al cliente
        $message_to_admin = 'Acaba de recibir una orden por parte del cliente ' . $user->firstname .' '. $user->lastname . 
        ' por un monto de: '.number_format($order->total_amount,2) .'$. Este atento para confirmar el pago.';
        $admin = Admin::where('role_id',3)->first();
        $message_to_client = '¡Felicidades! Acaba de realizar un pedido en alfogolarexpress por un monto de: ' .number_format($order->total_amount,2) .
        '$. Estamos a su servicio.' ;
        
        try {
            send_general_email($admin->email, '¡Alerta de Orden!', $message_to_admin, 'Admin');
            send_general_email($user->email, '¡Pedido realizado!', $message_to_client, $user->firstname .' '. $user->lastname);
        } catch (\Exception $exp) {
            return response()->json(['error' => "Error de email"]);
            //$notify[] = ['error', 'Error de email'];
            //return back()->withNotify($notify);
            
        }

        if($coupon_code != null){
            $applied_coupon = new AppliedCoupon();
            $applied_coupon->user_id    = auth()->id();
            $applied_coupon->coupon_id  = $coupon->id;
            $applied_coupon->order_id   = $order->id;
            $applied_coupon->amount     = $coupon_amount;
            $applied_coupon->save();
        }
        
        return response()->json(['order' => $order]);
    }
}
