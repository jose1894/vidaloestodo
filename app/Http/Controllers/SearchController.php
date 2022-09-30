<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SearchRequest;

class SearchController extends Controller
{
    //
    public function search(SearchRequest $request){

        return response()->json(['shipping' => 'searchs']);
    }
}
