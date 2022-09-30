<?php

namespace App\Http\Controllers\Admin;

use App\EmailTemplate;
use App\GeneralSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $page_title = 'Plantillas de Correo Electrónico';
        $empty_message = 'Sin Plantillas Disponibles';
        $email_templates = EmailTemplate::get();
        return view('admin.email_template.index', compact('page_title', 'empty_message', 'email_templates'));
    }

    public function edit($id)
    {
        $email_template = EmailTemplate::findOrFail($id);
        $page_title = $email_template->name;
        return view('admin.email_template.edit', compact('page_title', 'email_template'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required',
            'email_body' => 'required',
        ]);
        $email_template = EmailTemplate::findOrFail($id);
        $email_template->update([
            'subj' => $request->subject,
            'email_body' => $request->email_body,
            'email_status' => $request->email_status ? 1 : 0,
        ]);

        $notify[] = ['success', $email_template->name . ' template has been updated.'];
        return back()->withNotify($notify);
    }


    public function emailSetting()
    {
        $page_title = 'Configuración de Correo Electrónico';
        $general_setting = GeneralSetting::first(['mail_config']);
        return view('admin.email_template.email_setting', compact('page_title', 'general_setting'));
    }

    public function emailSettingUpdate(Request $request)
    {
        $general_setting = GeneralSetting::first();
        $request->validate([
            'email_method' => 'required|in:php,smtp,sendgrid,mailjet',
            'host' => 'required_if:email_method,smtp',
            'port' => 'required_if:email_method,smtp',
            'username' => 'required_if:email_method,smtp',
            'password' => 'required_if:email_method,smtp',
            'appkey' => 'required_if:email_method,sendgrid',
            'public_key' => 'required_if:email_method,mailjet',
            'secret_key' => 'required_if:email_method,mailjet',
        ], [
            'host.required_if' => ':attribute is required for SMTP configuration',
            'port.required_if' => ':attribute is required for SMTP configuration',
            'username.required_if' => ':attribute is required for SMTP configuration',
            'password.required_if' => ':attribute is required for SMTP configuration',

            'appkey.required_if' => ':attribute is required for SendGrid configuration',

            'public_key.required_if' => ':attribute is required for Mailjet configuration',
            'secret_key.required_if' => ':attribute is required for Mailjet configuration',
        ]);

        if ($request->email_method == 'php') {
            $data['name'] = 'php';
        } else if ($request->email_method == 'smtp') {
            $request->merge(['name' => 'smtp']);
            $data = $request->only('name', 'host', 'port', 'enc', 'username', 'password', 'driver');

        } else if ($request->email_method == 'sendgrid') {
            $request->merge(['name' => 'sendgrid']);
            $data = $request->only('name', 'appkey');
        } else if ($request->email_method == 'mailjet') {
            $request->merge(['name' => 'mailjet']);
            $data = $request->only('name', 'public_key', 'secret_key');
        }


        $general_setting->update(['mail_config' => $data]);
        $notify[] = ['success', 'La Configuración del Correo ha sido Actualizada!.'];
        return back()->withNotify($notify);
    }


    public function emailTemplate()
    {
        $page_title = 'Plantilla de Correo Electrónico Global';
        $general_setting = GeneralSetting::first(['email_from', 'email_template']);
        return view('admin.email_template.email_template', compact('page_title', 'general_setting'));
    }

    public function emailTemplateUpdate(Request $request)
    {
        $request->validate([
            'email_from' => 'required|email',
        ]);

        $general_setting = GeneralSetting::first();
        $general_setting->update([
            'email_from' => $request->email_from,
            'email_template' => $request->email_template,
        ]);

        $notify[] = ['success', 'La Plantilla de Correo Electrónico Global Ha sido Actualizada.'];
        return back()->withNotify($notify);
    }

    public function sendTestMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $general = GeneralSetting::first();
        $config = $general->mail_config;
        $receiver_name = explode('@', $request->email)[0];
        $subject = 'Probando ' . strtoupper($config->name) . ' Mail';
        $message = 'Este es un correo electrónico de prueba, ignórelo si no está destinado a recibir este correo electrónico.';

        try {
            send_general_email($request->email, $subject, $message, $receiver_name);
        } catch (\Exception $exp) {
            $notify[] = ['error', 'Credencial inválida'];
            return back()->withNotify($notify);
        }

        $notify[] = ['success', 'Debería recibir un correo de prueba en ' . $request->email . ' dentro de poco.'];
        return back()->withNotify($notify);
    }
}
