<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Modelos\mPaquetePagoFacil;
use GuzzleHttp\Client;
use Carbon\Carbon;


use App\Models\PuntoVenta;
use App\Models\Cliente;
use App\Models\EmpresaUsuarioPersonal;
use App\Models\EmpresaSucursal;
use App\Models\Asociacion;
use App\Models\Producto;
use App\Models\TokenServicio;
use App\Models\claseSiat;
use App\Models\Movimineto;
use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\UsuarioEmpresaController;


class ConsultaController extends Controller
{
    //Controlador de apollo para obtener siertos metodos

    /**
     * debuelve los tipos de documento sector hablilitado por empresa.
     * @metodo EmpresaTipoDocumentoSector()
     * @autor   jakeline
     * @fecha   07-09-2023
     * @parametro int tnEmpresa
     * @return Object $oTipoDocumento
     */
    // public function empresaTipoDocumentoSector(Request $request)
    public function empresaTipoDocumentoSector($tnEmpresa)
    {
        $oUser = Auth::user();
        $oUsuarioPersonalEmpresa = EmpresaUsuarioPersonal::where('Usuario', $oUser->Usuario)
        ->where('Empresa',$tnEmpresa)->where('CodigoAmbiente',$oUser->CodigoAmbiente)->first();

        $oTipoDocumentoSector = DB::table('TIPODOCUMENTOSECTOR as td')
        ->join('ASOCIACIONTIPODOCUMENTOSECTOR as atds', 'atds.TipoDocumentoSector', '=', 'td.TipoDocumentoSector')
        ->join('ASOCIACION as a', 'a.Asociacion', '=', 'atds.Asociacion')
        ->join('PUNTOVENTA as pv', 'pv.CodigoSistema', '=', 'a.CodigoSistema')
        ->where( 'pv.PuntoVenta', $oUsuarioPersonalEmpresa->PuntoVenta )
        ->where( 'atds.Serial', 1)
        ->where( 'a.Empresa', $tnEmpresa)
            ->select('td.TipoDocumentoSector as Tipo','td.NOMBRE as Nombre', 'pv.PuntoVenta')
            ->distinct()
            ->get();

        return $oTipoDocumentoSector;
    }


    
    /**
     * debuelve la asociacion relacionada al tipo documento sector de la empresa.
     * @metodo asociacionReturn()
     * @autor   jakeline
     * @fecha   23-11-2023
     * @parametro int tnEmpresa, tnDocumentoSector
     * @return Object $oAsociacion;
     */
    // public function empresaTipoDocumentoSector(Request $request)
    public function asociacionReturn($tnEmpresa, $tnTipoDocumentoSector)
    {
        $oAsociacion = DB::table('TIPODOCUMENTOSECTOR as td')
        ->join('ASOCIACIONTIPODOCUMENTOSECTOR as atds', 'atds.TipoDocumentoSector', '=', 'td.TipoDocumentoSector')
        ->join('ASOCIACION as a', 'a.Asociacion', '=', 'atds.Asociacion')
        ->where( 'atds.Serial', 1)
        ->where( 'a.Empresa', $tnEmpresa)
        ->where( 'td.TipoDocumentoSector', $tnTipoDocumentoSector)
        ->select('td.TipoDocumentoSector as Tipo','td.NOMBRE as Nombre')
        ->distinct()
        ->get();
        
        return $oAsociacion;
    }
    
    
    
    
    /**
     * debuelve los todos los puntos de ventas habilitados de todas las sucursales habilitadas de una empresa.
     * @metodo EmpresaTipoDocumentoSector()
     * @autor   jakeline
     * @fecha   16-10-2023
     * @parametro int tnEmpresa
     * @return Object $oPuntosVenta
     */
    //    public function empresaTipoDocumentoSector(Request $request)
    public function PuntoVentaReturn($tnEmpresa)
    {
        $loPuntoVenta = DB::table('PUNTOVENTA as pv')
        ->join('SUCURSAL as s', 's.Sucursal', '=', 'pv.Sucursal')
        ->where( 's.Estado', 2)
        ->where( 'pv.Estado', 2)
        ->where( 's.Empresa', $tnEmpresa)
        ->select('pv.*')
        ->distinct()
        ->get();
        return $loPuntoVenta;
    }
    
    public function SucursalPuntoVentaReturn(){
        $oUser = Auth::user();
        
        $sqlSucursal = "SELECT s.*
                FROM EMPRESASUCURSAL as s
                WHERE Empresa = ".$oUser->EmpresaSeleccionada.";";
        
        $loSucursal= DB::select($sqlSucursal);
        
        $sqlPuntoVenta = "SELECT p.*
                FROM EMPRESASUCURSAL as s, PUNTOVENTA as p
                WHERE p.Sucursal = s.Sucursal and Empresa = ".$oUser->EmpresaSeleccionada.";";
        
        $loEmpresaUsuarioPersonal = EmpresaUsuarioPersonal::where('Usuario', $oUser->Usuario)
        ->where('Empresa', $oUser->EmpresaSeleccionada)->first();
        
        $loPuntoVenta= DB::select($sqlPuntoVenta);
        
        
        return [$loSucursal,$loPuntoVenta,$loEmpresaUsuarioPersonal];
    }
    

    /**
     * Metodo que obtiene los credenciales de google cloud desde la base de datos 
     * 02/01/2024
     */
    public function parametroConfiguracion(){
        $sql = "Select ClienteIDGoogle
                From PARAMETROS
                LIMIT 1";
        $lcParametro = DB::select($sql);
        $lcParametro = $lcParametro[0];
        $lcParametro = $lcParametro->ClienteIDGoogle;
        
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Consulta exitosa",
            'messageMostrar'=> 'Consula exitoso',
            'messageSistema'=> 'Identificador de google cloud optenido.',
            'values'=> $lcParametro,
        ],201);

    }
}
