<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\GeneralSetting;
use App\User;
use App\Cart;
use App\PlanUsers;
use Carbon\Carbon;
use App\Coupon;
use App\Product;
use App\UserShipping;
use App\Admin;
use App\ShippingMethod;
use App\Order;
use App\AppliedCoupon;
use App\OrderDetail;

if (!defined('ACTIVE')) define('ACTIVE', 1);
if (!defined('INACTIVE')) define('INACTIVE', 0);

class OrderController extends Controller
{
    public function confirmOrder(Request $request)
    {
        //return $request->all();
        $general = GeneralSetting::first();
        $notify[] = [];
        $payment = 1;
        $is_prime = false;

        $user_id = auth()->user()->id ?? null;
        $user = User::find($user_id);


        if (!is_null($user)) {


            $shipping = [
                'address' => $request->billing['address'],
                'state' => $user->address->state ?? '',
                'zip' => $user->address->zip ?? '',
                'country' => $user->address->country ?? '',
                'city' => $user->address->city ?? '',
            ];

            $user->firstname = $request->billing['name'];
            $user->lastname = $request->billing['lastname'];
            $user->type_dni = $request->billing['type'];
            $user->dni  = $request->billing['document'];
            $user->address  = $shipping;
            $user->direction = $request->billing['address'];
            $user->mobile = $request->billing['mobile'];
            $user->save();
        }

        $invoice_information = [
            'names'     => $request->billing['name'] . ' ' . $request->billing['lastname'],
            'type_dni'  => $request->billing['type'],
            'dni'       => $request->billing['document'],
            'address'   => $request->billing['address'],
            'mobile'    => $request->billing['mobile'],
        ];

        $is_prime = false;
        if ($user_id) {
            //checkeo si es prime
            $hoy = Carbon::now()->format('Y-m-d');
            $prime = PlanUsers::where('user_id', $user_id)
                ->where('status', 1)
                ->whereDate('expiration_date', '>', $hoy)
                ->first();

            if ($prime) {
                $is_prime = true;
            }
        }

        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id ?? null)
            ->with(
                [
                    'product.offer',
                    'product.categories'
                ]
            )->get();


        $coupon_amount  = 0;
        $coupon_code    = null;
        $cart_total     = 0;
        $product_categories = [];
        $base_imponible = 0;
        $excento = 0;
        $iva_total = 0;

