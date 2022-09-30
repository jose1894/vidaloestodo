<?php

namespace App\Http\Controllers\Admin;

use App\Brand;
use App\Http\Controllers\Controller;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $page_title     = "Todas las Ramas";
        $empty_message  = "Sin Marcas todavía";
        $brands         = Brand::with('products')
        ->orderBy('id', 'desc')
        ->paginate(getPaginate());
        return view('admin.brands.index', compact('page_title', 'empty_message', 'brands'));
    }

    public function trashed()
    {
        $page_title     = "Marcas Borradas";
        $empty_message  = "Sin Marcas todavía";
        $brands         = Brand::with('products')
        ->onlyTrashed()
        ->orderBy('id', 'desc')
        ->paginate(getPaginate());
        return view('admin.brands.index', compact('page_title', 'empty_message', 'brands'));
    }


    public function brandSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Sin Marcas Encontrada';
            $search         = trim(strtolower($request->search));
            $brands         = Brand::with('products')
            ->where('name', 'like', "%$search%")
            ->orderByDesc('id', 'desc')
            ->paginate(getPaginate());
            $page_title     = 'Buscar Marca - ' . $search;
            return view('admin.brands.index', compact('page_title', 'empty_message', 'brands'));
        } else {
            return redirect()->route('admin.brand.all');
        }

    }

    public function brandTrashedSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Sin Marcas Encontradas';
            $search         = trim(strtolower($request->search));
            $brands         = Brand::with('products')
            ->onlyTrashed()
            ->orderByDesc('id')
            ->where('name', 'like', "%$search%")->paginate(getPaginate());

            $page_title     = 'Buscar Marca Borrada - ' . $search;
            return view('admin.brands.index', compact('page_title', 'empty_message', 'brands'));
        } else {
            return redirect()->route('admin.brand.all');
        }

    }

    public function store(Request $request, $id)
    {
        $validation_rule = [
            'name'                      => 'required|string|max:50|unique:brands,name,'.$id,
            'meta_title'                => 'nullable|string|max:191',
            'meta_description'          => 'nullable|string|max:191',
            'meta_keywords'             => 'nullable|array',
            'meta_keywords.array.*'     => 'nullable|string',
        ];

        if($id ==0){
            $brand = new Brand();
            $validation_rule['image_input']  = ['required', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])];
            $notify[] = ['success', 'Marca Creada con éxito'];
        }else{
            $brand = Brand::findOrFail($id);
            $validation_rule['image_input']  = ['nullable', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])];
            $notify[] = ['success', 'Marca Actualizada con éxito'];
        }
        $request->validate($validation_rule,[
            'meta_keywords.array.*'     => 'Todas las palabras clave',
            'image_input.required'      => 'El campo del logotipo es obligatorio'
        ]);

        if ($request->hasFile('image_input')) {

            try {
                $request->merge(['image' => $this->store_image($request->key, $request->image_input, $brand->logo)]);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'No se pudo cargar la Imagen.'];
                return back()->withNotify($notify);
            }
        }else{
            $request->merge(['image'=>$brand->logo]);
        }

        $brand->name             = $request->name;
        $brand->meta_title       = $request->meta_title;
        $brand->meta_description = $request->meta_description;
        $brand->meta_keywords    = $request->meta_keywords;
        $brand->logo             = $request->image;
        $brand->save();

        return redirect()->back()->withNotify($notify);
    }

    public function delete($id)
    {
        $category = Brand::where('id', $id)->withTrashed()->first();

        if ($category->trashed()){
            $category->restore();
            $notify[] = ['success', 'Marca Restaurada Correctamente'];
            return redirect()->back()->withNotify($notify);
        }else{
            $category->delete();
            $notify[] = ['success', 'Marca Borrada Correctamente'];
            return redirect()->back()->withNotify($notify);
        }
    }

    public function setTop(Request $request)
    {
        $brand = Brand::findOrFail($request->id);
        if ($brand) {
            if ($brand->top == 1) {
                $brand->top = 0;
                $msg = 'Excluído del top de Marcas';
            } else {
                $brand->top = 1;
                $msg = 'Incluído en el top de Marcas';
            }
            $brand->save();
            return response()->json(['success' => $msg]);
        }
    }

    protected function store_image($key, $image, $old_image = null)
    {
        $path = imagePath()['brand']['path'];
        $size = imagePath()['brand']['size'];
        return uploadImage($image, $path, $size, $old_image);
    }
}
