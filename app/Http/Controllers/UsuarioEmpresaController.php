<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pais;
use App\Models\Empresa;
use App\Models\EmpresaSucursal;
use App\Models\PuntoVenta;
use App\Models\EmpresaUsuarioPersonal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\ConsultaController;
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
            'Sucursal' => 'nullable',
        ]);

        // Verificar si CodigoSucursal está presente y no es nulo
        if (isset($credentials['Sucursal'])) {
            // Si está presente, asignar el valor cero si es nulo
            $credentials['Sucursal'] = $credentials['Sucursal'] ?? 0;
        } else {
            // Si no está presente, asignar el valor cero
            $oSucursal= EmpresaSucursal::where('Empresa',$credentials['Empresa'])->first();
            
            $credentials['Sucursal'] = $oSucursal->Sucursal;
        }
        
        // Crear el nuevo usuario
        $oUser = User::find($credentials['Usuario']);
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
        $oSucursal = EmpresaSucursal::Where('Empresa',$credentials['Empresa'])
        ->where('Sucursal',$credentials['Sucursal'])->first();
        
        if(is_null($oSucursal)){
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "Dato Invialido",
                'messageMostrar'=> 'no se encontro a la Sucursal',
                'messageSistema'=> 'Sucursal no relacionada con la empresa',
                'values'=> null,
            ]);
        }
        
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
        $oPuntoVenta = PuntoVenta::where('Sucursal',$oSucursal->Sucursal)
        ->where('CodigoAmbiente', 1)->first();
        $ultimoRegistro = $this->serieAutoIncrement();
        //return $ultimoRegistro;
        $personal = EmpresaUsuarioPersonal::create([
            'Empresa' => $oEmpresa->Empresa,
            'Serial'=> $ultimoRegistro,
            'Usuario'=> $oUser->Usuario,
            'Sucursal'=> $oSucursal->Sucursal,
            'CodigoAmbiente'=>1,
            'PuntoVenta'=>$oPuntoVenta->PuntoVenta,
            'Estado'=> 1
        ]);
        
        $oPuntoVentaPrueva = PuntoVenta::where('Sucursal',$oSucursal->Sucursal)
        ->where('CodigoAmbiente', 2)->first();
        $ultimoRegistro = $this->serieAutoIncrement();
        //return $ultimoRegistro;
        $personalPrueva = EmpresaUsuarioPersonal::create([
            'Empresa' => $oEmpresa->Empresa,
            'Serial'=> $ultimoRegistro,
            'Usuario'=> $oUser->Usuario,
            'Sucursal'=> $oSucursal->Sucursal,
            'CodigoAmbiente'=> 2,
            'PuntoVenta'=>$oPuntoVentaPrueva->PuntoVenta,
            'Estado'=> 1
        ]);
        
        $oUser->EmpresaSeleccionada = $oEmpresa->Empresa;
        $oUser->save();
        
        // Generar y devolver el token JWT para el usuario recién registrado
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Registro exitoso",
            'messageMostrar'=> 'Registro exitoso',
            'messageSistema'=> 'empresa vinculada con el usuario',
            'values'=> [$personal,$personalPrueva],
        ],201);

    }

    /**
     *
     * mis  empresas
     */
    public function misEmpresas(Request $request)
    {
        $oUser = auth()->user();
        // Crear el nuevo usuario
        $oUser = User::find($oUser->Usuario);

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
            ->where('EMPRESAUSUARIOPERSONAL.CodigoAmbiente', '=', $oUser->CodigoAmbiente)
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
        // queda pendiente lo del punto de venta
        $empresa = DB::table('EMPRESA as e')
        ->select('e.*','p.Nombre as Pais')
        ->join('PAIS as p', 'p.Pais', '=', 'e.Pais')
        ->where('e.Empresa',$InEmpresa)
        ->first();


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


    public function misEmpresasReturn()
    {
        $oUser = Auth::user();
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
        $empresas;
        $lnEmpresaSeleccionada =$oUser->EmpresaSeleccionada;
        if(($lnEmpresaSeleccionada === 0)||($lnEmpresaSeleccionada === '0')){
            $empresas = Empresa::select('EMPRESA.*')
            ->leftJoin('EMPRESAUSUARIOPERSONAL', 'EMPRESAUSUARIOPERSONAL.Empresa', '=', 'EMPRESA.Empresa')
            ->leftJoin('USUARIO', 'EMPRESAUSUARIOPERSONAL.Usuario', '=', 'USUARIO.Usuario')
            ->where('USUARIO.Usuario', '=', $oUser->Usuario)
            ->orderBy('EMPRESA.Empresa', 'asc')
            ->get();
        }else{
            $empresas = Empresa::where('Empresa',$lnEmpresaSeleccionada)->get();
            
        }
        

        return $empresas;

    }

    public function esMiEmpresa($tnEmpresa)
    {
        $oUser = Auth::user();
        if(is_null($oUser)){
            return false;
        }

        return EmpresaUsuarioPersonal::where('Usuario', $oUser->Usuario)
            ->where('Empresa', $tnEmpresa)
            ->exists();

    }

    public function ver(){
        $oUser = auth()->user();

        $lnEmpresaSeleccionada = $oUser->EmpresaSeleccionada ;
        if($lnEmpresaSeleccionada ===0){
            $oEmpresas = $this->misEmpresasReturn();
            $lnEmpresaSeleccionada = $oEmpresas[0]->Empresa;
        }
        
        return $this->show($lnEmpresaSeleccionada);
    }

    public function  miEmpresa(){
        
        $controller = new ConsultaController();        
        $result = $controller->SucursalPuntoVentaReturn();
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Consulta exitosa",
            'messageMostrar'=> 'Consula exitoso',
            'messageSistema'=> 'Listado de Sucursales y puntos de ventas Ejecutado',
            'values'=> $result,
        ],201);
        
    }
    
}