        foreach ($carts_data as $cart) {
            $product = Product::where('id', $cart->product_id)->with('productIva')->first();

            //si es prime
            if ($is_prime == true) {
                $cart->is_prime = 1;
            } else {
                $cart->is_prime = 0;
            }

            if ($cart->product->is_plan == 0) {
                $product_categories[] = $cart->product->categories->pluck('id')->toArray();
            }

            $amount = $cart->product->offer->activeOffer->amount ?? 0;
            $discount_type =  $cart->product->offer->activeOffer->discount_type ?? 0;
            if ($is_prime == true) {
                $cart->is_prime = 1;
                $base_price = $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;

                //si tiene iva o no
                if ($cart->product->iva == ACTIVE) {
                    $base_imponible += $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;
                    if (!is_null($product->productIva)) {
                        $iva_total += ((($cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price) * $cart->quantity) * ($product->productIva->percentage / 100));
                    }
                } else {
                    $excento += $cart->product->prime_price > 0 && $cart->product->prime_price != null ? $cart->product->prime_price : $cart->product->base_price;
                }
            } else {
                $cart->is_prime = 0;
                $base_price = $cart->product->base_price ?? 0;

                //si tiene iva o no
                if ($cart->product->iva == ACTIVE) {
                    $base_imponible += $cart->product->base_price;
                    if (!is_null($product->productIva)) {
                        $iva_total += (($cart->product->base_price * $cart->quantity) * ($product->productIva->percentage / 100));
                    }
                } else {
                    $excento += $cart->product->base_price;
                }
            }


            if ($cart->attributes != null) {
                $s_price = priceAfterAttribute($cart->product, $cart->attributes);
            } else {
                $details['variants']        = null;
                $details['offer_amount']    = calculateDiscount($amount, $discount_type, $base_price);
                if (optional($cart->product)->offer) {
                    $s_price = $base_price - calculateDiscount($amount, $discount_type, $base_price);
                } else {
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

        if (session('coupon')) {
            $coupon = Coupon::where('coupon_code', session('coupon')['code'])->with('categories')->first();

            // Check Minimum Subtotal
            if ($cart_total < $coupon->minimum_spend) {
                return response()->json(['error' => "Lo sentimos, tiene que pedir una cantidad mínima de $coupon->minimum_spend $general->cur_text"]);
            }

            // Check Maximum Subtotal
            if ($coupon->maximum_spend != null && $cart_total > $coupon->maximum_spend) {
                return response()->json(['error' => "Lo sentimos, tienes que pedir la cantidad máxima de $coupon->maximum_spend $general->cur_text"]);
            }

            //Check Limit Per Coupon
            if ($coupon->appliedCoupons->count() >= $coupon->usage_limit_per_coupon) {
                return response()->json(['error' => "Lo sentimos, su cupón ha excedido el límite máximo de uso"]);
            }

            //Check Limit Per User
            if ($coupon->appliedCoupons->where('user_id', auth()->id())->count() >= $coupon->usage_limit_per_user) {
                return response()->json(['error' => "Lo sentimos, ya alcanzó el límite de uso máximo para este cupón"]);
            }

            if ($cart->product->is_plan == 0) {
                $product_categories = array_unique(array_flatten($product_categories));
                if ($coupon) {
                    $coupon_categories = $coupon->categories->pluck('id')->toArray();
                    $coupon_products = $coupon->products->pluck('id')->toArray();

                    $cart_products = $carts_data->pluck('product_id')->unique()->toArray();

                    if (empty(array_intersect($coupon_products, $cart_products))) {
                        if (empty(array_intersect($product_categories, $coupon_categories))) {
                            return response()->json(['error' => "El cupón no está disponible en varios productos del carrito."]);
                            // $notify[]=['error', 'El cupón no está disponible en varios productos del carrito.'];
                            // return redirect()->back()->withNotify($notify);
                        }
                    }

                    if ($coupon->discount_type == 1) {
                        $coupon_amount = $coupon->coupon_amount;
                    } else {
                        $coupon_amount = $cart_total * $coupon->coupon_amount / 100;
                    }
                    $coupon_code    = $coupon->coupon_code;
                }
            }
        }

        //OBTENGO LA DIRECCION DE ENVÍO SELECCIONADA
        if ($request->method_entrega == 1) {
            $shipping_address = UserShipping::where('id', $request->shippingUser)->where('user_id', $user_id)->first();
        } else if ($request->method_entrega == 2) {
            $shipping_address = Admin::where('id', 1)->first();
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

        foreach ($carts_data as $cart) {
            $od = new OrderDetail();
            $od->order_id       = $order->id;
            $od->product_id     = $cart->product_id;
            $od->quantity       = $cart->quantity;
            //si es prime
            if ($cart->is_prime == 1) {
                $od->base_price     = $cart->product->prime_price ?? $cart->product->base_price;
            } else {
                $od->base_price     = $cart->product->base_price;
            }
            $od->prime_price = $cart->product->prime_price ?? 0;

            $amount = $cart->product->offer->activeOffer->amount ?? 0;
            $discount_type =  $cart->product->offer->activeOffer->discount_type ?? 0;
            $offer_amount = calculateDiscount($amount, $discount_type, $base_price);
            if ($cart->is_prime == 1) {
                $od->base_price  = $cart->product->prime_price ?? $cart->product->base_price;
            } else {
                $od->base_price  = $cart->product->base_price ?? 0;
            }

            if ($cart->attributes != null) {
                $attr_item                   = productAttributesDetails($cart->attributes);
                $attr_item['offer_amount'] = $offer_amount;
                //si es prime
                if ($cart->is_prime == 1) {
                    $sub_total                   = ((($cart->product->prime_price ?? $cart->product->base_price) + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                } else {
                    $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                }

                $od->total_price             = $sub_total;
                unset($attr_item['extra_price']);
                $od->details                 = json_encode($attr_item);
            } else {
                $details['variants']        = null;
                $details['offer_amount']    = $offer_amount;
                //si es prime
                if ($cart->is_prime == 1) {
                    $sub_total                  = (($cart->product->prime_price ?? $cart->product->base_price) - $offer_amount) * $cart->quantity;
                } else {
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
        $order->total_amount =  getAmount(((($cart_total - $coupon_amount) + $shipping_data->charge) + $order->propina - $order->coupon_amount) + $iva);
        $order->save();


        if ($coupon_code != null) {
            $applied_coupon = new AppliedCoupon();
            $applied_coupon->user_id    = auth()->id();
            $applied_coupon->coupon_id  = $coupon->id;
            $applied_coupon->order_id   = $order->id;
            $applied_coupon->amount     = $coupon_amount;
            $applied_coupon->save();
        }

        return response()->json([
            'general' => $general,
            'user' => $user,
            'request' => $request->all(),
            'shipping' => $shipping,
            'invoice_information' => $invoice_information,
            'carts_data' => $carts_data,
            'base_imponible' => $base_imponible,
            'iva' => $iva,
            'excento' => $excento,
            'cart_total' => $cart_total,
            'shipping_address' => $shipping_address,
            'method_entrega' => $request->method_entrega,
            'shippingUser' => $request->shippingUser,
            'shipping_data' =>  $shipping_data,
            'order' => $order,
            'order_number' => $order->order_number
        ]);
    }

    public function orders(Request $request)
    {
        $type = is_null($request->type) ? 'all' : $request->type;
        switch ($request->type) {
            case "incomplete-payment":
                $p_status = 0;
                break;
            case "processing":
                $status   = [1];
                break;
            case "dispatched":
                $status   = [2];
                break;
            case "completed":
                $status   = [3];
                break;
            case "canceled":
                $status   = [4];
                break;
            case "pending":
                $status   = [0];
                break;
            case "all":
                $orders   = Order::where('user_id', auth()->user()->id)->where('payment_status', '!=', 0)
                    ->with('deposit.gateway', 'orderDetail.product', 'appliedCoupon', 'shipping')
                    ->latest()->get();
                break;
            default:
                abort(403, 'Acción No Autorizada.');
        }

        if (isset($p_status)) {
            $orders = Order::where('user_id', auth()->user()->id)->with('deposit.gateway', 'orderDetail.product', 'appliedCoupon', 'shipping')
                ->where('payment_status', 0)->latest()->get();
        }
        if (isset($status)) {
            $orders = Order::where('user_id', auth()->user()->id)->with('deposit.gateway', 'orderDetail.product', 'appliedCoupon', 'shipping')
                ->whereIn('status', $status)->where('payment_status', 1)->get();
        }

        return response()->json($orders);
    }

    public function orderDetails($order_number)
    {
        $general = GeneralSetting::first();
        $order = Order::where('order_number', $order_number)->where('user_id', auth()->user()->id)->with('deposit.gateway', 'orderDetail.product', 'appliedCoupon', 'shipping')->first();

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




        $orderDetail = [];
        foreach ($order->orderDetail as $detail) {
            $orderDetail[] = $this->detailComplements($detail);
        }

        $order = json_decode(json_encode($order));
        $order->order_detail = $orderDetail;

        return response()->json(['order' => $order, 'discountPrime' => $discountPrime, 'cur_sym' => $general->cur_sym]);
    }

    public function detailComplements($detail)
    {
        $general = GeneralSetting::first();

        $json = json_decode(json_encode($detail));
        $details = json_decode($json->details);
        $json->offer_price = $general->cur_sym . getAmount($details->offer_amount);
        $json->extra_price = 0;
        $base_price = 0;

        if ($details->variants) {
            foreach ($details->variants as $item) {
                $json->extra_price += $item->price;
            }
        }
        $base_price = $json->base_price + $json->extra_price;
        $json->detail_price = $general->cur_sym . ($json->base_price - getAmount($details->offer_amount));
        $json->total_price = $general->cur_sym . getAmount(($base_price - $details->offer_amount) * $json->quantity);
        return $json;
    }
}
