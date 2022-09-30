<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\PasswordReset;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }


    public function showLinkRequestForm()
    {
        //$page_title = "Forgot Password";
        $page_title = "Recuperar Contraseña";
        return view(activeTemplate() . 'user.auth.passwords.email', compact('page_title'));
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $notify[] = ['error', 'Usuario no encontrado.'];
            return back()->withNotify($notify);
        }

        PasswordReset::where('email', $user->email)->delete();
        $code = verificationCode(6);
        PasswordReset::create([
            'email' => $user->email,
            'token' => $code,
            'created_at' => \Carbon\Carbon::now(),
        ]);

        $userAgent =  array_merge(getIpInfo(), osBrowser());
        send_email($user, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => @$userAgent['os_platform'],
            'browser' => @$userAgent['browser'],
            'ip' => @$userAgent['ip'],
            'time' => @$userAgent['time']
        ]);

        $page_title = 'Recuperación de Cuenta';
        $email = $user->email;
        session()->put('pass_res_mail',$email);
        $notify[] = ['success', 'El reestablecimiento de la contraseña fué enviado al correo de forma exitosa'];
        return redirect()->route('user.password.code_verify')->withNotify($notify);
    }

    public function codeVerify(){
        $page_title = 'Recuperación de Cuenta';
        $email = session()->get('pass_res_mail');
        if (!$email) {
            $notify[] = ['error','Opps! session expired'];
            return redirect()->route('user.password.request')->withNotify($notify);
        }
        return view(activeTemplate().'user.auth.passwords.code_verify',compact('page_title','email'));
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|numeric', 'email' => 'required']);

        if (PasswordReset::where('token', $request->code)->where('email', $request->email)->count() != 1) {
            $notify[] = ['error', 'Token Inválido'];
            return redirect()->route('user.password.request')->withNotify($notify);
        }
        $notify[] = ['success', 'Usted puede cambiar su contraseña.'];
        session()->flash('fpass_email', $request->email);
        return redirect()->route('user.password.reset', $request->code)->withNotify($notify);
    }

}
