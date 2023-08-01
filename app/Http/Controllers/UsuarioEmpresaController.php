<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\EMPRESAUSUARIOPERSONAL;

class UsuarioEmpresaController extends Controller
{
    //

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'Usuario' => 'required',
            'Empresa' => 'required',
        ]);

        // Crear el nuevo usuario
        $oUser = Usuario::find($credentials['Usuario']);
        if(is_null($oUser)){
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "Dato Invialido",
                'messageMostrar'=> 'no se encontro al Usuario',
                'messageSistema'=> 'usuario invalido',
                'values'=> null,
            ]);
        }

        $oEmpresa = Empresa::find($credentials['Empresa']);
        if(is_null($oEmpresa)){
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "Dato Invialido",
                'messageMostrar'=> 'no se encontro a la Empresa',
                'messageSistema'=> 'empresa invalida',
                'values'=> null,
            ]);
        }

        $ultimoRegistro = $this->serieAutoIncrement();
//        return $ultimoRegistro;

        $personal = EMPRESAUSUARIOPERSONAL::create([
            'Empresa' => $oEmpresa->Empresa,
            'Serial'=> $ultimoRegistro,
            'Usuario'=> $oUser->Usuario,
            'Estado'=> 1
        ]);

        // Generar y devolver el token JWT para el usuario recién registrado

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Registro exitoso",
            'messageMostrar'=> 'Registro exitoso',
            'messageSistema'=> 'empresa vinculada con el usuario',
            'values'=> $personal,
        ],201);

    }

    public function misEmpresas(Request $request)
    {
        $credentials = $request->validate([
            'Usuario' => 'required',
        ]);


        // Crear el nuevo usuario
        $oUser = Usuario::find($credentials['Usuario']);
        if(is_null($oUser)){
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "Dato Invialido",
                'messageMostrar'=> 'no se encontro al Usuario',
                'messageSistema'=> 'usuario invalido',
                'values'=> null,
            ]);
        }

        $personal = EmpresaUsuarioPersonal::where('Usuario',$oUser->Usuario)->get();
//        return $personal;

        $empresas = Empresa::select('EMPRESA.*')
            ->leftJoin('EMPRESAUSUARIOPERSONAL', 'EMPRESAUSUARIOPERSONAL.Empresa', '=', 'EMPRESA.Empresa')
            ->leftJoin('USUARIO', 'EMPRESAUSUARIOPERSONAL.Usuario', '=', 'USUARIO.Usuario')
            ->where('USUARIO.Usuario', '=', $oUser->Usuario)
            ->orderBy('EMPRESA.Empresa', 'asc')
            ->get();

  //      return $empresas;
        // Generar y devolver el token JWT para el usuario recién registrado

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Consulta exitosa",
            'messageMostrar'=> 'Consula exitoso',
            'messageSistema'=> 'Listado de Empresas Personales Ejecutado',
            'values'=> $empresas,
        ],201);

    }


    public function serieAutoIncrement(){
        $ultimoRegistro = EmpresaUsuarioPersonal::orderBy('Serial', 'desc')->first();
        if(is_null($ultimoRegistro)){
            $ultimoRegistro =1;
        }else{
            $ultimoRegistro =$ultimoRegistro->Serial +1;
        }

        return $ultimoRegistro;
    }
}
