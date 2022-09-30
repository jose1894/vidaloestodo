<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Product;
use App\PlanDetails;
use App\PlanUsers;
use App\ProductStock as Stock;
use Carbon\Carbon;

class PlansController extends Controller
{
    public function index(){
        $page_title     = "Planes";
        $empty_message  = "Sin Registros";
        $plans = PlanDetails::with([
            'product'
        ])
        ->orderBy('id', 'desc')
        ->paginate(getPaginate());       

        return view('admin.plans.index', compact('page_title', 'empty_message','plans'));
    }

    public function store(Request $request, $id)
    {
        //return $id;
        $decimal = floatval(str_replace (",", ".", ($request->base_price)));
        $precio = number_format($decimal,2,".","");

        if($id == 0){
            $plan = new Product();
            $notify[] = ['success', 'Tasa Creada Exitosamente'];
        }else{
            $plan = Product::findOrFail($id);
            $plan->name = $request->name;
            $plan->base_price = $precio;
            $plan->update();

            $pd = PlanDetails::where('product_id', $plan->id)->first();
            $pd->name = $request->name;
            $pd->description = $request->description;
            $pd->meses = $request->meses;
            $pd->update();

            $notify[] = ['success', 'Plan Editado Exitosamente'];
        }



        return redirect()->back()->withNotify($notify);
    }

    public function delete($id)
    {
        $plans = PlanDetails::findOrFail($id);
        if($plans->status == 1){
            $plans->status = 0;
            $notify[] = ['success', 'Plan inactivado correctamente'];
        }
        else{
            $plans->status = 1;
            $notify[] = ['success', 'Plan activado correctamente'];
        }

        $plans->save();

        return redirect()->back()->withNotify($notify);
    }
}
