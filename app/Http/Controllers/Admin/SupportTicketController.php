<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\SupportAttachment;
use App\SupportMessage;
use App\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;

class SupportTicketController extends Controller
{
    public function tickets()
    {
        $page_title = 'Tickets de soporte';
        $empty_message = 'Sin Resultados.';
        $items = SupportTicket::latest()->with('user')->paginate(getPaginate());
        return view('admin.support.tickets', compact('items', 'page_title','empty_message'));
    }

    public function pendingTicket()
    {
        $page_title = 'Tickets Pendientes';
        $empty_message = 'Sin Datos.';
        $items = SupportTicket::whereIn('status', [0,2])->latest()->with('user')->paginate(getPaginate());
        return view('admin.support.tickets', compact('items', 'page_title','empty_message'));
    }

    public function closedTicket()
    {
        $empty_message = 'Sin Resultados.';
        $page_title = 'Tickets Cerrados';
        $items = SupportTicket::whereIn('status', [3])->latest()->with('user')->paginate(getPaginate());
        return view('admin.support.tickets', compact('items', 'page_title','empty_message'));
    }

    public function answeredTicket()
    {
        $page_title = 'Tickets Respondidos';
        $empty_message = 'Sin Resultados.';
        $items = SupportTicket::latest()->with('user')->whereIN('status', [1])->paginate(getPaginate());
        return view('admin.support.tickets', compact('items', 'page_title','empty_message'));
    }


    public function ticketReply($id)
    {
        $ticket = SupportTicket::with('user')->where('id', $id)->firstOrFail();
        $page_title = 'Tickets de soporte';
        $messages = SupportMessage::with('ticket')->where('supportticket_id', $ticket->id)->latest()->get();
        $path = imagePath()['ticket']['path'];
        
        return view('admin.support.reply', compact('ticket', 'messages', 'page_title', 'path'));
    }
    public function ticketReplySend(Request $request, $id)
    {
        $ticket = SupportTicket::with('user')->where('id', $id)->firstOrFail();
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
                                return $fail("Imágenes ¡MÁXIMO 2 MB PERMITIDO!");
                            }
                            if (!in_array($ext, $allowedExts)) {
                                return $fail("Solo se permiten archivos png, jpg, jpeg, pdf, doc, docx");
                            }
                        }
                        if (count($files) > 5) {
                            return $fail("Se pueden cargar un máximo de 5 archivos");
                        }
                    },
                ],
                'message' => 'required',
            ],[
                'message.required' => '¡Es Obligatorio Ingresar un Mensaje!'
            ]);
            $ticket->status = 1;
            $ticket->last_reply = Carbon::now();
            $ticket->save();

            $message->supportticket_id = $ticket->id;
            $message->admin_id = Auth::guard('admin')->id();
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
                        $notify[] = ['error', '¡Ocurrió un Problema al subir el archivo! Inténtelo Nuevamente'];
                        return back()->withNotify($notify)->withInput();
                    }
                }
            }

            notify($ticket, 'ADMIN_SUPPORT_REPLY', [
                'ticket_id' => $ticket->ticket,
                'ticket_subject' => $ticket->subject,
                'reply' => $request->message,
                'link' => route('ticket.view',$ticket->ticket),
            ]);

            $notify[] = ['success', "El ticket de soporte se respondió correctamente"];

        } elseif ($request->replayTicket == 2) {
            $ticket->status = 3;
            $ticket->save();
            $notify[] = ['success', "El ticket de soporte se cerró correctamente"];
        }
        return back()->withNotify($notify);
    }


    public function ticketDownload($ticket_id)
    {
        $attachment = SupportAttachment::findOrFail(decrypt($ticket_id));
        $file = $attachment->image;


        $path = imagePath()['ticket']['path'];

        $full_path = $path.'/' . $file;
        $title = str_slug($attachment->supportMessage->ticket->subject).'-'.$file;
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimetype = mime_content_type($full_path);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($full_path);
    }
    public function ticketDelete(Request $request)
    {
        $message = SupportMessage::findOrFail($request->message_id);
        $path = imagePath()['ticket']['path'];
        if ($message->attachments()->count() > 0) {
            foreach ($message->attachments as $file) {
                @unlink($path.'/'.$file->attachment);
                $file->delete();
            }
        }
        $message->delete();
        $notify[] = ['success', "Borrado Exitosamente"];
        return back()->withNotify($notify);

    }

}
