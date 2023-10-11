<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Empresa;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\EmpresaUsuarioPersonal;
use App\Modelos\mPaquetePagoFacil;
//use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{

    public function register(Request $request)
    {
        $credentials = $request->validate([
            'tcEmail' => 'required',
            'tcPassword' => 'required',//password_confirmation
            'tcCorreoRespaldo' => 'nullable',
            'tcNombre' => 'required',
            'tcApellido' => 'required',
            'tnTelefono' => 'nullable',
        ]);
        
        if ($request->tcPassword !== $request->tcPassword_confirmation ) {
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> "La contrase�a de confirmacion no coincide",
                'values'=>null
            ]);
            
        }
        
        
        if (User::where('email', $request->tcEmail)->exists()) {
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> "Usuario Registrado Anteriormente",
                'values'=>null
            ]);
            
        }
        
        // Crear el nuevo usuario
        $user = new User();
        $user->email = $credentials['tcEmail'];
        $user->password = Hash::make($credentials['tcPassword']);
        $user->CorreoRespaldo = $credentials['tcCorreoRespaldo'];
        $user->Nombre = $credentials['tcNombre'];
        $user->Apellido = $credentials['tcApellido'];
        $user->Telefono = $credentials['tnTelefono'];
        $user->save();

        return response()->json([
            'user' => $user
        ], 201);

    }

    public function login(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        try {
            $credentials = request(['email', 'password']);
            
            if (!Auth::attempt($credentials)){
                $oUser = User::where('email', $request->email)->first();
                if($oUser){
                    $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "Error AUTHENTICACION NO VALIDA";
                    $oPaquete->message = "Clave de Seguridad Invalida";
                    $oPaquete->values = null;
                }else{
                    $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "Error AUTHENTICACION NO VALIDA";
                    $oPaquete->message = "Correo No Encontrado";
                    $oPaquete->values = null;
                }
                return response()->json($oPaquete,401);
                
            }
            $user = $request->user();
            $tokenResult = $user->createToken('Personal Access Token');
            
            $token = $tokenResult->token;
            if ($request->remember_me){
                $token->expires_at = Carbon::now()->addWeeks(1);
            }
            
            $token->save();
            $user->save();
            
            
            $oPaquete->error = 0; 
            $oPaquete->status = 1; 
            $oPaquete->messageSistema = "Comando ejecutado";
            $oPaquete->message = "Usuario obtenido";
            $oPaquete->values = [$tokenResult->accessToken];
            return response()->json($oPaquete);
            
            
        } catch (JWTException $e) {
            error::guardar("Error generacion de token )", $e);
            
            
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error AUTHENTICACION NO VALIDA";
            $oPaquete->message = "a ocurrido un error  ";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }
        
    }

    public function selecionarEmpresa(Request $request)
    {
        $request->validate([
            'Empresa' => 'required',
        ]);

        $oUser = $request->user();
        if($request->Empresa == 0){
            $oUser->EmpresaSeleccionada = 0;
            $oUser->save();
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "ejecusion sin inconvenientes",
                'messageMostrar'=> 'se Selecciono la empresa',
                'messageSistema'=> 'comando ejecutado',
                'values'=> 1
            ]);



        }

        $oUser = $request->user();
        $oEmpresaspersonal = EmpresaUsuarioPersonal::where('Usuario',$oUser->Usuario)->get();
        $oEmpresaSeleccionada = Empresa::find($request->Empresa);
        foreach ($oEmpresaspersonal as $oEmpresapersonal) {
            $oEmpresaIterada = Empresa::find($oEmpresapersonal->Empresa);
        //            return $oEmpresaIterada;
            if($oEmpresaIterada->Empresa == $oEmpresaSeleccionada->Empresa){
                $oUser->EmpresaSeleccionada = $oEmpresaSeleccionada->Empresa;
                $oUser->save();

                return response()->json([
                    'error' => 0,
                    'status' => 1,
                    'message'=> "ejecusion sin inconvenientes",
                    'messageMostrar'=> 'se Selecciono la empresa',
                    'messageSistema'=> 'comando ejecutado',
                    'values'=> 1
                ]);

            }
        }

        return response()->json([
            'error' => 1,
            'status' => 0,
            'message'=> "a ocurrido un error",
            'messageMostrar'=> 'la empresa no conforma dentro de las empresas personales del Ususario',
            'messageSistema'=> 'error de ejecucion',
            'values'=> null
        ]);
    }

    public function logout(Request $request)
    {

        $request->user()->token()->revoke();
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Usuario cerro sesion",
            'messageMostrar'=> 'Successfully logged out',
            'messageSistema'=> 'Successfully logged out',
            'values'=> null
        ]);

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        $oUser = $request->user();
        if($oUser->EmpresaSeleccionada !== 0 ){
            
        }
        $oEmpresa = Empresa::where('Empresa', $oUser->EmpresaSeleccionada)->first();
        $lcUrlLogo;
        if($oEmpresa){
            $lcUrlLogo = $oEmpresa->UrlLogo;
            $oUser->EmpresaSeleccionada = $oEmpresa->Nombre;
            if($lcUrlLogo === ''){
                $lcUrlLogo = 'favicon.ico';
            }
        }else{
            $lcUrlLogo =env('APP_URL').env('APP_PORT').'/imagenes/default/multi-empresas.png';
            $oUser->EmpresaSeleccionada ='Multiempresa';
        }
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Usuario obtenido",
            'messageMostrar'=> 'se obtubo el usuario',
            'messageSistema'=> 'se obtubo el usuario',
            'values'=>[$oUser,$lcUrlLogo]
        ]);

        return response()->json($request->user());
    }

    public function update(Request $request,$tnVenta)
    {
        $recurso = User::find($tnVenta);
        $recurso->update($request->all());
        $recurso->save();

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Usuario obtenido",
            'messageMostrar'=> 'se obtubo el usuario',
            'messageSistema'=> 'se obtubo el usuario',
            'values'=>$recurso
        ]);


    }

    public function userPass(Request $request)
    {
        $oUser = Auth::user();
        $request->validate([
            'tcActual' => 'required',
            'tcNueva' => 'required',
            'tcConfirmar' => 'required',
        ]);

        if($request->tcNueva != $request->tcConfirmar){
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> "la contraseña Repetida no coincide",
                'messageSistema'=> 'a ocurrido un error',
                'values'=>null
            ]);
        }

        #Match The Old Password
        if(!Hash::check($request->tcActual, auth()->user()->password)){
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> "la contraseña actual no coincide",
                'messageSistema'=> 'error',
                'values'=>null
            ]);
        }

        #Update the new Password
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->tcNueva)
        ]);

        Auth::logout();

        return back()->with("status", "Password changed successfully!");


        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "el Usuario a Actualizado su contraseña",
            'messageMostrar'=> '',
            'messageSistema'=> 'Contraseña Actualizada',
            'values'=>$recurso
        ]);


    }
}
