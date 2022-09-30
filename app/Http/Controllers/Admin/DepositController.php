<?php

namespace App\Http\Controllers\Admin;

use App\Deposit;
use App\GeneralSetting;
use App\Http\Controllers\Controller;
use App\Order;
use App\OrderDetail;
use App\PlanUsers;
use App\Transaction;
use App\User;
use App\Rates;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DepositController extends Controller
{

    public function pending()
    {
        $page_title = 'Pagos Pendientes';
        $empty_message = 'Sin Pagos Pendientes.';
        $deposits = Deposit::pending()->with(['user', 'gateway'])->latest()->paginate(getPaginate());
        // dd($deposits);
        return view('admin.deposit.log', compact('page_title', 'empty_message', 'deposits'));
    }


    public function approved()
    {
        $page_title = 'Pagos Aprobados';
        $empty_message = 'Sin Pagos Aprobados.';
        $deposits = Deposit::where('status', 1)->with(['user', 'gateway'])->latest()->paginate(getPaginate());
        return view('admin.deposit.log', compact('page_title', 'empty_message', 'deposits'));
    }

    public function successful()
    {
        $page_title = 'Pagos Realizados';
        $empty_message = 'Sin Pagos Realizados.';
        $deposits = Deposit::where('status', 1)->with(['user', 'gateway'])->latest()->paginate(getPaginate());
        return view('admin.deposit.log', compact('page_title', 'empty_message', 'deposits'));
    }

    public function rejected()
    {
        $page_title = 'Pagos Rechazados';
        $empty_message = 'Sin Pagos Rechazados.';
        $deposits = Deposit::where('status', 3)->with(['user', 'gateway'])->latest()->paginate(getPaginate());
        return view('admin.deposit.log', compact('page_title', 'empty_message', 'deposits'));
    }

    public function deposit()
    {
        $page_title = 'Todos los Pagos';
        $empty_message = 'Sin Historial de Pagos Disponible.';
        $deposits = Deposit::with(['user', 'gateway'])->where('status','!=',0)->latest()->paginate(getPaginate());
        return view('admin.deposit.log', compact('page_title', 'empty_message', 'deposits'));
    }

    public function search(Request $request, $scope)
    {
        $search = $request->search;
        $page_title = '';
        $empty_message = 'Sin Resultados.';
        $deposits = Deposit::with(['user', 'gateway'])->where('status','!=',0)->where(function ($q) use ($search) {
            $q->where('trx', 'like', "%$search%")->orWhereHas('user', function ($user) use ($search) {
                $user->where('username', 'like', "%$search%");
            });
        });
        switch ($scope) {
            case 'pending':
                $page_title .= 'Búsqueda de Pagos Pendientes';
                $deposits = $deposits->where('status', 2);
                break;
            case 'approved':
                $page_title .= 'Búsqueda de Pagos Aprobados';
                $deposits = $deposits->where('status', 1);
                break;
            case 'rejected':
                $page_title .= 'Búsqueda de Pagos Rechazados';
                $deposits = $deposits->where('status', 3);
                break;
            case 'list':
                $page_title .= 'Búsqueda del Historial de Pagos';
                break;
        }
        $deposits = $deposits->paginate(getPaginate());
        $page_title .= ' - ' . $search;

        return view('admin.deposit.log', compact('page_title', 'search', 'scope', 'empty_message', 'deposits'));
    }

    public function details($id)
    {
        $general = GeneralSetting::first();
        $deposit = Deposit::where('id', $id)->with(['user', 'gateway'])->firstOrFail();
        $page_title = $deposit->user->username.' solicitado ' . getAmount($deposit->amount) . ' '.$general->cur_text;
        $details = ($deposit->detail != null) ? json_encode($deposit->detail) : null;

        return view('admin.deposit.detail', compact('page_title', 'deposit','details'));
    }

    public function approve(Request $request)
    {

        $request->validate(['id' => 'required|integer']);
        $deposit = Deposit::where('id',$request->id)->where('status',2)->firstOrFail();
        $deposit->update(['status' => 1]);

        $order = Order::where('id', $deposit->order_id)->with('orderDetail.product')->first();
        $order->payment_status = 1;

        //si es un plan confirmo la orden una vez cinfirmo el pago
        foreach($order->orderDetail as $od){
            if($od->product->is_plan == 1){
                $order->status = 3; //entregado

                //registro el plan_user
                $orderdetail = OrderDetail::where('order_id', $order->id)->with('product.planDetails')->first();
                if($orderdetail->product->is_plan == 1){
                    $meses = $orderdetail->product->planDetails->meses; 
                    
                    $planuser = PlanUsers::where('user_id', $deposit->user_id)->first();
                    if($planuser){
                        $planuser->product_id = $orderdetail->product->id;
                        //sumo los meses del plan
                        $planuser->expiration_date = Carbon::parse($planuser->expiration_date)->addMonths($meses)->format('Y-m-d');
                        $planuser->update();
                    }
                    else{
                        $planuser = new PlanUsers();
                        $planuser->user_id = $deposit->user_id;
                        $planuser->product_id = $orderdetail->product->id;
                        $planuser->init_date = Carbon::now()->format('Y-m-d');
                        $planuser->expiration_date = Carbon::parse($planuser->init_date)->addMonths($meses)->format('Y-m-d');
                        $planuser->save();
                    }           
                }
            }
        }

        $order->save();

        $user = User::find($deposit->user_id);
        $user->update();

        $transaction = new Transaction();
        $transaction->user_id = $deposit->user_id;
        $transaction->amount = getAmount($deposit->amount);
        $transaction->charge = getAmount($deposit->charge);
        $transaction->trx_type = '+';
        $transaction->details = 'Pagado por ' . $deposit->gateway_currency()->name;
        $transaction->trx =  $deposit->trx;
        $transaction->save();

        $tasa = Rates::select('tasa_del_dia')->where('status','1')->orderBy('id', 'desc')->first();
        $gnl = GeneralSetting::first();

        $order =  Order::find($deposit->order_id);

        notify($user, 'DEPOSIT_APPROVE', [
            'method_name' => $deposit->gateway_currency()->name,
            'order_number' =>  $order->order_number,
            'method_currency' => $deposit->method_currency,
            'method_amount' => getAmount($deposit->final_amo),
            'amount' => getAmount($deposit->amount),
            'charge' => getAmount($deposit->charge),
            'currency' => $gnl->cur_text,
            'rate' => $tasa->tasa_del_dia,
            'trx' => $deposit->trx,
        ]);
        $notify[] = ['success', 'Pago Aprobado Exitosamente.'];

        return redirect()->route('admin.deposit.pending')->withNotify($notify);
    }

    public function reject(Request $request)
    {

        $request->validate([
            'id' => 'required|integer',
            'message' => 'required|max:250'
        ],[
            'message.required' => '¡Debe ingresar un Mensaje de forma obligatoria!',
            'message.max' => '¡El Mensaje excede el número maximo de caracteres! Debe ser menos de 250 caracteres.'
        ]);
        $deposit = Deposit::where('id',$request->id)->where('status',2)->firstOrFail();

        $deposit->admin_feedback = $request->message;
        $deposit->status = 3;
        $deposit->save();

        $gnl = GeneralSetting::first();
        $order =  Order::find($deposit->order_id);

        notify($deposit->user, 'DEPOSIT_REJECT', [
            'method_name' => $deposit->gateway_currency()->name,
            'order_id' =>  $order->order_number,
            'method_currency' => $deposit->method_currency,
            'method_amount' => getAmount($deposit->final_amo),
            'amount' => getAmount($deposit->amount),
            'charge' => getAmount($deposit->charge),
            'currency' => $gnl->cur_text,
            'rate' => getAmount($deposit->rate),
            'trx' => $deposit->trx,
            'rejection_message' => $request->message
        ]);

        $notify[] = ['success', 'El Pago ha Sido Rechazado.'];
        return  redirect()->route('admin.deposit.pending')->withNotify($notify);

    }
}
