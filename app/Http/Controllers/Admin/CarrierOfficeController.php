<?php

namespace App\Http\Controllers\Admin;

use App\Carrier;
use App\CarrierOffice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\State;
// Activamos el uso de las funciones de caché.
use Illuminate\Support\Facades\Cache;

class CarrierOfficeController extends Controller
{
    //

    public function index() {

        $page_title     = "Todas las oficinas";
        $empty_message  = "Sin oficinas todavía";
        $states         = State::orderBy('name', 'asc')->get();
        $carriers       = Carrier::orderBy('name', 'asc')->get();
        $carrierOffices = CarrierOffice::orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.carrier-office.index', compact('page_title', 'empty_message', 'carrierOffices', 'carriers', 'states'));
    }

    public function trashed()
    {
        $page_title     = "Oficinas de transporte borradas";
        $empty_message  = "Sin oficinas todavía";
        $states         = State::orderBy('name', 'asc')->get();
        $carriers       = Carrier::orderBy('name', 'asc')->get();
        $carrierOffices         = CarrierOffice::onlyTrashed()
        ->orderBy('id', 'desc')
        ->paginate(getPaginate());
        return view('admin.carrier-office.index', compact('page_title', 'empty_message', 'carrierOffices', 'carriers', 'states'));
    }

    public function carrierOfficeSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Sin oficina encontrada';
            $search         = trim(strtolower($request->search));
            $states         = State::orderBy('name', 'asc')->get();
            $carriers       = Carrier::orderBy('name', 'asc')->get();
            $carrierOffices         = CarrierOffice::where('name', 'like', "%$search%")
            ->orderByDesc('id', 'desc')
            ->paginate(getPaginate());
            $page_title     = 'Buscar oficina - ' . $search;
            return view('admin.carrier-office.index', compact('page_title', 'empty_message', 'carrierOffices', 'carriers', 'states'));
        } else {
            return redirect()->route('admin.carrier-office.index');
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id = $request->id;
        $validation_rule = [
            'name'    => 'required|string|max:50|unique:carrier,name,'.$id,           
            'carrier_id'=> 'required',           
            'state_id'=> 'required',           
            'city_id' => 'required',
            'address' => 'required'           
        ];

        if($id ==0){
            $carrierOffice = new CarrierOffice();
            $notify[] = ['success', 'Oficina creada con éxito'];
        }else{
            $carrierOffice = CarrierOffice::findOrFail($id);
            $notify[] = ['success', 'Oficina actualizada con éxito'];
        }
        $request->validate($validation_rule,[
            // 'meta_keywords.array.*'     => 'Todas las palabras clave',
            'name.required'      => 'El campo nombre es obligatorio',
            'carrier_id.required'      => 'El campo transportista es obligatorio',
            'state_id.required'      => 'El campo estado es obligatorio',
            'city_id.required'      => 'El campo ciudad es obligatorio',
            'address.required'      => 'El campo direcciòn es obligatorio',
        ]);

        $carrierOffice->name       = $request->input('name');
        $carrierOffice->carrier_id = $request->input('carrier_id');
        $carrierOffice->state_id   = $request->input('state_id');
        $carrierOffice->city_id    = $request->input('city_id');
        $carrierOffice->code       = $request->input('code');
        $carrierOffice->address    = $request->input('address');

        $carrierOffice->save();

        return redirect()->back()->withNotify($notify);
    }

    public function delete($id)
    {
        $carrierOffice = CarrierOffice::where('id', $id)->withTrashed()->first();

        if ($carrierOffice->trashed()){
            $carrierOffice->restore();
            $notify[] = ['success', 'Oficina restaurada correctamente'];
            return redirect()->back()->withNotify($notify);
        }else{
            $carrierOffice->delete();
            $notify[] = ['success', 'Oficina borrado correctamente'];
            return redirect()->back()->withNotify($notify);
        }
    }
}
