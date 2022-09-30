<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Admin;
use App\AdminPasswordReset;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.guest');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        $page_title = 'Recuperación de Cuenta';
        return view('admin.auth.passwords.email', compact('page_title'));
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('admins');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);



        $user = Admin::where('email', $request->email)->first();
        if ($user == null) {
            return back()->withErrors(['Correo no disponible']);
        }

        $code = verificationCode(6);

        AdminPasswordReset::create([
            'email' => $user->email,
            'token' => $code,
            'status' => 0,
            'created_at' => date("Y-m-d h:i:s")
        ]);

        $userAgent =  array_merge(getIpInfo(), osBrowser());
        send_email($user, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => $userAgent['os_platform'],
            'browser' => $userAgent['browser'],
            'ip' => $userAgent['ip'],
            'time' => $userAgent['time']
        ]);

        $page_title = 'Recuperación de Cuenta';
        $notify[] = ['success', 'El reestablecimiento de la contraseña fué enviado al correo de forma exitosa'];
        return view('admin.auth.passwords.code_verify', compact('page_title', 'notify'));
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code.*' => 'required']);
        $notify[] = ['success', 'Puede Cambiar su Contraseña.'];

        $code =  str_replace(',','',implode(',',$request->code));

        return redirect()->route('admin.password.change-link', $code)->withNotify($notify);
    }
}
