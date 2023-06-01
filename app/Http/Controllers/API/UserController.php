<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function profile()
    {   
        return response()->json(['user' => Auth::user()]);
    }

    public function submitProfile(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'address' => "sometimes|required|max:80",
            'state' => 'sometimes|required|max:80',
            'zip' => 'sometimes|required|max:40',
            'city' => 'sometimes|required|max:50',
            // 'image' => 'mimes:png,jpg,jpeg',
            'email' => 'string|email:filter|max:160',
        ],[
            'firstname.required'=>'Debe Introducir un Nombre Válido',
            'lastname.required'=>'Debe Introducir un Apellido Válido'
        ]);


        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;
        $in['type_dni'] = $request->type_dni;
        $in['dni'] = $request->dni;

        if($request->email){
            $in['email'] = $request->email;
        }
        

        $in['address'] = [
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country,
            'city' => $request->city,
        ];


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $user->username . '.jpg';
            $location = 'assets/images/user/profile/' . $filename;
            $in['image'] = $filename;

            $path = './assets/images/user/profile/';
            $link = $path . $user->image;
            if (!file_exists($link)) {            
                mkdir($path, 777, true);
            }
            else{
                @unlink($link);
            }
            $size = '500x500';
            $image = Image::make($image);
            $size = explode('x', strtolower($size));
            $image->resize($size[0], $size[1]);
            $image->save($location);
        }

        $user->fill($in)->save();
        $user->direction = $request->address;
        $user->save();
        return response()->json(['success' => true, 'message' => 'Perfil Actualizado con Éxito.'], 200);
    }
}
