<?php

namespace App\Exports;

use App\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class OrderExport implements FromView
{
    use Exportable;

    public function __construct(int $order)
    {
        $this->order = $order;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Order::all();
    }

    public function view(): View
    {
        $order = Order::where('id', $this->order)->with('deposit', 'user.plan_users', 'orderDetail.product', 'shipping')->first();
       // dd($order);
        return view('invoice.export', [
            'order' => $order
        ]);
    }
}
