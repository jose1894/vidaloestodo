<?php

namespace App\Http\Controllers\Admin;

use App\Carrier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;

class CarrierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_title     = "Todos los transportistas";
        $empty_message  = "Sin transportistas todavía";
        $carriers         = Carrier::orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.carrier.index', compact('page_title', 'empty_message', 'carriers'));
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
        // dd($request);
        $validation_rule = [
            'name'                      => 'required|string|max:50|unique:carrier,name,'.$id,           
        ];

        if($id ==0){
            $carrier = new Carrier();
            $validation_rule['image_input']  = ['required', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])];
            $notify[] = ['success', 'Transportista creada con éxito'];
        }else{
            $carrier = Carrier::findOrFail($id);
            $validation_rule['image_input']  = ['nullable', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])];
            $notify[] = ['success', 'Transportista actualizado con éxito'];
        }
        $request->validate($validation_rule,[
            // 'meta_keywords.array.*'     => 'Todas las palabras clave',
            'image_input.required'      => 'El campo del logotipo es obligatorio'
        ]);

        if ($request->hasFile('image_input')) {

            try {
                $request->merge(['image' => $this->store_image($request->key, $request->image_input, $carrier->logo)]);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'No se pudo cargar la Imagen.'];
                return back()->withNotify($notify);
            }
        }else{
            $request->merge(['image'=>$carrier->logo]);
        }

        $carrier->name             = $request->name;
        $carrier->logo             = $request->image;
        $carrier->save();

        return redirect()->back()->withNotify($notify);
    }

    public function carrierSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Sin transportista encontrado';
            $search         = trim(strtolower($request->search));
            $carriers         = Carrier::where('name', 'like', "%$search%")
            ->orderByDesc('id', 'desc')
            ->paginate(getPaginate());
            $page_title     = 'Buscar transportista - ' . $search;
            return view('admin.carrier.index', compact('page_title', 'empty_message', 'carriers'));
        } else {
            return redirect()->route('admin.carrier.index');
        }

    }

    public function carrierTrashedSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Sin transportistas encontrados';
            $search         = trim(strtolower($request->search));
            $carriers         = Carrier::all()
            ->onlyTrashed()
            ->orderByDesc('id')
            ->where('name', 'like', "%$search%")->paginate(getPaginate());

            $page_title     = 'Buscar transportista borrado - ' . $search;
            return view('admin.carrier.index', compact('page_title', 'empty_message', 'carriers'));
        } else {
            return redirect()->route('admin.carriers.index');
        }

    }

    public function delete($id)
    {
        $carrier = Carrier::where('id', $id)->withTrashed()->first();

        if ($carrier->trashed()){
            $carrier->restore();
            $notify[] = ['success', 'Transportista restaurado correctamente'];
            return redirect()->back()->withNotify($notify);
        }else{
            $carrier->delete();
            $notify[] = ['success', 'Transportista borrado correctamente'];
            return redirect()->back()->withNotify($notify);
        }
    }

    protected function store_image($key, $image, $old_image = null)
    {
        $path = imagePath()['carrier']['path'];
        $size = imagePath()['carrier']['size'];
        return uploadImage($image, $path, $size, $old_image);
    }

    public function trashed()
    {
        $page_title     = "Transportistas borrados";
        $empty_message  = "Sin trasnsportistas todavía";
        $carriers         = Carrier::onlyTrashed()
        ->orderBy('id', 'desc')
        ->paginate(getPaginate());
        return view('admin.carrier.index', compact('page_title', 'empty_message', 'carriers'));
    }
}
