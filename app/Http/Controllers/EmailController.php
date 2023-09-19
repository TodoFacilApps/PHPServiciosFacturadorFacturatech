<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Mail; //Importante incluir la clase Mail, que será la encargada del envío

use App\Mail\DemoEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\MiCorreo;

class EmailController extends Controller
{


    public function send()
    {
        $objDemo = new \stdClass();
        $objDemo->demo_one = 'Demo One Value';
        $objDemo->demo_two = 'Demo Two Value';
        $objDemo->sender = 'SenderUserName';
        $objDemo->receiver = 'ReceiverUserName';
        Mail::to("receiver@example.com")->send(new DemoEmail($objDemo));
    }

    public function contact(Request $request){
        $subject = "Asunto del correo";
        $for = "soraideaurora@gmail.com";

        Mail::send('email',$request->all(), function($msj) use($subject,$for){
            $msj->from("jakeli1997.jcs@gmail.com","Prueva de Facturador");
            $msj->subject($subject);
            $msj->to($for);
        });
        return 'mensaje enviado ';
    }

    public function prueva(){
        $nombreUsuario = 'Juan'; // Nombre del usuario
        $mensajePersonalizado = '¡Bienvenido a nuestro sitio web! Esperamos que disfrutes de tu experiencia.';

        $mail = new MiCorreo($nombreUsuario, $mensajePersonalizado);
        Mail::to('leonardo.ayala@pagofacil.com.bo')->send($mail);
//        return response()->json($mail);
    }
}
