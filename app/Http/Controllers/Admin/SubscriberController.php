<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index()
    {
        $page_title = 'Todos los Suscriptores';
        $empty_message = 'Sin suscriptores.';
        $subscribers = Subscriber::latest()->paginate(getPaginate());
        return view('admin.subscriber.index', compact('page_title', 'empty_message', 'subscribers'));
    }

    public function sendEmailForm()
    {
        $page_title = 'Enviar Correo al Suscriptor';
        return view('admin.subscriber.send_email', compact('page_title'));
    }

    public function remove(Request $request)
    {
        $request->validate(['subscriber' => 'required|integer']);
        $subscriber = Subscriber::findOrFail($request->subscriber);
        $subscriber->delete();

        $notify[] = ['success', 'El Suscriptor ha sido removido!'];
        return back()->withNotify($notify);
    }

    public function sendEmail(Request $request, $id=null)
    {

        $request->validate([
            'subject' => 'required',
            'body' => 'required',
        ]);

        if (!Subscriber::first()) return back()->withErrors(['No Hay Suscriptores para Enviar Correos.']);
        if($id){
            $subscriber = Subscriber::findOrFail($id);
            $receiver_name = explode('@', $subscriber->email)[0];
            send_general_email($subscriber->email, $request->subject, $request->body, $receiver_name);
            $notify[] = ['success', 'Correo Enviado al Suscriptor.'];
            return back()->withNotify($notify);
        }
        $subscribers = Subscriber::all();

        foreach ($subscribers as $subscriber) {
            $receiver_name = explode('@', $subscriber->email)[0];
            send_general_email($subscriber->email, $request->subject, $request->body, $receiver_name);
        }

        $notify[] = ['success', 'El Correo ha Sido Enviado a Todos los Subscriptores.'];
        return back()->withNotify($notify);
    }
}
