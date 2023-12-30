<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Empresa;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\EmpresaUsuarioPersonal;
use App\Models\PuntoVenta;
use App\Models\Asociacion;
use App\Models\Cufd;
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
use Mail;


use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\EmpresaSucursal;
use App\Http\Controllers\UsuarioEmpresaController;

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
            'tnEmpresa' => 'required',
            'tnSucursal' => 'nullable',
            'tnPuntoVenta' => 'nullable',
        ]);

        $oUser = $request->user();
        if($request->tnEmpresa == 0){
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
        
        $oEmpresapersonal = EmpresaUsuarioPersonal::where('Usuario',$oUser->Usuario)
        ->where('Empresa',$oUser->EmpresaSeleccionada)
        ->where('CodigoAmbiente',$oUser->CodigoAmbiente)
        ->first();
        
        $sql = "UPDATE EMPRESAUSUARIOPERSONAL SET "; 
        if($oUser->EmpresaSeleccionada === $request->tnEmpresa){
            if($oEmpresapersonal ){
                if($oEmpresapersonal->Sucursal === $request->tnSucursal){
                    $sql .=  " PuntoVenta = ".$request->tnPuntoVenta." ";
                }else{
                    $oEmpresapersonal->Sucursal = $request->tnSucursal;
                    
                    $oPuntoVenta = PuntoVenta::where('Sucursal',$request->tnSucursal)
                    ->where('CodigoAmbiente',$oUser->CodigoAmbiente)->first();
                    if($oPuntoVenta){
                        $sql .=  " Sucursal = ".$request->tnSucursal." ,  PuntoVenta = ".$oPuntoVenta->PuntoVenta." ";
                    }else{
                        return response()->json([
                            'error' => 1,
                            'status' => 1,
                            'message'=> "a ocurrido un error",
                            'messageMostrar'=> 'la sucursal no cuenta con un punto de venta habilitado para el Ambiente',
                            'messageSistema'=> 'error de ejecucion',
                            'values'=> null
                        ]);
                    }
                }
                $sql .= "WHERE Empresa = ".$request->tnEmpresa." and Usuario = ".$oEmpresapersonal->Usuario." and CodigoAmbiente = ".$oEmpresapersonal->CodigoAmbiente.";";
                    DB::select($sql);
            }
        }else{
            $controller = new  UsuarioEmpresaController();
            if($controller->esMiEmpresa($request->tnEmpresa)){
                $oUser->EmpresaSeleccionada = $request->tnEmpresa;
                $sql = "UPDATE USUARIO SET EmpresaSeleccionada =".$request->tnEmpresa."
                        WHERE USUARIO = ".$oUser->Usuario." ;";
                DB::select($sql);
                
           }else{
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message'=> "a ocurrido un error",
                    'messageMostrar'=> 'la empresa no conforma dentro de las empresas personales del Ususario',
                    'messageSistema'=> 'error de ejecucion',
                    'values'=> null
                ]);
            }
        }
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "ejecusion sin inconvenientes",
            'messageMostrar'=> 'se Selecciono la empresa',
            'messageSistema'=> 'comando ejecutado',
            'values'=> 1
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
        $oEmpresaUsuarioPersonal = EmpresaUsuarioPersonal::where('Usuario',$oUser->Usuario)
        ->where('Empresa',$oUser->EmpresaSeleccionada)
        ->where('CodigoAmbiente', '=', $oUser->CodigoAmbiente)
        ->first();
        
        
        $oEmpresas = Empresa::select('EMPRESA.*')
        ->leftJoin('EMPRESAUSUARIOPERSONAL', 'EMPRESAUSUARIOPERSONAL.Empresa', '=', 'EMPRESA.Empresa')
        ->leftJoin('USUARIO', 'EMPRESAUSUARIOPERSONAL.Usuario', '=', 'USUARIO.Usuario')
        ->where('USUARIO.Usuario', '=', $oUser->Usuario)
        ->where('EMPRESAUSUARIOPERSONAL.CodigoAmbiente', '=', $oUser->CodigoAmbiente)
        ->orderBy('EMPRESA.Empresa', 'asc')
        ->get();
        
        
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
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "Usuario obtenido",
                'messageMostrar'=> 'se obtubo el usuario',
                'messageSistema'=> 'se obtubo el usuario',
                'values'=>[
                    $oUser,
                    $lcUrlLogo,
                    $oEmpresaUsuarioPersonal, 
                    $oEmpresas,
                    [],
                    []
                ]
            ]);
            
            
        }

        
        $oSucursal = EmpresaSucursal::where('Empresa', $oEmpresaUsuarioPersonal->Empresa)->get();
        
        
        $oPuntoVenta = PuntoVenta::where('Sucursal',$oEmpresaUsuarioPersonal->Sucursal)
        ->where('CodigoAmbiente', $oUser->CodigoAmbiente)->get();
        
        
        
        $oCufd = Cufd::where('Sucursal',$oEmpresaUsuarioPersonal->Sucursal)
        ->where('Empresa', $oEmpresaUsuarioPersonal->Empresa)
        ->where('PuntoVenta', $oEmpresaUsuarioPersonal->Sucursal)
        ->where('CodigoAmbiente', $oUser->CodigoAmbiente)
        ->orderBy('FechaVigencia', 'desc')
        ->first();
                
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Usuario obtenido",
            'messageMostrar'=> 'se obtubo el usuario',
            'messageSistema'=> 'se obtubo el usuario',
            'values'=>[
                $oUser,
                $lcUrlLogo,
                $oEmpresaUsuarioPersonal,
                $oEmpresas,
                $oSucursal,
                $oPuntoVenta,
                $oCufd
            ]
        ]);

        return response()->json($request->user());
    }

    public function update(Request $request,$tnUsuario)
    {
        $recurso = User::find($tnUsuario);
        
        $lnEmpresaSeleccionada = $recurso->EmpresaSeleccionada;
        
        $asociacion = Asociacion::where('Empresa', $lnEmpresaSeleccionada)
        ->Where('CodigoAmbiente', $request->CodigoAmbiente)
        ->get();
        
        if(count($asociacion)>0){
            $recurso->update($request->all());
            $recurso->EmpresaSeleccionada = $lnEmpresaSeleccionada;
            $recurso->save();
            
        }else{
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> "La Empresa no cuenta con credenciales de Prueba",
                'messageMostrar'=> 'error al ejecutar comando',
                'messageSistema'=> 'no existe asociacion con respecto a la empresa y codigo de ambiente',
                'values'=>null
            ]);
            
        }
        

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

    public function enlace(Request $request)
    {
        //Validación de email
        $request->validate([
            'tcEmail' => 'required',
        ]);

        $oUser = User::where('email', $request->tcEmail)->first();
        if($oUser){
            //Generación de token y almacenado en la tabla password_resets
            $token = Str::random(64);
            DB::table('password_resets')->insert([
                'email' => $request->tcEmail,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            //Envío de email al usuario
            Mail::send('email', ['token' => $token], function($message) use($request){
                $message->to($request->tcEmail);
                $message->subject('Cambiar contraseña en Facturador');
            });

                return response()->json([
                    'error' => 0,
                    'status' => 1,
                    'message'=> "Comando ejecutado",
                    'messageSistema'=> 'error',
                    'values'=>'Te hemos enviado un email a <strong>' . $request->email . '</strong> con un enlace para realizar el cambio de contraseña.'
                ]);
        }else{
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> "No se encontro el coreo electronico entre los usuarios.",
                'messageSistema'=> 'error',
                'values'=>null
            ]);
        }
    }

    public function optenerCorreo(Request $request)
    {
        $request->validate([
            'tcToken' => 'required',
        ]);

        $solicitud = DB::table('password_resets')
        ->where('token', $request->tcToken)
        ->first(); // Obtiene la tupla más reciente

        if ($solicitud) {
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "comando ejecutado",
                'values'=>$solicitud->email
            ]);
        } else {
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> "El codigo Temporal es Invalido",
                'messageSistema'=> 'error',
                'values'=>null
            ]);
        }
    }

    //cambio la clave
    
    public function cambiar(Request $request)
    {
        DB::beginTransaction(); // Iniciar la transacci�n
        
        try {
            $request->validate([
                'tcEmail' => 'required',
                'tcPassword' => 'required',
                'tcPasswordConfirmation' => 'required'
            ]);            
            // Compruebo token v�lido
            $comprobarToken = DB::table('password_resets')->where(['email' => $request->tcEmail, 'token' => $request->token])->first();
            if (!$comprobarToken) {
                throw new \Exception("El enlace no es v�lido");
            }
            
            // Actualizo password
            User::where('email', $request->tcEmail)->update(['password' => Hash::make($request->tcPassword)]);
            
            // Borro token para que no se pueda volver a usar
            DB::table('password_resets')->where(['email' => $request->tcEmail])->delete();
            
            DB::commit(); // Confirmar (commit) los cambios en la base de datos
            
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => "comando ejecutado",
                'values' => 'La Clave de Seguridad se ha cambiado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir (rollback) los cambios en caso de excepci�n
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => $e->getMessage(),
                'messageSistema' => 'error',
                'values' => null
            ]);
        }
    }

    public function loginGogle(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'email' => 'required|string|email',
            'id_google' => 'required',
            'remember_me' => 'boolean'
        ]);
        try {
            $credentials = User::where('Google', $request->id_google)->first();
            
            if ($credentials){

                $user = $credentials;
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
                
            }else{
                $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error AUTHENTICACION NO VALIDA";
                $oPaquete->message = "Correo No Encontrado";
                $oPaquete->values = null;
                
                return response()->json($oPaquete,401);
            
            }
            
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
    

    public function sincronizarGogle(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'email' => 'required|string|email',
            'id_google' => 'required',
            'remember_me' => 'boolean'
        ]);
        try {
            $credentials = User::where('Google', $request->id_google)->first();
            
            if ($credentials){
                
                $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error al Sincronizar";
                $oPaquete->message = "el Correo ya esta vinculado a otra cuenta";
                $oPaquete->values = null;
                return response()->json($oPaquete,401);
                
            }else{
                $user = $request->user();
                if(is_null($user->Google)){
                    
                    $user->Google = $request->id_google;
                    $user->save();
                    
                    $oPaquete->error = 0;
                    $oPaquete->status = 1;
                    $oPaquete->messageSistema = "Comando ejecutado";
                    $oPaquete->message = "Usuario sincronizado Corectamente";
                    $oPaquete->values = 1;
                    
                    return response()->json($oPaquete);
                    
                    
                }else{
                    $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "Error al Sincronizar";
                    $oPaquete->message = "la cuenta ya se encuentra sincronizada";
                    $oPaquete->values = null;
                    return response()->json($oPaquete,401);
                }
            }
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
    
    
    
}
