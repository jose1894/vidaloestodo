<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rates;

class RatesController extends Controller
{
    public function index(){
        $page_title     = "Tasa del día";
        $empty_message  = "Sin Registros";
        $rates = Rates::orderBy('id', 'desc')
            ->paginate(getPaginate());

        return view('admin.rates.index', compact('page_title', 'empty_message','rates'));
    }

    public function store(Request $request, $id)
    {

        $decimal = floatval(str_replace (",", ".", ($request->tasa_del_dia)));
        $tasa = number_format($decimal,2,".","");

        $confirm= rates::where('status',1)->get();

        foreach ($confirm as $key) {
            if($key->status == 1){
                $key->status == 0;
                $key->save();
            }
        }

        if($id ==0){
            $rate = new rates();
            $notify[] = ['success', 'Tasa Creada Exitosamente'];
        }else{
            $rate = Rates::findOrFail($id);
            $notify[] = ['success', 'Tasa Editada Exitosamente'];
        }


        $rate->tasa_del_dia = $tasa;
        $rate->status = '1';
        $rate->save();

        session()->put('rate', $rate->tasa_del_dia);
        session()->save();
        return redirect()->back()->withNotify($notify);
    }

    public function delete($id)
    {
        $rate = Rates::findOrFail($id);
        if($rate->status == '1'){
            $rate->status = '0';
            $notify[] = ['success', 'Tasa del día inactivada correctamente'];
        }
        else{
            $rate->status = '1';
            $notify[] = ['success', 'Tasa del día activada correctamente'];
        }

        $rate->save();

        return redirect()->back()->withNotify($notify);
    }
}
