<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Sucursal;
use App\Http\Controllers\UsuarioEmpresaController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;




class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }


    public function dashboard()
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();
        $oUser = auth()->user();

        $lnEmpresaSeleccionada = $oUser->EmpresaSeleccionada;
        if((!$lnEmpresaSeleccionada)||($lnEmpresaSeleccionada===0)||($lnEmpresaSeleccionada ==='0')){
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 2; // Sucedio un error
            $oPaquete->messageSistema = "comando ejecutado";
            $oPaquete->message = "El Usuario no a Seleccionado ninguna Empresa";
            $oPaquete->values = null;
            //notas de ventas realizadad por tres meces
            return response()->json($oPaquete);
        }
        
        //segundo caso ventas por sucursal por tres meses
        $fechaActual =  Carbon::now('America/La_Paz');
        
        $fechaTresMesesAntes = $fechaActual->copy();
        $fechainicioMes = $fechaActual->copy();
        
        $fechaTresMesesAntes = $fechaTresMesesAntes->subMonths(3);
        $fechaTresMesesAntes = $fechaTresMesesAntes->format('Y-m-d');

        $fechainicioMes = $fechainicioMes->firstOfMonth();
        $fechainicioMes  = $fechainicioMes->format('Y-m-d');

        
        $fechaActual = $fechaActual->format('Y-m-d');
        
        
        $oSincronizacionControlador = new SincronizacionSiatController();
                        
        
        
        $loVentaSucursal = DB::table('VENTA')
            ->select('VENTA.Fecha', DB::raw('COUNT(VENTA.Venta) as Cantidad'))
            ->leftJoin('EMPRESASUCURSAL', 'EMPRESASUCURSAL.Sucursal', '=', 'VENTA.Sucursal')
            ->where('VENTA.Empresa', $lnEmpresaSeleccionada)
            ->whereBetween('VENTA.Fecha', ['2023-09-01 00:00:00', '2023-10-02 00:00:00'])
            ->whereIn('VENTA.Venta', function ($query) {
                $query->select('Venta')
                    ->from('VENTAFACTURA')
                    ->where('EstadoSiat', 1);
            })
            ->groupBy('VENTA.Fecha')
            ->get();


        $ldPrimeroMes =  Carbon::now('America/La_Paz')->firstOfMonth();
        $ldPrimeroMes = $ldPrimeroMes->format('Y-m-d');
        
        $loVentaConFacturaPendiente;
        $loVentaConFacturaValidas;
        $loVentaConFacturaAnuladas;
        $loVentaConFacturaObservadas;
        $loVentaConFacturaRechazadas;

        $anEstadosSiat = [1,2,3,4,5];
        $facturas;
        foreach ($anEstadosSiat as $item) {
            $facturas = $oSincronizacionControlador->buscarFacturaReturn($fechainicioMes, $fechaActual, $item);
            $facturas =$facturas->values;
                        
            if(filled($facturas)){
                $consulta = array_count_values(array_map(function($factura) {
                    return $factura->FechaEmision ;
                }, $facturas));
            }else{
                $consulta = [ $fechaActual=> 0];
            }
                
            switch($item){
                case 1:
                    $loVentaConFacturaPendiente =$consulta;
                    break;
                case 2:
                    $loVentaConFacturaValidas =$consulta;
                    break;
                case 3:
                    $loVentaConFacturaAnuladas =$consulta;
                    break;
                case 4:
                    $loVentaConFacturaObservadas =$consulta;
                    break;
                case 5:
                    $loVentaConFacturaRechazadas =$consulta;
                    break;
            }
        }
        
        
        
        $loVentaSinFactura = Venta::select('VENTA.Fecha' , \DB::raw('COUNT(VENTA.Venta) as Cantidad'))
            ->where('VENTA.Empresa', $oEmpresas[0]->Empresa)
            ->whereBetween('VENTA.Fecha', [$ldPrimeroMes, $fechaActual])
            ->whereNotIn('VENTA.Venta', function($query) {
                $query->select('Venta')->from('VENTAFACTURA');
                })
            ->groupBy('VENTA.Fecha')
            ->get();
 
            
        $facturas = $oSincronizacionControlador->buscarFacturaReturn($fechainicioMes, $fechaActual, 2);
        $facturas =$facturas->values;
        
        $loEvolucionVentas = [];
        if(filled($facturas)){
            $loEvolucionVentas = array_count_values(array_map(function($factura) {
                return implode(',', [ $factura->CodigoSucursal, $factura->FechaEmision]);
            }, $facturas));
                
        }
            

        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [
            $loEvolucionVentas,
            $loVentaSinFactura,
            $loVentaConFacturaPendiente,
            $loVentaConFacturaValidas,
            $loVentaConFacturaAnuladas,
            $loVentaConFacturaObservadas,
            $loVentaConFacturaRechazadas
            ];
        //notas de ventas realizadad por tres meces
        return response()->json($oPaquete);
    }

    
    
    public function dashboardAntecedentes()
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oUser = auth()->user();
        $lnEmpresaSeleccionada = $oUser->EmpresaSeleccionada;
        set_time_limit(600);
        ini_set('memory_limit', '18192M');
        try{
            if(($lnEmpresaSeleccionada)||($lnEmpresaSeleccionada != 0)||($lnEmpresaSeleccionada != '0')){
                $fechaActual =  Carbon::now('America/La_Paz');
                Carbon::setLocale('es');
                $result = [];
                $oSincronizacionControlador = new SincronizacionSiatController();
                $fechaActual = $fechaActual->subMonths(4);
                for ($i = 0; $i < 3; $i++) {
                    //retrosediendo un mes
                    $fechaActual = $fechaActual->subMonths(-1);
                    $fechaMesesInicio = $fechaActual->copy();
                    $fechaMesFin = $fechaActual->copy();
                    
                    $fechaMesesInicio = $fechaMesesInicio->firstOfMonth();
                    $fechaMesesInicio  = $fechaMesesInicio->format('Y-m-d');
                    
                    $fechaMesFin = $fechaMesFin->endOfMonth();
                    $fechaMesFin = $fechaMesFin->format('Y-m-d');
                    $valor = [];
                    $facturas;
                    $anEstadosSiat = [1,4,3,5,2];
                    $facturas = $oSincronizacionControlador->buscarFacturaReturn($fechaMesesInicio, $fechaMesFin, 0);
                    $facturas=$facturas->values;
                    $cantidad;
                    if($facturas){
                        $cantidad = array_count_values(array_map(function($factura) {
                            return $factura->EstadoEnSiat;
                        }, $facturas));
                    }else{
                        $cantidad =0;
                    }
                    foreach ($anEstadosSiat as $item) {
                        switch($item){
                            case 1:
                                if(isset($cantidad[$item])){
                                    array_push($valor, ['Tipo' => 'Pendiente', 'Valor' => $cantidad[$item]]);
                                }else{
                                    array_push($valor, ['Tipo' => 'Pendiente', 'Valor' => 0]);
                                }
                                break;
                            case 2:
                                if(isset($cantidad[$item])){
                                    array_push($valor, ['Tipo' => 'Validas', 'Valor' => $cantidad[$item]]);
                                }else{
                                    array_push($valor, ['Tipo' => 'Validas', 'Valor' => 0]);
                                }
                                break;
                            case 3:
                                if(isset($cantidad[$item])){
                                    array_push($valor, ['Tipo' => 'Anuladas', 'Valor' => $cantidad[$item]]);
                                }else{
                                    array_push($valor, ['Tipo' => 'Anuladas', 'Valor' => 0]);
                                }
                                break;
                            case 4:
                                if(isset($cantidad[$item])){
                                    array_push($valor, ['Tipo' => 'Observadas', 'Valor' => $cantidad[$item]]);
                                }else{
                                    array_push($valor, ['Tipo' => 'Observadas', 'Valor' => 0]);
                                }
                                break;
                            case 5:
                                if(isset($cantidad[$item])){
                                    array_push($valor, ['Tipo' => 'Rechazadas', 'Valor' => $cantidad[$item]]);
                                }else{
                                    array_push($valor, ['Tipo' => 'Rechazadas', 'Valor' => 0]);
                                }
                                break;
                        }
                    }
                    $mes = $fechaActual->format('F');;
                     array_push($result, ['tnMes' => $mes, 'dato' => $valor]);
                }
                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "comando ejecutado";
                $oPaquete->message = "ejecusion sin inconvenientes";
                $oPaquete->values = $result;
        
            }else{
                $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "a ocurrido un error";
                $oPaquete->message = "el Usuario no esta Vinculado con la empresa";
                $oPaquete->values = null;
            }
            //notas de ventas realizadad por tres meces
            return response()->json($oPaquete);
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        
    }

}
