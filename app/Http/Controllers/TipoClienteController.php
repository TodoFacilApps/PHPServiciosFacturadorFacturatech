<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Empresa;
use App\Models\TipoCliente;
use App\Models\EmpresaUsuarioPersonal;
use App\Http\Controllers\UsuarioEmpresaController;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class TipoClienteController extends Controller
{
    //
    public function index()
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oUser = auth()->user();


        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();


        $oTipoCliente = DB::table('TIPOCLIENTE as tc')
        ->join('EMPRESA as e', 'e.Empresa', '=', 'tc.Empresa')
        ->join('EMPRESAUSUARIOPERSONAL as eup', 'eup.Empresa', '=', 'e.Empresa')
        ->join('USUARIO as u', 'u.Usuario', '=', 'eup.Usuario')
        ->where('u.Usuario', $oUser->Usuario)
        ->select('tc.*')->get();

        $oEmpresaSeleccionada = $oUser->EmpresaSeleccionada;
        
        if(($oEmpresaSeleccionada ===0) || ($oEmpresaSeleccionada =='0')){
            $oEmpresaSeleccionada = $oEmpresas[0]->Empresa;
        }
        
        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [$oTipoCliente, $oEmpresas, $oEmpresaSeleccionada ] ;
        return response()->json($oPaquete);
    }


    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oUser = auth()->user();


        $empresasController = new UsuarioEmpresaController();
        if(!$empresasController->esMiEmpresa($request->Empresa)){
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "comando ejecutado";
            $oPaquete->message = "la empresa no esta esctrechamente relacionada con el usuario";
            $oPaquete->values = null;
        }else{

            $loTipoCliente= TipoCliente::where('Empresa',$request->Empresa)
            ->where('Descripcion', $request->Descripcion)
            ->count();


            if($loTipoCliente > 0){
                $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "comando ejecutado";
                $oPaquete->message = "el Tipo de Cliente ya Existe";
                $oPaquete->values = null ;

            }else{

                $PrecioPorMayor = $request->PrecioPorMayor ?? false;
                $PrecioOferta = $request->PrecioOferta ?? false;
                $PrecioRemate = $request->PrecioRemate ?? false;

                $loTipoCliente= TipoCliente::create([
                    'Empresa'=> $request->Empresa,
                    'Descripcion'=> $request->Descripcion,
                    'PrecioOferta'=> $PrecioOferta,
                    'PrecioPorMayor'=> $PrecioPorMayor,
                    'PrecioRemate'=> $PrecioRemate
                ]);

                $oTipoCliente = DB::table('TIPOCLIENTE as tc')
                ->join('EMPRESA as e', 'e.Empresa', '=', 'tc.Empresa')
                ->join('EMPRESAUSUARIOPERSONAL as eup', 'eup.Empresa', '=', 'e.Empresa')
                ->join('USUARIO as u', 'u.Usuario', '=', 'eup.Usuario')
                ->where('u.Usuario', $oUser->Usuario)
                ->select('tc.*')
                ->get();

                $oEmpresas = $empresasController->misEmpresasReturn();

                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "comando ejecutado";
                $oPaquete->message = "ejecusion sin inconvenientes";
                $oPaquete->values = [$oTipoCliente, $oEmpresas, $oUser->EmpresaSeleccionada] ;

            }

        }
        return response()->json($oPaquete);
    }


}



