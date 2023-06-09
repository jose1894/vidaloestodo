<?php

namespace App\Http\Controllers\Admin;

use App\GeneralSetting;
use App\Http\Controllers\Controller;
use App\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $page_title = 'Plantillas SMS';
        $empty_message = 'Sin Plantillas Disponibles';
        $sms_templates = SmsTemplate::get();
        return view('admin.sms_template.index', compact('page_title', 'empty_message', 'sms_templates'));
    }

    public function edit($id)
    {
        $sms_template = SmsTemplate::findOrFail($id);
        $page_title = $sms_template->name;
        return view('admin.sms_template.edit', compact('page_title', 'sms_template'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sms_body' => 'required',
        ]);

        $sms_template = SmsTemplate::findOrFail($id);

        $sms_template->update([
            'sms_body' => $request->sms_body,
            'sms_status' => $request->sms_status ? 1 : 0,
        ]);

        $notify[] = ['success', $sms_template->name . ' Plantilla Actualizada'];
        return back()->withNotify($notify);
    }


    public function smsSetting()
    {
        $page_title = 'API de SMS';

        $general_setting = GeneralSetting::first('sms_api');

        return view('admin.sms_template.sms_setting', compact('page_title', 'general_setting'));
    }

    public function smsSettingUpdate(Request $request)
    {
        $request->validate([
            'sms_api' => 'required',
        ]);
        $general_setting = GeneralSetting::first();
        $general_setting->update([
            'sms_api' => $request->sms_api,
        ]);

        $notify[] = ['success', 'La Plantilla de SMS ha Sido Actualizada'];
        return back()->withNotify($notify);
    }

    public function sendTestSMS(Request $request)
    {
        $request->validate(['mobile' => 'required']);
        $general = GeneralSetting::first(['sn', 'smsapi']);
        if ($general->sn == 1) {
            $message = shortCodeReplacer("{{number}}", $request->mobile, $general->smsapi);
            $message = shortCodeReplacer("{{message}}", 'This is a test sms', $message);
            $result = @file_get_contents($message);
        }

        $notify[] = ['success', 'Deberías recibir un sms de prueba en ' . $request->mobile . ' dentro de poco.'];
        return back()->withNotify($notify);
    }
}
