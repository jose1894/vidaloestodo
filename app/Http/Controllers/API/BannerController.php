<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Frontend;

class BannerController extends Controller
{
    public function  allBanner(){
      $middles =  $this->banners_middle("banners_middle.element");
      $sliders =  $this->banners_sliders("sliders.element");

      return response()->json([
        "middles" => $middles,
        "sliders" => $sliders
    ]);
    }
    public function banners_middle($keys)
    {
        $singleQuery = false;
        $limit = null;
        $article = \App\Frontend::query();
        $article->when($limit != null, function ($q) use ($limit) {
            return $q->limit($limit);
        });
        $contents = $article->where('data_keys', $keys)->latest()->get();
        
        $arrayContents = [];
        foreach ($contents as $content) {
            $content['urlimage'] = getImage('assets/images/frontend/banners_middle/' . $content->data_values->image, '551x151');
            $arrayContents[] = $content;
        }

        //return response()->json($arrayContents);
        return $arrayContents;
    }
    public function banners_sliders($keys)
    {
        $singleQuery = false;
        $limit = null;
        $article = \App\Frontend::query();
        $article->when($limit != null, function ($q) use ($limit) {
            return $q->limit($limit);
        });
        $contents = $article->where('data_keys', $keys)->latest()->get();
        
        $arrayContents = [];
        foreach ($contents as $content) {
            $content['urlimage'] = getImage('assets/images/frontend/sliders/' . @$content->data_values->slider, '1220x350');
            $arrayContents[] = $content;
        }

        //return response()->json($arrayContents);
        return $arrayContents;
    }
}
