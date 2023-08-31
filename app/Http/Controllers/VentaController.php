<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Modelos\mPaquetePagoFacil;
use App\Models\PuntoVenta;
use App\Models\Cliente;
use App\Models\EMPRESAUSUARIOPERSONAL;
use App\Models\EmpresaSucursal;
use App\Models\Asociacion;
use App\Models\Producto;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use App\Models\TokenServicio;
use App\Models\claseSiat;
use Carbon\Carbon;

use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\UsuarioEmpresaController;

class VentaController extends Controller
{
    public $message;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            DB::beginTransaction(); // Iniciar la transacción
            $request->validate([
                'Venta'=> 'required',
                'VentaDetalle'=> 'required',
            ]);
            if(!$this->valudateVenta($request->Venta)){
                $oPaquete->error = 1;
                $oPaquete->status = 0;
                $oPaquete->messageSistema = "Error en el proceso";
                $oPaquete->message = $this->message;
            }else{
                if(!$this->valudateVentaDetalle($request->VentaDetalle)){
                    $oPaquete->error = 1;
                    $oPaquete->status = 0;
                    $oPaquete->messageSistema = "Error en el proceso";
                    $oPaquete->message = $this->message;
                }else{
                    $oInput = $this->valuesTVentaToVenta($request->Venta);
                    $oVenta = Venta::create($oInput);
                    foreach( $request->VentaDetalle as $detalle ){
                        $oInput = $this->valuesTVentaDetalleToVentaDetalle($detalle, $oVenta->Venta);
                        $oDetalle = VentaDetalle::create($oInput);

                    }

                    $oPaquete->error = 0;
                    $oPaquete->status = 1;
                    $oPaquete->messageSistema = "Comando ejecutado";
                    $oPaquete->message = "sin novedades";
                    $oPaquete->values = 1;
                }
            }
            DB::commit(); // Confirmar la transacción si todo va bien
            return response()->json($oPaquete);
        }catch (\Exception $e) {

            return response()->json($e->getMessage(), 500);
            DB::rollback(); // Revertir la transacción en caso de error
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    }

    public function valudateVenta($venta) {
        try {
            $venta['tnEmpresa'];
            $venta['tnSucursal'];
            $venta['tnPuntoVenta'];
            $venta['tnCliente'];
            $venta['tnTotal'];
            return true;
        } catch (\Exception $e) {
            $error_message = trans('messages.error_message');
            $this->message = $e->getMessage();
            return false;
        }
    }

    public function valudateVentaDetalle($ventaDetalles) {
        try {

            foreach( $ventaDetalles as $detalle ){
                $detalle['tcProducto'];
                $detalle['tcDescripcion'];
                $detalle['tnCantidad'];
                $detalle['tnUnidadMedida'];
                $detalle['tnPrecioUnitario'];
                $detalle['tnMontoDescuento'];
                $detalle['tnSubtotal'];
                $detalle['tcNumeroSerie'];
                $detalle['tcNumeroImei'];
            }
            return true;
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        //
    }

    public function ventasData()
    {

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $empresasController = new UsuarioEmpresaController();
            $oEmpresas = $empresasController->misEmpresasReturn();

            $oEmpresaSucursal;
            $oPuntoVenta;
            if($oEmpresas){

                $oEmpresaSucursal = EmpresaSucursal::where('Empresa', $oEmpresas[0]->Empresa)
                ->where('Estado',1)->get();
                if($oEmpresaSucursal){
                    $oPuntoVenta = PuntoVenta::where('Sucursal', $oEmpresaSucursal[0]->Sucursal)
                    ->where('Estado',1)->get();
                }
            }

            $sincSiatController = new SincronizacionSiatController();
            $oUnidad = $sincSiatController->SincronizacionSiatReturn( $oEmpresas[0]->Empresa,18);
            $oProducto = Producto::where('Empresa',$oEmpresas[0]->Empresa)
            ->where('Estado',1)->get();
            $oClientes = Cliente::where('Empresa',$oEmpresas[0]->Empresa)->get();

            $oPaquete->error = 0;
            $oPaquete->status = 1;
            $oPaquete->messageSistema = "sin errores";
            $oPaquete->message = 'comando ejecutado';
            $oPaquete->values = [$oEmpresas,$oEmpresaSucursal,$oPuntoVenta,$oUnidad,$oProducto,$oClientes];
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

    public function ventasDataEmpresa(Request $request)
    {

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $empresasController = new UsuarioEmpresaController();
            $oEmpresas = $empresasController->misEmpresasReturn();

            $oEmpresaSucursal;
            $oPuntoVenta;

            if($empresasController->esMiEmpresa($request->tnEmpresa)){
                $oEmpresaSucursal = EmpresaSucursal::where('Empresa', $request->tnEmpresa)
                ->where('Estado',1)->get();

                $oPuntoVenta = DB::table('PUNTOVENTA')
                    ->join('EMPRESASUCURSAL', 'PUNTOVENTA.Sucursal', '=', 'EMPRESASUCURSAL.Sucursal')
                    ->where('EMPRESASUCURSAL.Empresa', $request->tnEmpresa)
                    ->where('PUNTOVENTA.Estado', 1)
                    ->select('PUNTOVENTA.*')
                    ->get();
                $sincSiatController = new SincronizacionSiatController();
                $oUnidad = $sincSiatController->SincronizacionSiatReturn( $request->tnEmpresa,18);

                $oProducto = Producto::where('Empresa',$request->tnEmpresa)
                ->where('Estado',1)->get();
                $oClientes = Cliente::where('Empresa',$request->tnEmpresa)->get();
                $oPaquete->error = 0;
                $oPaquete->status = 1;
                $oPaquete->messageSistema = "sin errores";
                $oPaquete->message = 'comando ejecutado';
                $oPaquete->values = [$oEmpresas,$oEmpresaSucursal,$oPuntoVenta,$oUnidad,$oProducto,$oClientes];
            }else{
                $oPaquete->error = 1;
                $oPaquete->status = 0;
                $oPaquete->messageSistema = "errores al ejecutar comando";
                $oPaquete->message = 'la empresa no esta relacionada con el usuario';
                $oPaquete->values = null;
            }


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

    public function valuesTVentaToVenta($tVenta){
        return [
            'Empresa'=> $tVenta['tnEmpresa'],
            'Sucursal'=> $tVenta['tnSucursal'],
            'PuntoVenta'=> $tVenta['tnPuntoVenta'],
            'Cliente'=> $tVenta['tnCliente'],
            'Total'=> $tVenta['tnTotal'],
            'Fecha' => Carbon::now(),
            'Moneda' => 1,
        ];
    }

    public function valuesTVentaDetalleToVentaDetalle($tVentaDetalle, $tnVenta){
        return [
            'Venta' => $tnVenta,
            'Producto'=> $tVentaDetalle['tcProducto'],
            'Descripcion'=> $tVentaDetalle['tcDescripcion'],
            'Cantidad'=> $tVentaDetalle['tnCantidad'],
            'UnidadMedida'=> $tVentaDetalle['tnUnidadMedida'],
            'PrecioUnitario'=> $tVentaDetalle['tnPrecioUnitario'],
            'MontoDescuento'=> $tVentaDetalle['tnMontoDescuento'],
            'SubTotal'=> $tVentaDetalle['tnSubtotal'],
            'NumeroSerie'=> $tVentaDetalle['tcNumeroSerie'],
            'NumeroImei'=> $tVentaDetalle['tcNumeroImei'],
        ];
    }
}
