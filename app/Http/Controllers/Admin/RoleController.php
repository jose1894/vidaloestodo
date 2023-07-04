<?php

namespace App\Http\Controllers\Admin;

use App\Roles;
use App\Http\Controllers\Controller;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $page_title     = "Todos los Roles";
        $empty_message  = "Sin Roles todavía";
        $roles            = Roles::orderBy('id', 'desc')->paginate(getPaginate());
        //   dd($roles);
        return view('admin.roles.index', compact('page_title', 'empty_message', 'roles'));
    }

    public function trashed()
    {
        $page_title     = "Marcas Roles";
        $empty_message  = "Sin Roles todavía";
        $roles            = Roles::onlyTrashed()
        ->orderBy('id', 'desc')
        ->paginate(getPaginate());
        return view('admin.roles.index', compact('page_title', 'empty_message', 'roles'));
    }

    public function rolSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Sin Rol Encontrado';
            $search         = trim(strtolower($request->search));
            $roles         = Roles::where('name', 'like', "%$search%")
            ->orderByDesc('id', 'desc')
            ->paginate(getPaginate());
            $page_title     = 'Buscar Rol - ' . $search;
            return view('admin.roles.index', compact('page_title', 'empty_message', 'roles'));
        } else {
            return redirect()->route('admin.roles.all');
        }

    }

    public function rolTrashedSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Sin Roles Encontradas';
            $search         = trim(strtolower($request->search));
            $roles         = Roles::onlyTrashed()
            ->orderByDesc('id')
            ->where('name', 'like', "%$search%")->paginate(getPaginate());

            $page_title     = 'Buscar Rol Borrado - ' . $search;
            return view('admin.roles.index', compact('page_title', 'empty_message', 'roles'));
        } else {
            return redirect()->route('admin.roles.all');
        }

    }

    public function store(Request $request, $id)
    {
        $id = $request->id;
        //dd($request);
        $validation_rule = [
            'name'                      => 'required|string|max:50|unique:roles,name,' . $id
        ];

        if($id == 0){
            $roles = new Roles();
            $notify[] = ['success', 'Rol Creado con éxito'];
        }else{
            $roles = Roles::findOrFail($id);
            $notify[] = ['success', 'Rol actualizado con éxito'];
        }

        $request->validate($validation_rule,[
            // 'meta_keywords.array.*'     => 'Todas las palabras clave',
            'input_name.required'      => 'El campo del nombre es obligatorio'
        ]);

        $roles->name             = $request->name;
        $roles->description      = $request->description;
        $roles->save();
        return redirect()->back()->withNotify($notify);
    }

    public function delete($id)
    {
        $Roles = Roles::where('id', $id)->withTrashed()->first();

        if ($Roles->trashed()) {
            $Roles->restore();
            $notify[] = ['success', 'Rol Restaurado Correctamente'];
            return redirect()->back()->withNotify($notify);
        } else {
            $Roles->delete();
            $notify[] = ['success', 'Rol Borrado Correctamente'];
            return redirect()->back()->withNotify($notify);
        }
    }
}
