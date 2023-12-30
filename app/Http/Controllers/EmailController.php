<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Mail; //Importante incluir la clase Mail, que será la encargada del envío

use App\Mail\DemoEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\MiCorreo;
use App\Mail\ContactoMailable;
use App\Mail\SMSAdjunto;

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


    
    function mensajeAdjunto() {
        
        $mensaje = [
            'name' => 'Auro',
            'email' => 'jakeli1997.jcs@gmail.com',
            'subjet' => 'mensaje Adjunto',
            'content' => 'este contenido es de prueba',
            'archivoPDF' => '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p8IxuqBd8KqmxKBms29b51KzVtX9/toqfibRyGYmxJqdYuR3a1Z4TpY/P6C9IcnDZTa1NfvZR3Dg9xRW9FMBVU8Xx8B1xJWYjT+7I4g1O8VIfta8WLNzeGan8MxO4ZmdwjM7hWd2Cs/sFJ7ZKTyzU3hmp/DMTuGZneIn6cemwQplbmRzdHJlYW0KZW5kb2JqCjEzIDAgb2JqCjw8IC9UeXBlIC9FeHRHU3RhdGUKL0JNIC9Ob3JtYWwKL2NhIDAuNQo+PgplbmRvYmoKMTQgMCBvYmoKPDwgL1R5cGUgL0V4dEdTdGF0ZQovQk0gL05vcm1hbAovY2EgMQo+PgplbmRvYmoKeHJlZgowIDE1CjAwMDAwMDAwMDAgNjU1MzUgZiAKMDAwMDAwMDAwOSAwMDAwMCBuIAowMDAwMDAwMDc0IDAwMDAwIG4gCjAwMDAwMDAxMjAgMDAwMDAgbiAKMDAwMDAwMDM3NSAwMDAwMCBuIAowMDAwMDAwNDEyIDAwMDAwIG4gCjAwMDAwMDA1OTEgMDAwMDAgbiAKMDAwMDAwMDY5NCAwMDAwMCBuIAowMDAwMDAyNTMzIDAwMDAwIG4gCjAwMDAwMDI2NDIgMDAwMDAgbiAKMDAwMDAwMjc1MCAwMDAwMCBuIAowMDAwMDAyODU4IDAwMDAwIG4gCjAwMDAwMDI5NzEgMDAwMDAgbiAKMDAwMDAwNDM2OCAwMDAwMCBuIAowMDAwMDA0NDI3IDAwMDAwIG4gCnRyYWlsZXIKPDwKL1NpemUgMTUKL1Jvb3QgMSAwIFIKL0luZm8gNSAwIFIKL0lEWzw2YTRiYjhlMzYzMTQzZjliYWU3NWE4NzJlYmMxNmQxND48NmE0YmI4ZTM2MzE0M2Y5YmFlNzVhODcyZWJjMTZkMTQ+XQo+PgpzdGFydHhyZWYKNDQ4NAolJUVPRgo=',
        ];
        
        Mail::to($mensaje['email'])->send(new SMSAdjunto($mensaje));
    }
    

}
