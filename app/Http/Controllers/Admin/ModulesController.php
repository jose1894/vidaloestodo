<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules;
use App\Roles;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    public function index(){
        $page_title     = "Todos los Módulos";
        $empty_message  = "Sin Registros";
        $modules = Modules::with('roles')
            ->orderBy('id', 'asc')
            ->paginate(getPaginate());

        $roles = Roles::where('name','!=','Superadmin')->get();

        return view('admin.modules.index', compact('page_title', 'empty_message','modules', 'roles'));
    }

    public function trashed()
    {
        $page_title     = "Módulos Eliminados";
        $empty_message  = "Sin Resultados";
        $modules         = Modules::with('roles')
            ->onlyTrashed()
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());
        return view('admin.modules.index', compact('page_title', 'empty_message', 'modules'));
    }


    public function moduleSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Módulo No Encontrado';
            $search         = trim(strtolower($request->search));

            $modules         = Modules::with('roles')
                ->where('name', 'like', "%$search%")
                ->orderByDesc('id', 'desc')
                ->paginate(getPaginate());

            $page_title     = 'Buscar Módulo - ' . $search;

            $roles = Roles::where('name','!=','Superadmin')->get();

            return view('admin.modules.index', compact('page_title', 'empty_message', 'modules', 'roles'));
        } else {
            return redirect()->route('admin.modules.index');
        }

    }

    public function moduleTrashedSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'Módulo No Encontrado';
            $search         = trim(strtolower($request->search));
            $modules         = Brand::with('products')
            ->onlyTrashed()
            ->orderByDesc('id')
            ->where('name', 'like', "%$search%")->paginate(getPaginate());

            $page_title     = 'Buscar Módulo Borrado - ' . $search;
            return view('admin.modules.index', compact('page_title', 'empty_message', 'modules'));
        } else {
            return redirect()->route('admin.modules.index');
        }

    }

    public function store(Request $request, $id)
    {
        $validation_rule = [
            'name'                      => 'required|string|max:50',
            'path'                      => 'string|max:50',
            'role_id'                   => 'string',
        ];

        if($id ==0){
            $module = new Modules();
            $module->roles()->attach($request->roles_id);
            $notify[] = ['success', 'Módulo creado con éxito'];
        }else{
            $module = Modules::findOrFail($id);
            $module->roles()->sync($request->roles_id);
            $notify[] = ['success', 'Módulo actualizado con éxito'];
        }

        $module->name             = $request->name;
        $module->path       = $request->path;
        $module->status = '1';
        //$module->role_id = $request->role_id;
        
        
        $module->save();

        return redirect()->back()->withNotify($notify);
    }

    public function delete($id)
    {
        $module = Modules::findOrFail($id);
        $notify = [];
        if($module->status == '1'){
            $module->status = '0';
            $notify[] = ['success', 'Módulo inhabilitado con éxito'];
        }
        else if($module->status == '0'){
            $module->status = '1';
            $notify[] = ['success', 'Módulo activado con éxito'];
        }
        $module->save();

        return redirect()->back()->withNotify($notify);
    }

    public function setTop(Request $request)
    {
        $brand = Brand::findOrFail($request->id);
        if ($brand) {
            if ($brand->top == 1) {
                $brand->top = 0;
                $msg = 'Excluido de las mejores marcas';
            } else {
                $brand->top = 1;
                $msg = 'Incluido en las mejores marcas';
            }
            $brand->save();
            return response()->json(['success' => $msg]);
        }
    }
}
