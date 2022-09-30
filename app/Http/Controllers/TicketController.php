<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SupportAttachment;
use App\SupportMessage;
use App\SupportTicket;
use Auth;
use Carbon\Carbon;

class TicketController extends Controller
{

    public function __construct()
    {
        $this->activeTemplate = activeTemplate();
    }


    // Support Ticket
    public function supportTicket()
    {
        if (Auth::id() == null) {
            abort(404);
        }
        $page_title = "Tickets de Soporte";
        $supports = SupportTicket::where('user_id', Auth::id())->latest()->paginate(15);
        return view($this->activeTemplate . 'user.support.index', compact('supports', 'page_title'));
    }


    public function openSupportTicket()
    {
        if (!Auth::user()) {
            abort(404);
        }
        $page_title = "Nuevo Ticket";
        $user = Auth::user();
        return view($this->activeTemplate . 'user.support.create', compact('page_title', 'user'));
    }

    public function storeSupportTicket(Request $request)
    {
        $ticket = new SupportTicket();
        $message = new SupportMessage();
        $files = $request->file('attachments');
        $allowedExts = array('jpg', 'png', 'jpeg', 'pdf','doc','docx');
        $this->validate($request, [
            'attachments' => [
                'max:4096',
                function ($attribute, $value, $fail) use ($files, $allowedExts) {
                    foreach ($files as $file) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (($file->getSize() / 1000000) > 2) {
                            return $fail("El Tamaño no debe superar los 2MB!");
                        }
                        if (!in_array($ext, $allowedExts)) {
                            return $fail("Solo png, jpg, jpeg, pdf son aceptadas");
                        }
                    }
                    if (count($files) > 5) {
                        return $fail("Se pueden cargar un máximo de 5 imágenes");
                    }
                },
            ],
            'name' => 'required|max:191',
            'email' => 'required|email|max:191',
            'subject' => 'required|max:100',
            'message' => 'required',
        ],[
            'name.required' => '¡Ingrese un Nombre Válido!',
            'name.max' => '¡El Nombre debe contener un Máximo de 191 Carácteres!',
            'email.required' => '¡Ingrese un Correo Electrónico Válido!',
            'email.email' => '¡Ingrese un Correo Electrónico Válido!',
            'email.max' => '¡El Correo debe contener un Máximo de 191 Carácteres!',
            'subject.required' => '¡Debe ser un Sujeto Válido!',
            'subject.max' => '¡El Sujeto debe contener un Máximo de 100 Carácteres!',
            'message.required' => '¡Es Obligatorio Ingresar un Mensaje!'
        ]);
        $ticket->user_id = Auth::id();
        $random = rand(100000, 999999);
        $ticket->ticket = $random;
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->subject = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status = 0;
        $ticket->save();
        $message->supportticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();
        $path = imagePath()['ticket']['path'];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as  $file) {
                try {
                    $attachment = new SupportAttachment();
                    $attachment->support_message_id = $message->id;
                    $attachment->image = uploadFile($file, $path);
                    $attachment->save();
                } catch (\Exception $exp) {
                    $notify[] = ['error', 'No se pudo cargar el archivo'];
                    return back()->withNotify($notify)->withInput();
                }
            }
        }
        $notify[] = ['success', 'Ticket Creado con Éxito!'];
        return redirect()->route('ticket')->withNotify($notify);
    }

    public function viewTicket($ticket)
    {
        $page_title = "Ver Ticket";
        $my_ticket = SupportTicket::where('ticket', $ticket)->latest()->first();
        $messages = SupportMessage::where('supportticket_id', $my_ticket->id)->latest()->get();
        $user = auth()->user();
        return view($this->activeTemplate. 'user.support.view', compact('my_ticket', 'messages', 'page_title', 'user'));

    }

    public function replyTicket(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $message = new SupportMessage();
        $files = $request->file('attachments');
        if ($request->replayTicket == 1) {

            $allowedExts = array('jpg', 'png', 'jpeg', 'pdf','doc','docx');
            $this->validate($request, [
                'attachments' => [
                    'max:4096',
                    function ($attribute, $value, $fail) use ($files, $allowedExts) {
                        foreach ($files as $file) {
                            $ext = strtolower($file->getClientOriginalExtension());
                            if (($file->getSize() / 1000000) > 2) {
                                return $fail("El Tamaño no debe superar los 2MB!");
                            }
                            if (!in_array($ext, $allowedExts)) {
                                return $fail("Solo png, jpg, jpeg, pdf son aceptadas");
                            }
                        }
                        if (count($files) > 5) {
                            return $fail("Se pueden cargar un máximo de 5 imágenes");
                        }
                    },
                ],
                'message' => 'required',
            ],[
                'message.required' => '¡Es Obligatorio Ingresar un Mensaje!'
            ]);

            $ticket->status = 2;
            $ticket->last_reply = Carbon::now();
            $ticket->save();



            $message->supportticket_id = $ticket->id;
            $message->message = $request->message;
            $message->save();

            $path = imagePath()['ticket']['path'];


            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as  $file) {
                    try {
                        $attachment = new SupportAttachment();
                        $attachment->support_message_id = $message->id;
                        $attachment->image = uploadFile($file, $path);
                        $attachment->save();
                    } catch (\Exception $exp) {
                        $notify[] = ['error', 'No se pudo cargar el archivo'];
                        return back()->withNotify($notify)->withInput();
                    }
                }
            }

            $notify[] = ['success', '¡Ticket de soporte Respondido con éxito!'];
        } elseif ($request->replayTicket == 2) {
            $ticket->status = 3;
            $ticket->last_reply = Carbon::now();
            $ticket->save();
            $notify[] = ['success', '¡Ticket de soporte Cerrado con éxito!'];
        }
        return back()->withNotify($notify);

    }





    public function ticketDownload($ticket_id)
    {
        $attachment = SupportAttachment::findOrFail(decrypt($ticket_id));
        $file = $attachment->image;

        $path = imagePath()['ticket']['path'];
        $full_path = $path.'/'. $file;

        $title = str_slug($attachment->supportMessage->ticket->subject);
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimetype = mime_content_type($full_path);


        header('Content-Disposition: attachment; filename="' . $title . '.' . $ext . '";');
        header("Content-Type: " . $mimetype);
        return readfile($full_path);
    }

}
