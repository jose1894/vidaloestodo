<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\GeneralSetting;

class GeneralSettingsController extends Controller
{
    //
    public function scopeSitename(Request $request) {
        $generalSettings = new GeneralSetting();
        dd($request);
        return response()->json($generalSettings->scopeSitename('', $request->title));
    }
}
