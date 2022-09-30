<?php

namespace App\Http\Controllers\Gateway;

use App\Cart;
use App\GeneralSetting;
use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\GatewayCurrency;
use App\Deposit;
use App\Order;
use App\OrderDetail;
use App\PlanUsers;
use App\ProductStock;
use App\StockLog;
use Illuminate\Support\Facades\Auth;
use Session;
use App\User;
use App\Rates;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct()
    {
        return $this->activeTemplate = activeTemplate();
    }

    public function deposit(Request $request)
    {
        if ($request->order_number) {
            session()->put('order_number', $request->order_number);
        }
        $order = Order::where('order_number', session('order_number'))->first();
        $rate = Rates::where('status', '1')->orderBy('id', 'ASC')->first();

        $totalbs = 0;
        $totalbs = ($order->total_amount * $rate->tasa_del_dia);
        $order->totalbs = $totalbs;

        if ($order) {
            if ($order->payment_status == 1)
                return redirect('/');
        }

        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', 1);
        })->with('method')->orderby('method_code')->get();

        if ($order->total_amount <= 0) {
            $notify[] = ['error', 'El Monto Total no puede ser menor a 0'];
            return back()->withNotify($notify);
        }
        // dd($order);
        $page_title = 'Métodos de Pago';
        return view($this->activeTemplate . 'user.payment.deposit', compact('gatewayCurrency', 'page_title', 'order', 'rate'));
    }

    public function depositInsert(Request $request)
    {
        //return $request->all;
        $request->validate([
            'method_code'   => 'required',
            'currency'      => 'required',
            'gateway_id' => 'required',
            'cantidad_pagar' => 'required',
        ]);

        $order = Order::where('order_number', session('order_number'))->first();
        $rate = Rates::where('status', '1')->orderBy('id', 'ASC')->first();

        $cantidadpagar = $request->cantidad_pagar;
        $sumTotal = 0;

        foreach ($cantidadpagar as $key => $cp) {
            //si no es null la cantidad
            if (!is_null($cp)) {

                if ($cp <= 0) {
                    $notify[] = ['error', 'Por favor digite un monto a pagar correcto.'];
                    return back()->withNotify($notify);
                }
                $sumTotal += $cp;
            }
        }

        $sumTotal = str_replace(',', '.', $sumTotal);

        //si el total de cantidad pagar es menor q el total de la orden
        if ($sumTotal < $order->total_amount) {
            $notify[] = ['error', 'El monto a pagar es menor que el total de la orden.'];
            return back()->withNotify($notify);
        }

        //si existe un deposito ya creado q quedo sin procesar lo elimino
        $oldDeposit = Deposit::where('order_id', $order->id)->get();

        if ($oldDeposit) {
            foreach ($oldDeposit as $old) {
                $old->delete();
            }
        }

        //elimino los null de cantidad_pagar
        $request->cantidad_pagar = array_filter($request->cantidad_pagar);
        $cantidades = array_values($request->cantidad_pagar);
        $methods = [];

        foreach ($request->gateway_id as $key => $gid) {
            if (!is_null($gid)) {
                $methods[$key]['gateway_id'] = $gid;
                $methods[$key]['cantidad_pagar'] = $cantidades[$key];
            }
        }

        // foreach($request->gateway_id as $key => $gid){
        //     if(!is_null($gid)){
        //         $methods[$key]['gateway_id'] = $gid;
        //         if(!is_null($request->cantidad_pagar[$key])){
        //             $methods[$key]['cantidad_pagar'] = $request->cantidad_pagar[$key];
        //         }
        //         else{
        //             foreach($request->cantidad_pagar as $cp){
        //                 if(!is_null($cp)){
        //                     $methods[$key]['cantidad_pagar'] = $cp;
        //                 }
        //             }
        //         }
        //     }
        // }

        foreach ($methods as $item) {
            $trx = getTrx();
            $user = auth()->user();

            if ($order->payment_status == 1) {
                $notify[] = ['error', 'Tienes un pago realizado a esta orden'];
                return redirect('/')->withNotify($notify);
            }

            $now = \Carbon\Carbon::now();

            // if (session()->has('req_time') && $now->diffInSeconds(\Carbon\Carbon::parse(session('req_time'))) <= 2) {
            //     $notify[] = ['error', 'Procesando Pago. Por Favor Espere...'];
            //     return redirect()->route('user.deposit.preview')->withNotify($notify);
            // }
            session()->put('req_time', $now);
            //$gate = GatewayCurrency::where('method_code', $request->method_code)->where('currency', $request->currency)->first();
            $gate = GatewayCurrency::where('id', $item['gateway_id'])->first();

            if (!$gate) {
                $notify[] = ['error', 'Enlace Inválido'];
                return back()->withNotify($notify);
            }

            $charge     = getAmount($gate->fixed_charge + (($item['cantidad_pagar']) * $gate->percent_charge / 100));
            $payable    = getAmount(($item['cantidad_pagar']) + $charge);
            $final_amo  = getAmount($payable);

            // if($gate->method_code >= 1000){
            //     //si es bolivares calculo el total en bs
            //     if(
            //         strtoupper($gate->currency) == 'BOLIVARES' || 
            //         strtoupper($gate->currency) == 'BOLÍVARES' || 
            //         strtoupper($gate->currency) == 'BS' || 
            //         strtoupper($gate->currency) == 'BSF' ||
            //         strtoupper($gate->currency) == 'BS.F' ||
            //         strtoupper($gate->currency) == 'BSS' ||
            //         strtoupper($gate->currency) == 'BS.S' ||
            //         strtoupper($gate->currency) == 'BF' ||
            //         strtoupper($gate->currency) == 'B.F' ||
            //         strtoupper($gate->currency) == 'B.S' 
            //     ) 
            //     {
            //         $final_amo  = getAmount($payable * $rate->tasa_del_dia);
            //     }
            //     else{
            //         $final_amo  = getAmount($payable);
            //     }            
            // }
            // else{
            //     $final_amo  = getAmount($payable);
            // }


            $depo['user_id']            = $user->id;
            $depo['method_code']        = $gate->method_code;
            $depo['order_id']           = $order->id;
            $depo['method_currency']    = strtoupper($gate->currency);
            $depo['amount']             = $item['cantidad_pagar'];
            $depo['charge']             = $charge;
            $depo['rate']               = $rate->tasa_del_dia;
            $depo['final_amo']          = getAmount($final_amo);
            $depo['btc_amo']            = 0;
            $depo['btc_wallet']         = "";
            $depo['trx']                = $trx;
            $depo['try']                = 0;
            $depo['status']             = 0;

            $data = Deposit::create($depo);

            Session::put('Track', $data['trx']);
        }


        return redirect()->route('user.deposit.preview');
    }


    public function depositPreview()
    {

        $track = Session::get('Track');
        $order = Order::where('payment_status', 0)->where('status', 0)->orderBy('created_at', 'DESC')->first();
        $data = Deposit::where('order_id', $order->id)->orderBy('id', 'DESC')->get();
        $rates = Rates::where('status', '1')->orderBy('id', 'ASC')->first();
        $tasa_del_dia = 1;
        if (is_null($rates)) {
            $rates = Rates::where('id', '<>', 0)->orderBy('id', 'ASC')->first();
            if (!is_null($rates)) {
                $tasa_del_dia = $rates->tasa_del_dia;
            }
        }
        // dd($rates);
        if (is_null($data)) {
            $notify[] = ['error', 'Solicitud de Pago no válida'];
            return redirect()->route('user.deposit')->withNotify($notify);
        }

        $totalbs = 0;
        foreach ($data as $item) {
            // if ($item->status != 0) {
            //     return 'depositPreview status 0';
            //     $notify[] = ['error', 'Solicitud de pago no válida'];
            //     return redirect()->route('user.deposit')->withNotify($notify);
            // }
            $totalbs = ($item->amount + $item->charge) * $tasa_del_dia;
            $item->totalbs = $totalbs;

            if (
                strtoupper($item->method_currency) == 'BOLIVARES' ||
                strtoupper($item->method_currency) == 'BOLÍVARES' ||
                strtoupper($item->method_currency) == 'BS' ||
                strtoupper($item->method_currency) == 'BSF' ||
                strtoupper($item->method_currency) == 'BS.F' ||
                strtoupper($item->method_currency) == 'BSS' ||
                strtoupper($item->method_currency) == 'BS.S' ||
                strtoupper($item->method_currency) == 'BF' ||
                strtoupper($item->method_currency) == 'B.F' ||
                strtoupper($item->method_currency) == 'B.S'
            ) {
                $item->moneda = 'bs';
            } else {
                $item->moneda = 'usd';
            }
        }

        $page_title = 'Vista Previa de Pago';

        return view($this->activeTemplate . 'user.payment.preview', compact('data', 'page_title', 'rates'));
    }


    public function depositConfirm($id)
    {
        $track = Session::get('Track');
        $order = Order::where('payment_status', 0)->where('status', 0)->orderBy('created_at', 'DESC')->first();
        $deposit = Deposit::where('id', $id)->where('order_id', $order->id)->where('method_code', '<', 1000)->orderBy('id', 'DESC')->with('gateway')->get();

        if (is_null($deposit)) {
            $notify[] = ['error', 'Solicitud de pago no válida'];
            return redirect()->route('user.deposit')->withNotify($notify);
        }

        foreach ($deposit as $dep) {


            if ($dep->status != 0) {
                $notify[] = ['error', 'Solicitud de pago no válida'];
                return redirect()->route('user.deposit')->withNotify($notify);
            }

            if ($dep->method_code >= 1000) {
                $this->userDataUpdate($dep);
                $notify[] = ['success', 'Su solicitud de pedido está en cola para su aprobación.'];
                return back()->withNotify($notify);
            }


            $dirName = $dep->gateway->alias;
            $new = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

            $data = $new::process($dep);
            $data = json_decode($data);


            if (isset($data->error)) {
                $notify[] = ['error', $data->message];
                return redirect()->route('user.deposit')->withNotify($notify);
            }
            if (isset($data->redirect)) {
                return redirect($data->redirect_url);
            }

            // for Stripe V3
            if (@$data->session) {
                $dep->btc_wallet = $data->session->id;
                $dep->save();
            }
        }

        $page_title = 'Confirmar Pago';

        return view($this->activeTemplate . $data->view, compact('data', 'page_title', 'deposit'));
    }


    public static function userDataUpdate($trx)
    {
        $rate = Rates::where('status', '1')->orderBy('id', 'ASC')->first();
        $gnl = GeneralSetting::first();
        $data = Deposit::where('trx', $trx)->first();
        Cart::where('user_id', auth()->user()->id ?? null)->delete();
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

            $order = Order::where('id', $data->order_id)->first();
            $order->payment_status = 1;
            $order->save();


            notify($user, 'DEPOSIT_COMPLETE', [
                'method_name' => $data->gateway_currency()->name,
                'method_currency' => $data->method_currency,
                'method_amount' => getAmount($data->final_amo),
                'amount' => getAmount($data->amount),
                'charge' => getAmount($data->charge),
                'currency' => $gnl->cur_text,
                'rate' => $rate->tasa_del_dia,
                'trx' => $data->trx,
                'order_id' => $order->order_number
            ]);
        }
    }

    public function manualDepositConfirm($id)
    {
        $track = Session::get('Track');
        $order = Order::where('payment_status', 0)->where('status', 0)->orderBy('created_at', 'DESC')->first();
        $data = Deposit::with('gateway')->where('id', $id)->where('status', 0)->where('order_id', $order->id)->where('method_code', '>', 999)->first();

        $rate = Rates::where('status', '1')->orderBy('id', 'ASC')->first();

        if (!$data) {
            return redirect()->route('user.deposit');
        }

        if ($data->status != 0) {
            return redirect()->route('user.deposit');
        }
        if ($data->method_code > 999) {
            $page_title = 'Confirmar Pago';
            $method = $data->gateway_currency();
            // dd($data->method_currency);
            return view($this->activeTemplate . 'user.manual_payment.manual_confirm', compact('data', 'page_title', 'method'));
        }

        // foreach($data as $key => $item){

        //     if ($item->status != 0) {
        //         return redirect()->route('user.deposit');
        //     }
        //     if ($item->method_code > 999) {


        //         $method = collect([]);
        //         $method->push($item->gateway_currency());

        //         $item->method = $method;



        //     }
        // }

        //return view($this->activeTemplate . 'user.manual_payment.manual_confirm', compact('data', 'page_title', 'method'));

        abort(404);
    }

    public function manualDepositUpdate(Request $request, $id)
    {
        //return $request->all();
        $rate = Rates::where('status', '1')->orderBy('id', 'ASC')->first();
        $track = session()->get('Track');
        $order = Order::where('payment_status', 0)->where('status', 0)->orderBy('created_at', 'DESC')->first();
        $data = Deposit::with('gateway')->where('id', $id)->where('status', 0)->where('order_id', $order->id)->where('method_code', '>', 999)->first();
        if (!$data) {
            return redirect()->route('user.deposit');
        }

        if ($data->status != 0) {
            return redirect()->route('user.deposit');
        }

        $params = json_decode($data->gateway_currency()->gateway_parameter);

        $rules = [];
        $inputField = [];
        $verifyImages = [];

        if ($params != null) {
            foreach ($params as $key => $cus) {
                $rules[$key] = [$cus->validation];
                if ($cus->type == 'file') {
                    array_push($rules[$key], 'image');
                    array_push($rules[$key], 'mimes:jpeg,jpg,png');
                    array_push($rules[$key], 'max:2048');

                    array_push($verifyImages, $key);
                }
                if ($cus->type == 'text') {
                    array_push($rules[$key], 'max:191');
                }
                if ($cus->type == 'textarea') {
                    array_push($rules[$key], 'max:300');
                }
                $inputField[] = $key;
            }
        }


        $this->validate($request, $rules);


        $directory = date("Y") . "/" . date("m") . "/" . date("d");
        $path = imagePath()['verify']['deposit']['path'] . '/' . $directory;


        $collection = collect($request);

        $reqField = [];
        if ($params != null) {
            foreach ($collection as $k => $v) {

                foreach ($params as $inKey => $inVal) {
                    if ($k != $inKey) {
                        continue;
                    } else {
                        if ($inVal->type == 'file') {
                            if ($request->hasFile($inKey)) {

                                try {
                                    $reqField[$inKey] = [
                                        'field_name' => $directory . '/' . uploadImage($request[$inKey], $path),
                                        'type' => $inVal->type,
                                    ];
                                } catch (\Exception $exp) {
                                    $notify[] = ['error', 'No se pudo cargar su ' . $inKey];
                                    return back()->withNotify($notify)->withInput();
                                }
                            }
                        } else {
                            $reqField[$inKey] = $v;
                            $reqField[$inKey] = [
                                'field_name' => $v,
                                'type' => $inVal->type,
                            ];
                        }
                    }
                }
            }
            $data->detail = $reqField;
        } else {
            $data->detail = null;
        }

        $data->status = 2; // pending
        $data->update();

        //si hay otro metodo de pago para la orden
        $data2 = Deposit::where('order_id', $data->order_id)->where('status', 0)->orderBy('id', 'DESC')->first();
        if (isset($data2) && $data2->status == 0) {
            $notify[] = ['success', 'Transaction realizada, continue con la siguiente'];
            return redirect()->route('user.deposit.preview')->withNotify($notify);
        } else {

            //Comprometo el stock
            $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id ?? null)
                ->with(
                    [
                        'product.offer',
                        'product.categories'
                    ]
                )->get();

            foreach ($carts_data as $cd) {
                $pid    = $cd->product_id;
                $attr   = $cd->attributes;
                $attr   = $cd->attributes ? json_encode($cd->attributes) : null;
                if ($cd->product->track_inventory) {
                    $stock  = ProductStock::where('product_id', $pid)->where('attributes', $attr)->first();
                    if ($stock) {

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

            Cart::where('user_id', auth()->user()->id ?? null)->delete();

            //seteo el payment_status
            $order->payment_status = 1;
            $order->save();

            // \Log::info($link);
            // \Log::info($link.'/'.$order->id);

            $gnl = GeneralSetting::first();
            notify($data->user, 'DEPOSIT_REQUEST', [
                'method_name' => $data->gateway_currency()->name,
                'method_currency' => $data->method_currency,
                'method_amount' => getAmount($data->final_amo),
                'amount' => getAmount($data->amount),
                'charge' => getAmount($data->charge),
                'currency' => $gnl->cur_text,
                'rate' => $rate->tasa_del_dia,
                'trx' => $data->trx,
                'order_number' => $order->order_number,
                'link' => route('print.invoice', $order->id),
            ]);

            $notify[] = ['success', 'Su solicitud de pedido ha sido aceptada.'];
            return redirect()->route('user.deposit.history')->withNotify($notify);
        }
    }

    public function paymentPending(Request $request)
    {
        // $request->id = 230;
        $gnl = GeneralSetting::first();
        $logs = auth()->user()->deposits()->find($request->id);

        $logs2 = Deposit::find($request->id)->delete();
        $order = Order::find($logs->order_id);
        $user = auth()->user();

        $detail['numero_de_referencia'] = [
            'field_name' => $request->ref,
            'type' => 'text',
        ];

        $rate = Rates::where('status', '1')->orderBy('id', 'ASC')->first();
        $deposit = new Deposit;

        $deposit->user_id            = $logs->user_id;
        $deposit->method_code        = $logs->method_code;
        $deposit->order_id           = $logs->order_id;
        $deposit->method_currency    = $logs->method_currency;
        $deposit->amount             = $logs->amount;
        $deposit->charge             = $logs->charge;
        $deposit->rate               = $rate->tasa_del_dia;
        $deposit->final_amo          = $logs->final_amo;
        $deposit->detail             = $detail;
        $deposit->btc_amo            = $logs->btc_amo;
        $deposit->btc_wallet         = $logs->btc_wallet;
        $deposit->trx                = getTrx();
        $deposit->try                = $logs->try;
        $deposit->status             = 2;
        $deposit->save();


        // dd($request->id, $deposit);
        notify(auth()->user(), 'CANCEL_TO_PENDING', [
            'user_name' => $user->name,
            'order_number' => $order->order_number,
            'amount' => getAmount($logs->amount),
            'currency' => $gnl->cur_text,
        ]);

        $notify[] = ['success', 'Su pedido ha sido cambiado a Pendiente.'];
        return redirect()->route('user.deposit.history')->withNotify($notify);
    }

    public function depositNew(Request $request)
    {
        $depo['user_id']            = $request->user_id;
        $depo['method_code']        = $request->method_code;
        $depo['order_id']           = $request->order_id;
        $depo['method_currency']    = strtoupper($request->method_currency);
        $depo['amount']             = $request->amount;
        $depo['charge']             = $request->charge;
        $depo['rate']               = $request->rate;
        $depo['final_amo']          = getAmount($request->final_amo);
        $depo['detail']             = $request->detail;
        $depo['btc_amo']            = 0;
        $depo['btc_wallet']         = "";
        $depo['trx']                = $request->trx;
        $depo['try']                = 0;
        $depo['status']             = $request->status;

        $data = Deposit::create($depo);
       /// $this->reduceInventory();
        return response()->json(['depost' => $data]);
    }

    public function reduceInventory()
    {
        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id ?? null)
            ->with(
                [
                    'product.offer',
                    'product.categories'
                ]
            )->get();

        foreach ($carts_data as $cd) {
            $pid    = $cd->product_id;
            $attr   = $cd->attributes;
            $attr   = $cd->attributes ? json_encode($cd->attributes) : null;
            if ($cd->product->track_inventory) {
                $stock  = ProductStock::where('product_id', $pid)->where('attributes', $attr)->first();
                if ($stock) {

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

        Cart::where('user_id', auth()->user()->id ?? null)->delete();
    }
}
