<?php

namespace App\Http\Controllers\Admin;

use App\GeneralSetting;
use App\Http\Controllers\Controller;
use App\Order;
use App\OrderDetail;
use App\Product;
use App\ProductStock;
use App\StockLog;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function ordered()
    {
        $empty_message  = 'Sin Resultados';
        $page_title     = "Todas Las Órdenes";

        $query          =  Order::where('payment_status', '!=' ,0);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders = $query->with(['user', 'deposit.gateway', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());

        return view('admin.order.ordered', compact('page_title', 'orders', 'empty_message'));
    }

    public function codOrders()
    {
        $empty_message  = 'Sin Resultados';
        $page_title     = "Órdenes COD";
        $query          = Order::where('payment_status',2);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders         = $query->with(['user', 'deposit'])->orderBy('id', 'DESC')->paginate(getPaginate());

        return view('admin.order.ordered', compact('page_title', 'orders', 'empty_message'));
    }

    public function pending()
    {
        $empty_message  = 'Sin Resultados';
        $page_title     = "Órdenes pendientes";

        $query         = Order::where('payment_status', '!=' , 0)->where('status', 0);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders         = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('page_title', 'orders', 'empty_message'));

    }

    public function notifyorders(){
        return Order::where('payment_status', '!=' , 0)->where('status', 0)->count();
    }

    public function onProcessing()
    {
        $empty_message  = 'Sin Resultados';
        $page_title     = "Órdenes en procesamiento";

        $query         = Order::where('payment_status', '!=' ,0)->where('status', 1);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders         = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('page_title', 'orders', 'empty_message'));
    }

    public function dispatched()
    {
        $empty_message  = 'Sin Resultados';
        $page_title     = "Pedidos enviados";
        $query         = Order::where('payment_status', '!=' ,0)->where('status', 2);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders         = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('page_title', 'orders', 'empty_message'));
    }


    public function canceledOrders()
    {
        $empty_message  = 'Sin Resultados';
        $page_title     = "Pedidos cancelados";

        $query         = Order::where('payment_status', '!=' ,0)->where('status', 4);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders         = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('page_title', 'orders', 'empty_message'));
    }

    public function deliveredOrders()
    {
        $empty_message  = 'Sin Resultados';
        $page_title     = "Órdenes entregadas";

        $query         = Order::where('payment_status', '!=' ,0)->where('status', 3);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders         = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());


        return view('admin.order.ordered', compact('page_title', 'orders', 'empty_message'));
    }

    public function changeStatus(Request $request)
    {
        $order = Order::where('id',$request->id)->with('deposit','orderDetail')->first();
        if($order->status == 3){
            $notify[] = ['error', 'Esta Orden ya ha Sido Entregada!'];
            return back()->withNotify($notify);
        }

        foreach($order->deposit as $deposit){
            if($deposit->status == 2){ //si el deposito esta pendiente (no confirmado)
                $notify[] = ['error', 'Primero debe confirmar el pago de esta orden. TRX de pago: ' . $deposit->trx];
                return back()->withNotify($notify);
            }
        }

        // dd($request->all());
        $order->status = $request->action;

        if($request->action==1){
            $action = 'Procesado';

            //paso el stock de comprometido a venta
            foreach($order->orderDetail as $od){
                $stock  = ProductStock::where('product_id', $od->product_id)->first();
                if($stock){
                    $stockLog = StockLog::where('stock_id', $stock->id)->where('type', 3)->orderBy('created_at', 'desc')->first();
                    if($stockLog){
                        $stockLog->type = 2; //venta
                        $stockLog->save();
                    }                 
                }
            }
            

        }elseif($request->action == 2){
            $action = 'Despachado';
        }elseif($request->action == 3){
            $action = 'Entregado';
            foreach($order->deposit as $od){
                $od->status = 1;
                $od->save();
            }
        }elseif($request->action == 4){
            $action = 'Cancelado';

            $devolver = OrderDetail::where('order_id',$request->id)->get();
            foreach ($devolver as $key) {
                $stock = ProductStock::where('product_id',$key->product_id)->first();
                $cantidad = $stock->quantity;
                $stock->quantity = $cantidad + $key->quantity;
                $stock->save();

                $stockLog = StockLog::where('stock_id', $stock->id)->where('type', 3)->orderBy('created_at', 'desc')->first();
                if($stockLog){
                    $stockLog->delete();
                }               
            }

        }elseif($request->action == 0){
            $action = 'Pendiente';
        }

        $notify[] = ['success', 'Estado de la Orden cambiado a '.$action];
        $order->save();
        $general = GeneralSetting::first('sitename', 'cur_sym');

        $short_code = [
            'site_name' => $general->sitename,
            'order_id'  => $order->order_number
        ];

        if($request->action == 1){
            $act = 'ORDER_ON_PROCESSING_CONFIRMATION';
        }elseif($request->action == 2){
            $act = 'ORDER_DISPATCHED_CONFIRMATION';
        }elseif($request->action == 3){
            $act = 'ORDER_DELIVERY_CONFIRMATION';
        }elseif($request->action == 4){
            $act = 'ORDER_CANCELLATION_CONFIRMATION';
        }elseif($request->action == 0){
            $act = 'ORDER_RETAKE_CONFIRMATION';
        }
        notify($order->user, $act, $short_code);
        return back()->withNotify($notify);
    }

    public function orderDetails($id)
    {
        $page_title = 'Detalles del pedido';
        $order = Order::where('id', $id)->with('user','deposit','deposit.gateway','orderDetail', 'appliedCoupon', 'shipping')->firstOrFail();
        return view('admin.order.order_details', compact('order', 'page_title'));
    }
}
