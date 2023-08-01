<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
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
            'email' => 'required|unique:USUARIO',
            'password' => 'required|min:6',
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

        $token = JWTAuth::fromUser($user);
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);

    }

    public function signUp(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|unique:USUARIO',
            'password' => 'required|min:6',
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

        $token = JWTAuth::fromUser($user);
        return response()->json([
            'user' => $user,
            'token' => $token
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
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
/*
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'values'=> $user,
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
        ]);
*/
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Usuario obtenido",
            'messageMostrar'=> 'se obtubo el usuario',
            'messageSistema'=> 'se obtubo el usuario',
            'values'=> $user,$token,
        ]);
    }



    public function logout(Request $request)
    {

        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Obtener el objeto User como json
     */
    public function user(Request $request)
    {

        return response()->json($request->user());
    }
}
