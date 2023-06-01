<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;

class CategoryController extends Controller
{
    public function index()
    {

        $categories     = Category::with(['allSubcategories'])->where('parent_id', null)->get();
        return response()->json($categories);
    }

    public function categories_filter_menu()
    {
        $categories = Category::has('specialProuducts')
            ->with(['allSubcategories'])
            ->where('parent_id', null)
            ->where('in_filter_menu',  '1')
            ->orderByRaw(
                "case when position is null then 1 else 0 end, position"
            )->get();


        return response()->json($categories);
    }
}
