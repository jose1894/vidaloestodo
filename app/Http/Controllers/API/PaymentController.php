<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Deposit;
use App\ProductStock;
use App\StockLog;
use App\Cart;

class PaymentController extends Controller
{
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

        return response()->json(['carts_data' => $carts_data]);
    }
}
