<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $page_title         = 'Gerente de métodos de envío';
        $empty_message      = 'Aún no se han creado métodos de envío';
        $shipping_methods   = ShippingMethod::where('is_plan',0)->latest()->paginate(getPaginate());

        return view('admin.shipping_method.index', compact('page_title', 'empty_message', 'shipping_methods'));
    }

    public function create()
    {
        $page_title = 'Crear un nuevo método de envío';

        return view('admin.shipping_method.create', compact('page_title'));
    }

    public function edit(ShippingMethod $id)
    {
        $shipping_method =  $id;
        $page_title = 'Editar método de envío';

        return view('admin.shipping_method.create', compact('page_title', 'shipping_method'));
    }

    public function store(Request $request, $id)
    {
        $validation_rule = [
            'name'          => 'required|string|max:191',
            'charge'        => 'required|numeric',
            'description'   => 'nullable|string|',
            'shipping_type' => 'nullable',
        ];
        $request->validate($validation_rule);

        if($id ==0){
            $sm = new ShippingMethod();
            $notify[] = ['success', 'Método de envío creado con éxito'];
        }else{
            $sm = ShippingMethod::findOrFail($id);
            $notify[] = ['success', 'Método de envío actualizado con éxito'];
        }

        $sm->name         = $request->name;
        $sm->charge       = $request->charge;
        $sm->shipping_type= $request->shipping_type??null;
        $sm->shipping_time= $request->deliver_in??0;
        $sm->description  = $request->description;
        $sm->save();

        return redirect()->back()->withNotify($notify);
    }

    public function delete(ShippingMethod $id)
    {
        $id->delete();
        $notify[] = ['success', 'Método de envío eliminado correctamente'];
        return redirect()->back()->withNotify($notify);
    }

    public function changeStatus(Request $request)
    {
        $method = ShippingMethod::findOrFail($request->id);
        if ($method) {
            if ($method->status == 1) {
                $method->status = 0;
                $msg = 'Activado con éxito';
            } else {
                $method->status = 1;
                $msg = 'Desactivado con éxito';
            }
            $method->save();
            return response()->json(['success' => true, 'message' => $msg]);
        }
    }

}
