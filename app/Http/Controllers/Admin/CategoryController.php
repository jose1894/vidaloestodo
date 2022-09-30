<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Http\Controllers\Controller;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $page_title     = "Todas Las Categorías";
        $empty_message  = "Sin Categorías";
        $categories     = Category::with(['allSubcategories'])->where('parent_id', null)->get();
        //dd($categories);
        return view('admin.categories.index', compact('page_title', 'empty_message', 'categories'));
    }

    public function trashed()
    {
        $page_title     = "Categorías en la papelera";
        $empty_message  = "Sin categoría todavía";


        $categories     = Category::onlyTrashed()->with(['allSubcategories'])->paginate(getPaginate());

        return view('admin.categories.trashed', compact('page_title', 'empty_message', 'categories'));
    }

    public function categoryTrashedSearch(Request $request)
    {
        if ($request->search != null) {
            $empty_message  = 'No se encontró ninguna categoría';
            $search         = trim(strtolower($request->search));
            $categories       = Category::onlyTrashed()->where('name', 'like', "%$search%")
            ->with(['subcategories'])
            ->orderByDesc('id')
            ->paginate(getPaginate());
            $page_title     = 'Búsqueda de categoría en la papelera - ' . $search;
            return view('admin.categories.trashed', compact('page_title', 'empty_message', 'categories'));
        } else {
            return redirect()->route('admin.categories.trashed');
        }
    }

    public function store(Request $request, $id)
    {
        // $request->validate([
        //     'parent_id'                 => 'nullable|integer|gt:0',
        //     'name'                      => 'required|string|max:50',
        //     'icon'                      => 'required|string|max:100',
        //     'meta_title'                => 'nullable|string|max:191',
        //     'meta_description'          => 'nullable|string|max:191',
        //     'meta_keywords'             => 'nullable|array',
        //     'meta_keywords.array.*'     => 'nullable|string',
        //     'top_category'              => 'nullable|integer|between:0,1',
        //     'special_category'          => 'nullable|integer|between:0,1',
        //     'filter_menu'               => 'nullable|integer|between:0,1'
        // ]);

        $validation_rule = [
            'parent_id'                 => 'nullable|integer|gt:0',
            'name'                      => 'required|string|max:50',
            'icon'                      => 'required|string|max:100',
            'meta_title'                => 'nullable|string|max:191',
            'meta_description'          => 'nullable|string|max:191',
            'meta_keywords'             => 'nullable|array',
            'meta_keywords.array.*'     => 'nullable|string',
            'top_category'              => 'nullable|integer|between:0,1',
            'special_category'          => 'nullable|integer|between:0,1',
            'filter_menu'               => 'nullable|integer|between:0,1'
        ];

        $request->validate($validation_rule,[
            'name.required'     => 'Introduzca un Nombre Válido',
            'icon.required'      => 'El campo del logotipo es obligatorio'
        ]);


        if($id ==0){
            $category = Category::where('name', $request->name)->where('parent_id', $request->parent_id)->first();

            if($category){
                return response()->json(['status'=>'error', 'message'=>'The Name Has Already Been Taken']);
            }

            $category = new Category();
            $validation_rule['image_input']  = ['required', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])];

        }else{
            $category = Category::where('name', $request->name)->where('parent_id', $request->parent_id)->where('id', '!=', $id)->first();

            if($category){
                return response()->json(['status'=>'error', 'message'=>'El nombre ya ha está en uso']);
            }

            $category = Category::findOrFail($id);

            $validation_rule['image_input']  = ['nullable', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])];
        }
        $validator = Validator::make($request->all(), $validation_rule,[
            'meta_keywords.array.*' => 'Todas las palabras clave',
            'image_input.required'   => 'El campo de la Imagen es obligatorio'
        ]);


        if($validator->fails()) {
            return response()->json(['status'=>'error', 'message'=>$validator->errors()]);
        }

        if ($request->hasFile('image_input')) {
            try {

                $request->merge(['image' => $this->store_image($request->image_input, $category->image)]);

            } catch (\Exception $exp) {
                $notify = ['error', '¡No se pudo subir la Imagen!'];
                return response()->json(['status'=>'error', 'message'=>'No se pudo cargar la imagen.']);
            }
        }else{
            $request->merge(['image'=>$category->image]);
        }

        $category->name             = $request->name;
        $category->parent_id        = $request->parent_id;
        $category->icon             = $request->icon;
        $category->meta_title       = $request->meta_title;
        $category->meta_description = $request->meta_description;
        $category->meta_keywords    = $request->meta_keywords;
        $category->image            = $request->image;
        $category->is_top           = $request->top_category??0;
        $category->is_special       = $request->special_category??0;
        $category->in_filter_menu   = $request->filter_menu??0;
        $category->position   = $request->position??0;
        $category->save();

        if($id ==0){
            $message = 'Categoría Agregada con éxito';
            $reload  = true;
        }else{
            $message = 'Categoría Actualizada correctamente';
            $reload  = false;
        }
        return response()->json(['status'=>'success', 'message'=>$message, 'reload' => $reload]);
    }

    public function delete($id)
    {
        $category = Category::where('id', $id)->withTrashed()->first();

        if ($category->trashed()){
            $category->restore();
            $notify[] = ['success', 'Categoría Restaurada con éxito'];
            return redirect()->back()->withNotify($notify);
        }else{
            $category->delete();
            $notify[] = ['success', 'Categoría Eliminada correctamente'];
            return redirect()->back()->withNotify($notify);
        }
    }

    protected function store_image($image, $old_image = null)
    {
        $path = imagePath()['category']['path'];
        $size = imagePath()['category']['size'];
        return uploadImage($image, $path, $size, $old_image);
    }

}
