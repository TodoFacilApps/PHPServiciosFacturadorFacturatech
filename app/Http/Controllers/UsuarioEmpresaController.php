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
    /**
     *
     */
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
        //return $ultimoRegistro;
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

    /**
     * mis  empresas
     */
    public function misEmpresas(Request $request)
    {
        $oUser = $request->user();
        /*
        $credentials = $request->validate([
            'Usuario' => 'required',
        ]);
        */

        // Crear el nuevo usuario
        $oUser = Usuario::find($oUser['Usuario']);
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
        //return $personal;

        $empresas = Empresa::select('EMPRESA.*')
            ->leftJoin('EMPRESAUSUARIOPERSONAL', 'EMPRESAUSUARIOPERSONAL.Empresa', '=', 'EMPRESA.Empresa')
            ->leftJoin('USUARIO', 'EMPRESAUSUARIOPERSONAL.Usuario', '=', 'USUARIO.Usuario')
            ->where('USUARIO.Usuario', '=', $oUser->Usuario)
            ->orderBy('EMPRESA.Empresa', 'asc')
            ->get();

        //return $empresas;
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
    /**
     * show => metodo que rebuelve una empresa
     * fecha 04/08/2023
     */
    public function show($InEmpresa)
    {
        $InUser = auth()->id();


        //      return $empresas;
        // Generar y devolver el token JWT para el usuario recién registrado
        $empresa = Empresa::find($InEmpresa);
        if(is_null($empresa)){
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "Dato Invialido",
                'messageMostrar'=> 'no se encontro a la empresa',
                'messageSistema'=> 'usuario invalido',
                'values'=> null,
            ]);
        }


        $personal = EmpresaUsuarioPersonal::where('Usuario',$InUser)
        ->where('Empresa',$InEmpresa)
        ->get();
        if ($personal->isEmpty()) {
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "Dato Invialido",
                'messageMostrar'=> 'no se encontro una relacion entre la empresa y el usuario',
                'messageSistema'=> 'no hay relacion Empresa Usuario',
                'values'=> null,
            ]);
        }

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Consulta exitosa",
            'messageMostrar'=> 'Consula exitoso',
            'messageSistema'=> 'Listado de Empresas Personales Ejecutado',
            'values'=> $empresa,
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
