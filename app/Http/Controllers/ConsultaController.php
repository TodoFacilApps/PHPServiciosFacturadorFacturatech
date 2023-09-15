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
use App\Models\EMPRESAUSUARIOPERSONAL;
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
//    public function empresaTipoDocumentoSector(Request $request)
    public function empresaTipoDocumentoSector($tnEmpresa)
    {
        $oTipoDocumentoSector = DB::table('TIPODOCUMENTOSECTOR as td')
        ->join('ASOCIACIONTIPODOCUMENTOSECTOR as atds', 'atds.TipoDocumentoSector', '=', 'td.TipoDocumentoSector')
        ->join('ASOCIACION as a', 'a.Asociacion', '=', 'atds.Asociacion')
        ->where( 'atds.Serial', 1)
        ->where( 'a.Empresa', $tnEmpresa)
            ->select('td.TipoDocumentoSector as Tipo','td.NOMBRE as Nombre')
            ->distinct()
            ->get();

        return $oTipoDocumentoSector;
    }
}
