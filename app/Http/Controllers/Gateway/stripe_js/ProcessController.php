<?php

namespace App\Http\Controllers\Gateway\stripe_js;

use App\Cart;
use App\GeneralSetting;
use App\Transaction;
use App\GatewayCurrency;
use App\Deposit;
use App\Order;
use App\OrderDetail;
use App\PlanUsers;
use App\User;
use App\Rates;
use App\ProductStock;
use App\StockLog;
use App\EmailTemplate;
use App\Http\Controllers\Gateway\PaymentController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use Carbon\Carbon;


class ProcessController extends Controller
{

    /*
     * StripeJS Gateway
     */
    public static function process($deposit)
    {
        $StripeJSAcc = json_decode($deposit->gateway_currency()->gateway_parameter);
        $val['key'] = $StripeJSAcc->publishable_key;
        $val['name'] = Auth::user()->username;
        $val['description'] = "Payment with Stripe";
        $val['amount'] = $deposit->final_amo * 100;
        $val['currency'] = $deposit->method_currency;
        $send['val'] = $val;


        $alias = $deposit->gateway->alias;

        $send['src'] = "https://checkout.stripe.com/checkout.js";
        $send['view'] = 'user.payment.' . $alias;
        $send['method'] = 'post';
        $send['url'] = route('ipn.' . $alias);
        return json_encode($send);
    }

    /*
     * StripeJS js ipn
     */
    public function ipn(Request $request)
    {

        $track = Session::get('Track');
        $order = Order::where('payment_status', 0)->where('status',0)->orderBy('created_at', 'DESC')->first();
        $data = Deposit::where('order_id', $order->id)->where('method_code','<',1000)->orderBy('id', 'DESC')->first();
        if ($data->status == 1) {
            $notify[] = ['error', 'Invalid Request.'];
        }
        $StripeJSAcc = json_decode($data->gateway_currency()->gateway_parameter);


        Stripe::setApiKey($StripeJSAcc->secret_key);

        Stripe::setApiVersion("2020-03-02");

        $customer =  Customer::create([
            'email' => $request->stripeEmail,
            'source' => $request->stripeToken,
        ]);

        $charge = Charge::create([
            'customer' => $customer->id,
            'description' => 'Payment with Stripe',
            'amount' => $data->final_amo * 100,
            'currency' => $data->method_currency,
        ]);


        if ($charge['status'] == 'succeeded') {
            //PaymentController::userDataUpdate($data->trx);
            //$notify[] = ['success', 'Transaction was successful.'];

            $gnl = GeneralSetting::first();

            if ($data->status == 0) {
                $data['status'] = 1;
                $data->update();

                $user = User::find($data->user_id);
                $user->save();

                $gateway        = $data->gateway;
                $transaction    = new Transaction();
                $transaction->user_id = $data->user_id;
                $transaction->amount = $data->amount;
                $transaction->charge = getAmount($data->charge);
                $transaction->trx_type = '+';
                $transaction->details = 'Pago A través de ' . $gateway->name;
                $transaction->trx = $data->trx;
                $transaction->save();

                notify($user, 'DEPOSIT_COMPLETE', [
                    'method_name' => $data->gateway_currency()->name,
                    'method_currency' => $data->method_currency,
                    'method_amount' => getAmount($data->final_amo),
                    'amount' => getAmount($data->amount),
                    'charge' => getAmount($data->charge),
                    'currency' => $gnl->cur_text,
                    'rate' => getAmount($data->rate),
                    'trx' => $data->trx,
                    'order_id' => $order->order_number
                ]);
            }
        }

        //si hay otro metodo de pago para la orden
        $data2 = Deposit::where('order_id',$data->order_id)->where('status',0)->orderBy('id', 'DESC')->first();
        if(isset($data2) && $data2->status == 0){
            $notify[] = ['success', 'Transaction realizada, continue con la siguiente'];
            return redirect()->route('user.deposit.preview')->withNotify($notify);
        }
        else{
            //Comprometo el stock
            $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id??null)
            ->with(
                [
                    'product.offer', 
                    'product.categories'
                ]
            )->get();

            foreach ($carts_data as $cd) {
                $pid    = $cd->product_id;
                $attr   = $cd->attributes;
                $attr   = $cd->attributes?json_encode($cd->attributes):null;
                if($cd->product->track_inventory){
                    $stock  = ProductStock::where('product_id', $pid)->where('attributes', $attr)->first();
                    if($stock){
                        $stock->quantity   -= $cd->quantity;
                        $stock->save();
                        $log = new StockLog();
                        $log->stock_id  = $stock->id;
                        $log->quantity  = $cd->quantity;
                        $log->type      = 3; //comprometida (cuando el admin confirme la orden se marca como 2)
                        $log->save();
                    }
                }
            }

            Cart::where('user_id', auth()->user()->id??null)->delete();

            $order2 = Order::where('id', $data->order_id)->first();
            $order2->payment_status= 1;
            $order2->save();

            //si es un plan registro el plan al user y la orden la marco como entregada
            $od = OrderDetail::where('order_id', $order2->id)->with('product.planDetails')->first();
            if($od->product->is_plan == 1){

                //La orden la paso a 3 entregado
                $order->status = 3; //entregado
                $order->save();

                $meses = $od->product->planDetails->meses; 
                
                $planuser = PlanUsers::where('user_id', auth()->user()->id)->first();
                if($planuser){
                    $planuser->product_id = $od->product->id;
                    //sumo los meses del plan
                    $planuser->expiration_date = Carbon::parse($planuser->expiration_date)->addMonths($meses)->format('Y-m-d');
                    $planuser->update();
                }
                else{
                    $planuser = new PlanUsers();
                    $planuser->user_id = auth()->user()->id;
                    $planuser->product_id = $od->product->id;
                    $planuser->init_date = Carbon::now()->format('Y-m-d');
                    $planuser->expiration_date = Carbon::parse($planuser->init_date)->addMonths($meses)->format('Y-m-d');
                    $planuser->save();
                }              
            }

            //$notify[] = ['success', 'Transaction was successful.'];
            //return redirect()->route(gatewayRedirectUrl())->withNotify($notify);
            $notify[] = ['success', 'Su solicitud de pedido ha sido aceptada.'];
            return redirect()->route('user.deposit.history')->withNotify($notify);
        }

        
    }
}
