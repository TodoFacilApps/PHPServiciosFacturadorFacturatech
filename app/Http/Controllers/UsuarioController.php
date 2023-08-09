<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Usuario obtenido",
            'messageMostrar'=> 'se obtubo el usuario',
            'messageSistema'=> 'se obtubo el usuario',
            'values'=> $request->user()
        ]);

        return response()->json($request->user());
    }
}
/*

mis facturaws
productos
catalogo

*/
