<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\UserLogin;
use Illuminate\Http\Request;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
//    protected $redirectTo = RouteServiceProvider::HOME;
    // protected $redirectTo = RouteServiceProvider::CHECKOUT;
    protected $redirectTo = 'user/checkout';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout','logoutGet');
    }

    public function showLoginForm()
    {
        $page_title = "Iniciar sesión";
        return view(activeTemplate() . 'user.auth.login', compact('page_title'));
    }

    public function login(Request $request)
    {

       // dd($request->all());


        $this->validateLogin($request);

        if(isset($request->captcha)){
            if(!captchaVerify($request->captcha, $request->captcha_secret)){
                $notify[] = ['error',"Captcha Inválido"];
                return back()->withNotify($notify)->withInput();
            }
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
            //return "a";
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
           //return "b";
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function username()
    {
        return 'username';
    }

    protected function validateLogin(Request $request)
    {
        $customRecaptcha = \App\Plugin::where('act', 'custom-captcha')->where('status', 1)->first();
        $validation_rule = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];

        if ($customRecaptcha) {
            $validation_rule['captcha'] = 'required';
        }

        $request->validate($validation_rule);

    }


    public function logout(Request $request)
    {
        return $this->logoutGet();
    }


    public function logoutGet()
    {
        $this->guard()->logout();

        request()->session()->invalidate();

        $notify[] = ['success', 'Usted ha sido desconectado.'];
        return redirect()->route('user.login')->withNotify($notify);
    }





    public function authenticated(Request $request, $user)
    {
        if ($user->status == 0) {
            $this->guard()->logout();
            return redirect()->route('user.login')->withErrors(['Your account has been deactivated.']);
        }


        // dd($request->all());
        $user = auth()->user();
        $user->save();


        $info = json_decode(json_encode( array_merge(getIpInfo(), osBrowser())), true);
        $userLogin = new UserLogin();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip =  request()->ip();
        $userLogin->longitude =  @implode(',',$info['long']);
        $userLogin->latitude =  @implode(',',$info['lat']);
        $userLogin->location =  @implode(',',$info['city']) . (" - ". @implode(',',$info['area']) ."- ") . @implode(',',$info['country']) . (" - ". @implode(',',$info['code']) . " ");
        $userLogin->country_code = @implode(',',$info['code']);
        $userLogin->browser = @$info['browser'];
        $userLogin->os = @$info['os_platform'];
        $userLogin->country =  @implode(',', $info['country']);
        $userLogin->save();

        //Check Cart
        insertUserToCart(auth()->user()->id, session('session_id'));

        //return redirect()->route('user.checkout');
        return redirect()->route('home');
    }


}
