<?php

namespace App\Http\Controllers\Admin;

use App\AssignProductAttribute;
use App\Http\Controllers\Controller;
use App\Product;
use App\ProductIva;
use Illuminate\Http\Request;

class ProductIvaController extends Controller
{
    public function all()
    {
        $data = ProductIva::where('deleted_at', null)->get();
        $page_title             = 'Administrar iva';
        $empty_message = "No hay IVAS registrados";

        return view('admin.products.iva.index', compact('page_title', 'data','empty_message','page_title'));
    }

    public function ivaAdd(Request $request, $id)
    {
        $request->validate([
            'nombre'          =>'sometimes|required|string',
            'percentage'      =>'required|string',
        ]);

        if($id != 0){
            $iva = ProductIva::find($id);
            $iva->name = $request->nombre??'';
            $iva->percentage = $request->percentage??0;
            $iva->update();
        }
        else if($id == 0){
            $iva = new ProductIva();
            $iva->name = $request->nombre;
            $iva->percentage = $request->percentage;
            $iva->status = 1;
            $iva->save();
        }
        

        $notify[] = ['success', 'IVA Actualizado Exitosamente'];
        return redirect()->back()->withNotify($notify);
    }

    public function ivaDelete(Request $request, $id)
    {
        $iva = ProductIva::find($id);
        if($iva->status==1){
            $iva->status = 0;
            $notify[] = ['success', 'IVA Desactivado Exitosamente'];
        }
        else{
            $iva->status = 1;
            $notify[] = ['success', 'IVA Activado Exitosamente'];
        }
        
        $iva->save();

        
        return redirect()->back()->withNotify($notify);
    }

}
