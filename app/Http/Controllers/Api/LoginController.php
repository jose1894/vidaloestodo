<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\UserLogin;
use App\User;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $token = Auth::user()->createToken(Auth::id())->plainTextToken;

            $request->session()->regenerate();

            //return response()->json(['message' => 'Autenticación exitosa']);
            $user = Auth::user();

            $info = json_decode(json_encode(array_merge(getIpInfo(), osBrowser())), true);
            $userLogin = new UserLogin();
            $userLogin->user_id = $user->id;
            $userLogin->user_ip =  request()->ip();
            $userLogin->longitude =  @implode(',', $info['long']);
            $userLogin->latitude =  @implode(',', $info['lat']);
            $userLogin->location =  @implode(',', $info['city']) . (" - " . @implode(',', $info['area']) . "- ") . @implode(',', $info['country']) . (" - " . @implode(',', $info['code']) . " ");
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->browser = @$info['browser'];
            $userLogin->os = @$info['os_platform'];
            $userLogin->country =  @implode(',', $info['country']);
            $userLogin->save();

            //Check Cart
            insertUserToCart($user->id, $request['session']);

            return response()->json([
                'success' => true, 
                'user' => $user, 
                'token' => $token,
                'session_id' => $request['session'],
                'info' => $info
            ]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        /*Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada exitosamente']);*/

        try {

            $user = User::findOrFail($request->input('id'));

            $user->tokens()->delete();

            return response()->json('User logged out!', 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Something went wrong in AuthController.logout'
            ]);
        }


    }
}
