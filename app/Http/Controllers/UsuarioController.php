<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\EmpresaUsuarioPersonal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\DB;


class UsuarioController extends Controller
{

    public function register(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email|unique:USUARIO',
            'password' => 'required|string|confirmed',//password_confirmation
            'Correo' => 'required|email|unique:USUARIO',
            'Nombre' => 'required',
            'Apellido' => 'required',
            'Telefono' => 'nullable|integer',
        ]);

        // Crear el nuevo usuario
        $user = new Usuario();
        $user->email = $credentials['email'];
        $user->password = Hash::make($credentials['password']);
        $user->Correo = $credentials['Correo'];
        $user->Nombre = $credentials['Nombre'];
        $user->Apellido = $credentials['Apellido'];
        $user->Telefono = $credentials['Telefono'];
        $user->save();

        return response()->json([
            'user' => $user
        ], 201);

    }

    public function signUp(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email|unique:USER',
            'password' => 'required|string|confirmed',
            'Correo' => 'required|email|unique:USUARIO',
            'Nombre' => 'required',
            'Apellido' => 'required',
            'Telefono' => 'nullable|integer',
        ]);


        // Crear el nuevo usuario
        $user = new Usuario();
        $user->email = $credentials['email'];
        $user->password = Hash::make($credentials['password']);
        $user->Correo = $credentials['Correo'];
        $user->Nombre = $credentials['Nombre'];
        $user->Apellido = $credentials['Apellido'];
        $user->Telefono = $credentials['Telefono'];
        $user->save();

        // Generar y devolver el token JWT para el usuario recién registrado

        //$token = JWTAuth::fromUser($user);
        return response()->json([
            'user' => $user //,
          //  'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;
        if ($request->remember_me){
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();
        $user->api_token = $token->id;
        $user->save();


        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Usuario obtenido",
            'messageMostrar'=> 'se obtubo el usuario',
            'messageSistema'=> 'se obtubo el usuario',
            'values'=> [$tokenResult->accessToken]
        ]);
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

    /**
     * Obtener el objeto User como json
     */
    public function user(Request $request)
    {
        $oUser = $request->user();
        $oEmpresa = Empresa::find($oUser->EmpresaSeleccionada);
        $lcUrlLogo;
        if($oEmpresa){
            $lcUrlLogo = $oEmpresa->UrlLogo;
            $oUser->EmpresaSeleccionada = $oEmpresa->Nombre;
        }else{
            $lcUrlLogo =env('APP_URL') .'/imagenes/default/prodductoServicio.jpg';
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
        $recurso = Usuario::find($tnVenta);
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
        Usuario::whereId(auth()->user()->id)->update([
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
/*

mis facturaws
productos
catalogo

*/
