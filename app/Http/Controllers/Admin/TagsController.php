<?php

namespace App\Http\Controllers\Admin;

use App\Tags;
use App\Http\Controllers\Controller;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function index()
    {
        $page_title     = "Todas las Etiquetas";
        $empty_message  = "Sin Etiquetas todavía";
        $tags            = Tags::with('products')->orderBy('id', 'desc')->paginate(getPaginate());
        //   dd($tags);
        return view('admin.tags.index', compact('page_title', 'empty_message', 'tags'));
    }

    public function trashed()
    {
        $page_title     = "Marcas Etiquetas";
        $empty_message  = "Sin Etiquetas todavía";
        $tags            = Tags::orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.tags.index', compact('page_title', 'empty_message', 'brands'));
    }

    public function store(Request $request, $id)
    {
        //dd($request);
        $validation_rule = [
            'name'                      => 'required|string|max:50|unique:brands,name,' . $id
        ];
        $notify[] = ['success', 'Etiqueta Creada con éxito'];
        $tags = new Tags();
        $tags->name             = $request->name;
        $tags->save();
        return redirect()->back()->withNotify($notify);
    }

    public function delete($id)
    {
        $Tags = Tags::where('id', $id)->withTrashed()->first();

        if ($Tags->trashed()) {
            $Tags->restore();
            $notify[] = ['success', 'Etiqueta Restaurada Correctamente'];
            return redirect()->back()->withNotify($notify);
        } else {
            $Tags->delete();
            $notify[] = ['success', 'Etiqueta Borrada Correctamente'];
            return redirect()->back()->withNotify($notify);
        }
    }
}
