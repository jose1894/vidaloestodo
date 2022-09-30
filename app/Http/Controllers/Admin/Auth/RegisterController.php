<?php

namespace App\Http\Controllers\Admin\Auth;

use App\GeneralSetting;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use App\UserLogin;
use App\Admin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('regStatus')->except('registrationNotAllowed');

        $this->activeTemplate = activeTemplate();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validate = Validator::make($data, [
            'firstname' => 'sometimes|required|string|max:60',
            'lastname' => 'sometimes|required|string|max:60',
            'email' => 'required|string|email:filter|max:160|unique:users', //email:filter valida el domino del email tiene q terminar en .algo
            'mobile' => 'string|max:30|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'username' => 'required|alpha_num|unique:users|min:6',
            'captcha' => 'sometimes|required',
            'country_code' => 'string'
        ]);

        return $validate;
    }

    public function registerAdmin(Request $request, $id){

        $data = $request->all();  

        if($data['role_id'] == 1){ //si es cliente
            //User Create
            if($id == 0){   
                $this->validator($request->all())->validate();  
                $data = $request->all();     
                $user = new User();
            }else{
                $user = User::findOrFail($id);
                $data = $request->all();
            }

            $gnl = GeneralSetting::first();

            isset($data['password']) ? $user->password = Hash::make($data['password']) : '';
            $user->firstname = isset($data['firstname']) ? $data['firstname'] : null;
            $user->lastname = isset($data['lastname']) ? $data['lastname'] : null;
            $user->email = strtolower(trim($data['email']));
            $user->username = trim($data['username']);
            $user->mobile = isset($data['mobile']) ? $data['country_code'].$data['mobile'] : '';
            $user->address = [
                'address' => '',
                'state' => '',
                'zip' => '',
                'country' => isset($data['country']) ? $data['country'] : null,
                'city' => ''
            ];
            $user->status = 1;
            $user->ev = $gnl->ev ? 0 : 1;
            $user->sv = $gnl->sv ? 0 : 1;
            $user->role_id = $data['role_id'];

            if($id == 0){   
                $user->save();

                // $short_code = [
                //     'site_name' => $gnl->sitename,
                //     'user_name'  => $user->fullName
                // ];
        
                // notify($user, 'WELCOME_MESSAGE', $short_code);
        
                // //Login Log Create
                // $ip = $_SERVER["REMOTE_ADDR"];
                // $exist = UserLogin::where('user_ip',$ip)->first();
                // $userLogin = new UserLogin();
        
                // //Check exist or not
                // if ($exist) {
                //     $userLogin->longitude =  $exist->longitude;
                //     $userLogin->latitude =  $exist->latitude;
                //     $userLogin->location =  $exist->location;
                //     $userLogin->country_code = $exist->country_code;
                //     $userLogin->country =  $exist->country;
                // }else{
                //     $info = json_decode(json_encode(getIpInfo()), true);
                //     $userLogin->longitude =  @implode(',',$info['long']);
                //     $userLogin->latitude =  @implode(',',$info['lat']);
                //     $userLogin->location =  @implode(',',$info['city']) . (" - ". @implode(',',$info['area']) ."- ") . @implode(',',$info['country']) . (" - ". @implode(',',$info['code']) . " ");
                //     $userLogin->country_code = @implode(',',$info['code']);
                //     $userLogin->country =  @implode(',', $info['country']);
                // }
        
                // $userAgent = osBrowser();
                // $userLogin->user_id = $user->id;
                // $userLogin->user_ip =  $ip;
        
                // $userLogin->browser = @$userAgent['browser'];
                // $userLogin->os = @$userAgent['os_platform'];
                // $userLogin->save();
        
                // $this->guard()->login($user);
            }else{
                $user->update();
            }
        }
        else if($data['role_id'] == 2){ //Moderador
            if($id == 0){
                $moderador = new Admin();
            }
            else{
                $moderador = Admin::findOrFail($id);
            }

            $moderador->name = $data['firstname'] .' '. $data['lastname'] ;
            $moderador->email = strtolower(trim($data['email']));
            $moderador->username = trim($data['username']);
            $moderador->role_id = '2'; //role moderador
            isset($data['password']) ? $moderador->password = Hash::make($data['password']) : '';

            if($id == 0){
                $moderador->save();
            }
            else{
                $moderador->update();
            }
        }   
        
        
        return redirect()->route('admin.users.all');

        // return $this->registered($request, $user)
        //     ?: redirect($this->redirectPath());

    }

    public function registered()
    {
        return redirect()->route('admin.users.all');
    }


}
