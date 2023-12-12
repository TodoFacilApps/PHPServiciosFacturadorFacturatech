<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Mail; //Importante incluir la clase Mail, que será la encargada del envío

use App\Mail\DemoEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\MiCorreo;
use App\Mail\ContactoMailable;

class EmailController extends Controller
{

    public function __construct()
    {
    }


    function index()
    {
     return view('form');
    }

    //envio de mensaje desde formulario
    public function send(Request $request)
    {
      $this->validate($request, [
          'name'     =>  'required',
          'email'  =>  'required|email',
          'message' =>  'required'
         ]);

       $data = array(
            'name'      =>  $request->input('name'),
            'message'   =>   $request->input('message')
        );

        $email = $request->input('email');
      Mail::to($email)->send(new MiCorreo($data));
      return back()->with('success', 'Enviado exitosamente!');
    }


    function mensaje(Request $request) {
        $this->validate($request, [
            'name'     =>  'required',
            'email'  =>  'required|email',
            'message' =>  'required'
        ]);

        $mensaje = "Saludos, " . $request->input('name') . "
        Utilice este código para restablecer la contraseña de la cuenta de: " . $request->input('email') . "

        Aquí está tu código: 1234123
        Otra variable: " . $request->input('message');

        $response = Mail::raw($mensaje, function ($message) use ($request) {
            $message->to($request->input('email'))
                    ->subject('Asunto del correo');
        });
    }

    function mensajeStatico() {
        $mensaje = "Saludos, Aurora
        Utilice este código para restablecer la contraseña de la cuenta de: soraideaurora@gmail.com

        Aquí está tu código: 1234123
        Otra variable: variable adicional";
        $response = Mail::raw($mensaje, function ($message) {
            $message->to("soraideaurora@gmail.com")
                    ->subject('Asunto del correo');
        });
    }

    protected function sendResetLinkResponse($response)
    {
        if (request()->header('Content-Type') == 'application/json') {
            return response()->json(['success' => 'Recovery email sent.']);
        }
        return back()->with('status', trans($response));
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        if (request()->header('Content-Type') == 'application/json') {
            return response()->json(['error' => 'Oops something went wrong.']);
        }
        return back()->withErrors(
            ['email' => trans($response)]
        );
    }



}
