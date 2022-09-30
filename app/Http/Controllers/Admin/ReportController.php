<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Order;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function transaction()
    {
        $page_title = 'Registros de transacciones';
        $transactions = Transaction::with('user')->latest()->paginate(getPaginate());
        $empty_message = 'Sin transacciones.';
        return view('admin.reports.transactions', compact('page_title', 'transactions', 'empty_message'));
    }

    public function transactionSearch(Request $request)
    {
        $search  = $page_title = 'Registros de transacciones';
        $empty_message  = 'Sin transacciones encontradas';

        if($request->search != null){
            $search         = trim(strtolower($request->search));
            $transactions   = Transaction::with('user')
            ->whereHas('user', function ($user) use ($search) {
                $user->where('username', 'like', "%$search%");
            })->orWhere('trx', 'like', "%$search%")->paginate(getPaginate());
            $page_title     = 'Buscar Transacciones - ' . $search;

        }elseif($request->has('date')){
            $request->validate([
                'date' => 'required|string',
            ]);

            $date               = explode('to', $request->date);

            if(count($date) == 2) {
                $start_date       = date('Y-m-d H:i:s',strtotime(trim($date[0])));
                $end_date         = date('Y-m-d H:i:s',strtotime(trim($date[1])));

                $transactions     = Transaction::whereBetween('created_at', [$start_date, $end_date])->paginate(getPaginate());

                $page_title     = 'Transactions - Between ' . showDatetime($start_date, 'M d, y').' to ' .showDatetime($end_date, 'M d, y');

            }else{
                $start_date       = date('Y-m-d',strtotime(trim($date[0])));
                $transactions     = Transaction::whereDate('created_at', $start_date)->paginate(getPaginate());

                $page_title     = 'Transactions of ' . showDatetime($start_date, 'M d, y');
            }

        }else{
            $page_title     = 'Transactions Logs';
            $transactions = Transaction::with('user')->whereHas('user')->latest()->paginate(getPaginate());
        }

        return view('admin.reports.transactions', compact('page_title', 'transactions', 'empty_message','search'));
    }


    public function userTransactionSearch(Request $request, $id)
    {
        $user = User::findOrfail($id);
        $search  = $page_title = '';
        $key    = $request->search??'';
        $empty_message  = 'Sin transacciones encontradas';

        if($request->search != null){
            $search         = trim(strtolower($request->search));
            $transactions   = Transaction::where('user_id', $id)->with('user')
            ->whereHas('user', function ($user) use ($search) {
                $user->where('username', 'like', "%$search%");
            })->orWhere('trx', 'like', "%$search%")->paginate(getPaginate());
            $page_title     = 'Buscar Transacciones - ' . $search;

        }elseif($request->has('date')){
            $request->validate([
                'date' => 'required|string',
            ]);

            $date               = explode('to', $request->date);
            if(count($date) == 2) {
                $start_date       = date('Y-m-d H:i:s',strtotime(trim($date[0])));
                $end_date         = date('Y-m-d H:i:s',strtotime(trim($date[1])));

                $transactions     = Transaction::where('user_id', $id)->whereBetween('created_at', [$start_date, $end_date])->paginate(getPaginate());

                $page_title     = 'Transacciones de '.$user->fullname .' Entre ' . showDatetime($start_date, 'M d, y').' para ' .showDatetime($end_date, 'M d, y');

            }else{
                $start_date       = date('Y-m-d', strtotime(trim($date[0])));
                $transactions     = Transaction::where('user_id', $id)->whereDate('created_at',$start_date)->paginate(getPaginate());

                $page_title     = 'Transacciones de '.$user->fullname.' por ' . showDatetime($start_date, 'M d, y');
            }
        }else{
            $transactions   = Transaction::where('user_id', $id)->with('user')
            ->whereHas('user')->paginate(getPaginate());
            $page_title     = 'Todas las Transacciones de ' . $user->fullname;
        }

        return view('admin.reports.transactions', compact('page_title', 'transactions', 'empty_message','search', 'user'));
    }

    public function order()
    {
        $page_title = 'Órdenes de Pedidos';
        $orders     = Order::where('payment_status', '!=' ,0)->with('user', 'deposit')->latest()->paginate(15);

        $empty_message = 'Sin órdenes.';
        return view('admin.reports.orders', compact('page_title', 'orders', 'empty_message'));
    }

    public function orderByUser($id)
    {
        $user = User::findOrFail($id);
        $page_title = 'Órdenes de Pedidos de '. $user->fullname;


        $orders     =  Order::where('user_id', $id)->where('payment_status', '!=' ,0)->with('user', 'deposit')->paginate(config('constansts.table.default'));

        $empty_message = 'Sin órdenes.';
        return view('admin.reports.orders', compact('page_title', 'user', 'orders', 'empty_message'));
    }

    public function orderSearch(Request $request)
    {
        $key    = $request->search??'';

        if($key){
            $orders = Order::where('payment_status', '!=' ,0)->with('user', 'deposit')->where('order_number', 'like', "%$key%")->paginate(config('constansts.table.default'));
            $page_title = 'Buscar Resultados para el ID de la Órden ' . $key;

        }elseif($request->has('date')){
            $request->validate([
                'date' => 'required|string',
            ]);

            if($request->date){
                $data['title']['Date to Date']      = $request->date ;

                $date               = explode('to', $request->date);

                if(count($date) == 2) {
                    $start_date       = date('Y-m-d H:i:s',strtotime(trim($date[0])));
                    $end_date         = date('Y-m-d H:i:s',strtotime(trim($date[1])));
                    $orders           = Order::where('payment_status', '!=' ,0)->with('user', 'deposit')->whereBetween('created_at', [$start_date, $end_date])->paginate(getPaginate());

                    $page_title = 'Pedidos entre : ' . showDateTime($start_date, 'd M, Y') .' para '. showDateTime($end_date, 'd M, Y');
                }else{
                    $start_date       = date('Y-m-d', strtotime(trim($date[0])));
                    $orders           = Order::where('payment_status', '!=' ,0)->with('user', 'deposit')->whereDate('created_at',$start_date)->paginate(getPaginate());

                    $page_title     = 'Órdenes de ' . showDatetime($start_date, 'M d, y');
                }

            }else{
                $page_title = 'Órdenes de Pedidos';
                $orders     = Order::where('payment_status', '!=' ,0)->with('user', 'deposit')->latest()->paginate(15);
            }

        }

        $empty_message  = 'Sin órdenes.';

        return view('admin.reports.orders', compact('page_title', 'orders', 'empty_message', 'key'));
    }

    public function userOrderSearch(Request $request, $id)
    {
        $user = User::findOrfail($id);
        $key    = $request->search??'';

        if($key){
            $orders = Order::where('user_id', $id)
            ->where('payment_status', '!=' ,0)
            ->with('user', 'deposit')->where('order_number', 'like', "%$key%")
            ->paginate(config('constansts.table.default'));

            $page_title = 'Buscar orden de' . $user->fullname .' - ID de la orden : ' . $key;
        }elseif($request->has('date')){
            $request->validate([
                'date' => 'required|string',
            ]);

            $date               = explode('to', $request->date);

            if(count($date) == 2) {

                $start_date       = date('Y-m-d H:i:s',strtotime(trim($date[0])));
                $end_date         = date('Y-m-d H:i:s',strtotime(trim($date[1])));

                $orders     = Order::where('user_id', $id)->where('payment_status', '!=' ,0)->with('user', 'deposit')->whereBetween('created_at', [$start_date, $end_date])->paginate(getPaginate());

                $page_title = 'Órdenes de '. $user->name .' Entre : ' . showDateTime($start_date, 'd M, Y') .' para '. showDateTime($end_date, 'd M, Y');

            }else{
                $start_date       = date('Y-m-d', strtotime(trim($date[0])));

                $orders           = Order::where('user_id', $id)->where('payment_status', '!=' ,0)->with('user', 'deposit')->whereDate('created_at',$start_date)->paginate(getPaginate());

                $page_title     = 'Órdenes de '.$user->name .' '. showDatetime($start_date, 'M d, y');
            }

        }

        $empty_message  = 'Sin Órdenes';

        return view('admin.reports.orders', compact('page_title', 'user', 'orders', 'empty_message', 'key'));
    }
}
