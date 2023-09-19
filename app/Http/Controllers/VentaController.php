<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\VentaFactura;
use App\Models\Descuento;
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
use App\Models\Movimineto;
use Carbon\Carbon;
use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\ConsultaController;

class VentaController extends Controller
{
    public $message;

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
                        // añadiendo un contro de movimientos de los productos en la venta
                        $oMovimineto = Movimineto::create([
                            'Empresa' => $oVenta->Empresa,
                            'Sucursal' => $oVenta->Sucursal,
                            'PuntoVenta' => $oVenta->PuntoVenta,
                            'Producto' =>$oDetalle->Producto,
                            'Cantidad' =>$oDetalle->Cantidad,
                            'TipoMovimiento' => '1',
                            'Motivo' => 'Venta: ' . $oVenta->Venta,
                            'Fecha' =>  Carbon::now(),
                        ]);
                    }
                    $oPaquete->error = 0;
                    $oPaquete->status = 1;
                    $oPaquete->messageSistema = "Comando ejecutado";
                    $oPaquete->message = "sin novedades";
                    $oPaquete->values = $oVenta->Venta;
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
            $venta['tnSubTotal'];
            $venta['tnTotalDesc'];
            $venta['tnTotalVenta'];
            $venta['tnGiftCard'];
            $venta['tnTotalPagar'];
            $venta['tnImporteIva'];
            $venta['tnMetodoPago'];


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
    public function show($tnVenta)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $oVenta = Venta::find($tnVenta);
            $oDetalle = VentaDetalle::Where('Venta',$tnVenta)->get();

            $oPaquete->error = 0;
            $oPaquete->status = 1;
            $oPaquete->message = "Comando ejecutado";
            $oPaquete->values = [$oVenta,$oDetalle];
            return response()->json($oPaquete);
        }catch (\Exception $e) {

            return response()->json($e->getMessage(), 500);
            DB::rollback(); // Revertir la transacción en caso de error

            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
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
                    $oPuntoVenta = DB::table('PUNTOVENTA')
                        ->join('EMPRESASUCURSAL', 'PUNTOVENTA.Sucursal', '=', 'EMPRESASUCURSAL.Sucursal')
                        ->where('EMPRESASUCURSAL.Empresa', $oEmpresas[0]->Empresa)
                        ->where('PUNTOVENTA.Estado', 1)
                        ->select('PUNTOVENTA.*')
                        ->get();
                }
            }
            $sincSiatController = new SincronizacionSiatController();
            $oUnidad = $sincSiatController->SincronizacionSiatReturn( $oEmpresas[0]->Empresa,18);
            $oProducto = Producto::where('Empresa',$oEmpresas[0]->Empresa)
            ->where('Estado',1)->get();
            $oClientes = Cliente::where('Empresa',$oEmpresas[0]->Empresa)->get();

            $consultaController = new ConsultaController();
            $oTipoDocumentoSector = $consultaController->empresaTipoDocumentoSector( $oEmpresas[0]->Empresa);

            $oTipoDocumentoIdentidad = $sincSiatController->SincronizacionSiatReturn( $oEmpresas[0]->Empresa,10);
            $oTipoDocumentoIdentidad = $oTipoDocumentoIdentidad->original->RespuestaListaParametricas->listaCodigos;
            $oTipoDocumentoIdentidad = array_map(function ($oTipoDocumentoIdentidad) {
            return [
                        'Tipo' => $oTipoDocumentoIdentidad->codigoClasificador,
                        'Nombre' => $oTipoDocumentoIdentidad->descripcion,
                    ];
                },
                $oTipoDocumentoIdentidad);

            $oDescuento = Descuento::where('Empresa',$oEmpresas[0]->Empresa)
            ->where('Estado',1)->get();

            $oPaquete->error = 0;
            $oPaquete->status = 1;
            $oPaquete->messageSistema = "sin errores";
            $oPaquete->message = 'comando ejecutado';
            $oPaquete->values = [$oEmpresas,$oEmpresaSucursal,$oPuntoVenta,$oUnidad,$oProducto,$oClientes,$oTipoDocumentoSector,$oTipoDocumentoIdentidad,$oDescuento];
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

                $consultaController = new ConsultaController();
                $oTipoDocumentoSector = $consultaController->empresaTipoDocumentoSector( $request->tnEmpresa);



                $oProducto = Producto::where('Empresa',$request->tnEmpresa)
                ->where('Estado',1)->get();
                $oClientes = Cliente::where('Empresa',$request->tnEmpresa)->get();

                $oTipoDocumentoIdentidad = $sincSiatController->SincronizacionSiatReturn( $request->tnEmpresa,10);
                $oTipoDocumentoIdentidad = $oTipoDocumentoIdentidad->original->RespuestaListaParametricas->listaCodigos;
                $oTipoDocumentoIdentidad = array_map(function ($oTipoDocumentoIdentidad) {
                return [
                            'Tipo' => $oTipoDocumentoIdentidad->codigoClasificador,
                            'Nombre' => $oTipoDocumentoIdentidad->descripcion,
                        ];
                    },
                    $oTipoDocumentoIdentidad);

                $oDescuento = Descuento::where('Empresa',$request->tnEmpresa)
                ->where('Estado',1)->get();



                $oPaquete->error = 0;
                $oPaquete->status = 1;
                $oPaquete->messageSistema = "sin errores";
                $oPaquete->message = 'comando ejecutado';
                $oPaquete->values = [$oEmpresas,$oEmpresaSucursal,$oPuntoVenta,$oUnidad,$oProducto,$oClientes,$oTipoDocumentoSector,$oTipoDocumentoIdentidad,$oDescuento];
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
        $horaActual = Carbon::now();
        if($tVenta['tnMetodoPago'] == 2){
            return [
                'Empresa'=> $tVenta['tnEmpresa'],
                'Sucursal'=> $tVenta['tnSucursal'],
                'PuntoVenta'=> $tVenta['tnPuntoVenta'],
                'Cliente'=> $tVenta['tnCliente'],
                'Fecha' =>  $horaActual,
                'Hora' => $horaActual->format('H:i:s'),
                'Moneda' => 1,
                'SubTotal'=> $tVenta['tnSubTotal'],
                'TotalDesc'=> $tVenta['tnTotalDesc'],
                'TotalVenta'=> $tVenta['tnTotalVenta'],
                'GiftCard'=> $tVenta['tnGiftCard'],
                'TotalPagar'=> $tVenta['tnTotalPagar'],
                'ImporteIva'=> $tVenta['tnImporteIva'],
                'MetodoPago'=> $tVenta['tnMetodoPago'],
                'Nro4Init'=> $tVenta['tnNro4Init'],
                'Nro4Fin'=> $tVenta['tnNro4Fin'],
            ];
        }else{
            return [
                'Empresa'=> $tVenta['tnEmpresa'],
                'Sucursal'=> $tVenta['tnSucursal'],
                'PuntoVenta'=> $tVenta['tnPuntoVenta'],
                'Cliente'=> $tVenta['tnCliente'],
                'Fecha' =>  $horaActual,
                'Hora' => $horaActual->format('H:i:s'),
                'Moneda' => 1,
                'SubTotal'=> $tVenta['tnSubTotal'],
                'TotalDesc'=> $tVenta['tnTotalDesc'],
                'TotalVenta'=> $tVenta['tnTotalVenta'],
                'GiftCard'=> $tVenta['tnGiftCard'],
                'TotalPagar'=> $tVenta['tnTotalPagar'],
                'ImporteIva'=> $tVenta['tnImporteIva'],
                'MetodoPago'=> $tVenta['tnMetodoPago'],
            ];
        }
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

    public function getVentas(Request $request){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $request->validate([
                'tnEmpresa'=> 'required',
                'tnSucursal'=> 'required',
                'tnPuntoVenta'=> 'required',
                'tdFInicio'=> 'required',
                'tdFFin'=> 'required',
            ]);

            $empresasController = new UsuarioEmpresaController();
            if($empresasController->esMiEmpresa($request->tnEmpresa)){
                $oVenta;
                if($request->tnSucursal == 0){

                    $oVenta = DB::table('VENTA as v')
                    ->select('v.*', 'em.Nombre as Empresa', 'es.Direccion as Sucursal','p.CodigoSucursal as PuntoVenta','c.RazonSocial as Cliente')
                    ->join('PUNTOVENTA as p', 'v.PuntoVenta', '=', 'p.PuntoVenta')
                    ->join('CLIENTE as c', 'v.Cliente', '=', 'c.CodigoCliente')
                    ->join('EMPRESA as em', 'v.Empresa', '=', 'em.Empresa')
                    ->join('EMPRESASUCURSAL as es', 'v.Sucursal', '=', 'es.Sucursal')
                    ->where('v.Empresa', $request->tnEmpresa)
                    ->whereBetween('v.Fecha', [$request->tdFInicio, $request->tdFFin])
                    ->get();

                }else{
                    if($request->tnPuntoVenta == 0){
                        $oVenta = DB::table('VENTA as v')
                        ->select('v.*', 'em.Nombre as Empresa', 'es.Direccion as Sucursal','p.CodigoSucursal as PuntoVenta','c.RazonSocial as Cliente')
                        ->join('PUNTOVENTA as p', 'v.PuntoVenta', '=', 'p.PuntoVenta')
                        ->join('EMPRESA as em', 'v.Empresa', '=', 'em.Empresa')
                        ->join('EMPRESASUCURSAL as es', 'v.Sucursal', '=', 'es.Sucursal')
                        ->join('CLIENTE as c', 'v.Cliente', '=', 'c.CodigoCliente')
                        ->where('v.Empresa', $request->tnEmpresa)
                        ->where('v.Sucursal',$request->tnSucursal)
                        ->whereBetween('v.Fecha', [$request->tdFInicio, $request->tdFFin])
                        ->get();


                    }else{

                        // queda pendiente lo del punto de venta
                        $oVenta = DB::table('VENTA as v')
                        ->select('v.*', 'em.Nombre as Empresa', 'es.Direccion as Sucursal','p.CodigoSucursal as PuntoVenta','c.RazonSocial as Cliente')
                        ->join('PUNTOVENTA as p', 'v.PuntoVenta', '=', 'p.PuntoVenta')
                        ->join('CLIENTE as c', 'v.Cliente', '=', 'c.CodigoCliente')
                        ->join('EMPRESA as em', 'v.Empresa', '=', 'em.Empresa')
                        ->join('EMPRESASUCURSAL as es', 'v.Sucursal', '=', 'es.Sucursal')
                        ->where('p.PuntoVenta', $request->tnPuntoVenta)
                        ->where('v.Empresa', $request->tnEmpresa)
                        ->where('v.Sucursal',$request->tnSucursal)
                        ->whereBetween('v.Fecha', [$request->tdFInicio, $request->tdFFin])
                        ->get();

                        /**
                         * $oVenta = Venta::where('Empresa',$request->tnEmpresa)
                         * ->where('Sucursal',$request->tnSucursal)
                         * ->where('PuntoVenta',$request->tnPuntoVenta)
                         * ->get();
                         */
                    }
                }

                $oPaquete->error = 0;
                $oPaquete->status = 1;
                $oPaquete->messageSistema = "sin errores";
                $oPaquete->message = 'comando ejecutado';
                $oPaquete->values = [$oVenta];
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

    public function crearFactura(Request $request){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $request->validate([
                'tnVenta'=> 'required',
                'tnTipoDocumentoSector'=> 'required',
                'tnCafc'=> 'required',
            ]);
            $oVenta = Venta::find($request->tnVenta);
            if($oVenta){

                $oFactura = VentaFactura::where('Venta',$oVenta->Venta)->first();
                if($oFactura){
                    $oPaquete->error = 1; // Indicar que hubo un error
                    $oPaquete->status = 0; // Indicar que hubo un error
                    $oPaquete->messageSistema = "Error en el proceso";
                    $oPaquete->message = 'la nota de venta ya cuenta con una Factura ';
                }else{
                    $oCliente = Cliente::where('CodigoCliente',$oVenta->Cliente)->first();
                    $lnCodexcepci = $this->validarNitCliente($oCliente);
                    // Realiza las acciones necesarias con $lnCodexcepci
                    $lcNumeroTarjeta = null;
                    if($oVenta->MetodoPago === 2 ){
                        $lcNumeroTarjeta = $oVenta->Nro4Init.'XXXXXXXX'.$oVenta->Nro4Fin;
                    }

                    $horaActual = Carbon::now();
                    $oSucursal = EmpresaSucursal::where('Sucursal',$oVenta->Sucursal)->first();
                    $oPuntoVenta = PuntoVenta::where('PuntoVenta',$oVenta->PuntoVenta)->first();

                    if($request->tdFechaEmision === null){
                        $request->tdFechaEmision = $horaActual->format('Y-m-d');
                    }
                    if($request->tdHoraEmision === null){
                        $request->tdHoraEmision = $horaActual->format('H:i:s');
                    }

                    $data = [
                        'Venta' => $oVenta->Venta,
                        'NumeroFactura' => 0,//por definir
                        'NitEmison' => $oVenta->empresa->Nit,
                        'FechaEmision' => $request->tdFechaEmision,
                        'HoraEmision' => $request->tdHoraEmision,
                        'ValidoSin' => 1,
                        'Moneda' => $oVenta->Moneda,
                        'CodigoSucursal' => $oSucursal->CodigoSucursal,
                        'CodigoPuntoVenta' => $oPuntoVenta->CodigoPuntoVenta,
                        'TipoDocumentoSector' => $request->tnTipoDocumentoSector,
                        'CodigoCliente' => $oCliente->CodigoCliente,
                        'DocumentoIdentidad' => $oCliente->TipoDocumento,
                        'NumeroDocumento' => $oCliente->Documento,
                        'Complemento' => $oCliente->Complemento,
                        'Codexcepci' => $lnCodexcepci,
                        'RazonSocial' => $oCliente->RazonSocial,
                        'Email' => $oCliente->Email,
                        'MetodoPago' => $oVenta->MetodoPago,
                        'NumeroTarjeta' => $lcNumeroTarjeta,
                        'Cafc' => $request->tnCafc,
                        'GiftCard' => $oVenta->GiftCard,
                        'FechaCreacion' => $horaActual->format('Y-m-d'),
                        'HoraCreacion' => $horaActual->format('H:i:s'),
                        'EstadoSiat' => 1,
                    ];

                    $ventaFactura = VentaFactura::create($data);

                    $oPaquete->error = 0;
                    $oPaquete->status = 1;
                    $oPaquete->message ="Comando ejecutado";
                    $oPaquete->values = $ventaFactura->Venta;

                }
            }else{
                $oPaquete->error = 1; // Indicar que hubo un error
                $oPaquete->status = 0; // Indicar que hubo un error
                $oPaquete->messageSistema = "Error en el proceso";
                $oPaquete->message = 'error el numero de venta no existe';
            }
            return response()->json($oPaquete);

        }catch (\Exception $e) {

            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    }

    public function validarNitCliente(Cliente $oCliente){
        $lnCodigoExcepcion;
        if($oCliente->TipoDocumento === 2){
            //aqui se lo manda a validar a impuestos
            //si es valido retorna 0 caso contrario retorna 1
            $lnCodigoExcepcion = 1;
        }else{
            $lnCodigoExcepcion = 0;
        }
        return $lnCodigoExcepcion;
    }

    public function emitirSiat(Request $request){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $oPaquete->error = 0;
            $oPaquete->status = 1;
            $oPaquete->message ="Comando ejecutado";
            $oPaquete->values = 1;
            return response()->json($oPaquete);

        }catch (\Exception $e) {

            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    }

    public function estadoSiat(Request $request){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $oPaquete->error = 0;
            $oPaquete->status = 1;
            $oPaquete->message = "Comando ejecutado";
            $oPaquete->values = 1;
            return response()->json($oPaquete);
        }catch (\Exception $e) {

            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    }

}

