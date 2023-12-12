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
use App\Models\EmpresaUsuarioPersonal;
use App\Models\EmpresaSucursal;
use App\Models\Asociacion;
use App\Models\Producto;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use App\Models\TokenServicio;
use App\Models\claseSiat;
use App\Models\Movimineto;
use App\Models\UnidadMedida;
use App\Models\UnidadMedidaEmpresa;
use App\Models\TipoCliente;
use Carbon\Carbon;
use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\ConsultaController;
use App\Models\TipoDocumentoIdentidad;
use App\Models\Credito;

class VentaController extends Controller
{
    public $message;
    
    public function index(){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        
        $loUser = auth()->user();
        $lnEmpresaSeleccionada =$loUser->EmpresaSeleccionada;
        if(($lnEmpresaSeleccionada === 0) ||($lnEmpresaSeleccionada ==='0')){
            $oPaquete->error = 1;
            $oPaquete->status = 2;
            $oPaquete->messageSistema = "empresa no encontrada";
            $oPaquete->message = 'El Ususario debe Seleccionar una empresa para poder Acceder a la Venta';
            $oPaquete->values = null;    
        }else{
            $empresasController = new UsuarioEmpresaController();
            $loEmpresas = $empresasController->misEmpresasReturn();
         
            $loSucursales = EmpresaSucursal::where('Empresa',$lnEmpresaSeleccionada)->get();
            
            $consultaController = new ConsultaController();
            $loPuntoVenta = $consultaController->PuntoVentaReturn($lnEmpresaSeleccionada);
            
            $oTipoDocumentoSector = $consultaController->empresaTipoDocumentoSector( $lnEmpresaSeleccionada);
            
            
            $oPaquete->error = 0;
            $oPaquete->status = 1;
            $oPaquete->messageSistema = "Comando ejecutado";
            $oPaquete->message = 'el comando se ejecuto correctamente.';
            $oPaquete->values = [
                $loEmpresas,
                $loSucursales,
                $loPuntoVenta,
                $lnEmpresaSeleccionada,
                $oTipoDocumentoSector,
            ];
            
        }
        return response()->json($oPaquete);
    }
    /**
     * Store a newly created resource in storage.
     */
    
    public function create() {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try {
            $oUser = auth()->user();
            $lnEmpresaSeleccionada = $oUser->EmpresaSeleccionada;
            
            if ($lnEmpresaSeleccionada === 0 || $lnEmpresaSeleccionada === '0') {
                $oPaquete->error = 1;
                $oPaquete->status = 0;
                $oPaquete->messageSistema = "Error en el proceso";
                $oPaquete->message = 'El Usuario debe seleccionar una empresa para acceder al m�todo.';
            } else {
                $empresasController = app(UsuarioEmpresaController::class);
                $oEmpresas = $empresasController->misEmpresasReturn();
                
                $oUnidad = DB::table('UNIDADMEDIDA as um')
                ->join('UNIDADMEDIDAEMPRESA as ume', 'ume.Codigo', '=', 'um.Codigo')
                ->where('ume.Empresa', $lnEmpresaSeleccionada)
                ->select('um.*', 'ume.Empresa')
                ->get();
                
                $oProducto = Producto::where('Empresa', $lnEmpresaSeleccionada)
                ->where('Estado', 1)
                ->get();
                
                $oClientes = Cliente::where('Empresa', $lnEmpresaSeleccionada)
                ->get();
                
                $consultaController = app(ConsultaController::class);
                $oTipoDocumentoSector = $consultaController->empresaTipoDocumentoSector($lnEmpresaSeleccionada);
                
                $oTipoDocumentoIdentidad = TipoDocumentoIdentidad::select('TipoDocumentoIdentidad as Tipo', 'Nombre')
                ->get();
                
                $oDescuento = Descuento::where('Empresa', $lnEmpresaSeleccionada)
                ->where('Estado', 1)
                ->get();
                
                $oTipoCliente = TipoCliente::where('Empresa', $lnEmpresaSeleccionada)
                ->get();
                
                $oPaquete->error = 0;
                $oPaquete->status = 1;
                $oPaquete->messageSistema = "sin errores";
                $oPaquete->message = 'comando ejecutado';
                $oPaquete->values = [
                    $oEmpresas,
                    $oUnidad,
                    $oProducto,
                    $oClientes,
                    $oTipoDocumentoSector,
                    $oTipoDocumentoIdentidad,
                    $oDescuento,
                    $lnEmpresaSeleccionada,
                    $oTipoCliente,
                ];
            }
        } catch (\Exception $e) {
            DB::rollback();
            $oPaquete->error = 1;
            $oPaquete->status = 0;
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage();
            return response()->json($oPaquete, 500);
        }
        
        return response()->json($oPaquete);
    }
    
    
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
                            'Fecha' =>  Carbon::now('America/La_Paz'),
                        ]);
                    }    
                    /*
                    $oCredito;
                    $oUser = auth()->user();
                    if($oVenta->TipoPago === 1){
                        $oCredito = Credito::create([
                            'Empresa',
                            'Sucursal' => $oVenta->Sucursal,
                            'PuntoVenta' => $oVenta->PuntoVenta,
                            'Producto' =>$oDetalle->Producto,
                            'Cantidad' =>$oDetalle->Cantidad,
                            'TipoMovimiento' => '1',
                            'Motivo' => 'Venta: ' . $oVenta->Venta,
                            'Fecha',
                            
                            
                            'Interes'=> 1,//identificador del tipo de interes
                            'Empresa' => $oVenta->Empresa,
                            'Cliente' => $oVenta->Cliente,
                            'Cuotas',
                            'CuotaMonto',
                            'CuotaInicial',
                            'Dias',
                            'TasaInteres' ,
                            'Estado' => 1,
                            'User' => $oUser->Ususario,
                            'UserHr' =>  Carbon::now('America/La_Paz'),
                            
                        ]);
                    }
                    */
                    
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
            $oVentaFactura = VentaFactura::find($tnVenta);
            
            $oDetalle = VentaDetalle::Where('Venta',$tnVenta)->get();
            $oCliente = Cliente::find($oVenta->Cliente);
            
            $oPaquete->error = 0;
            $oPaquete->status = 1;
            $oPaquete->message = "Comando ejecutado";
            $oPaquete->values = [$oVenta, $oDetalle, $oCliente, $oVentaFactura];
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

    public function ventasData()
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $empresasController = new UsuarioEmpresaController();
            $oEmpresas = $empresasController->misEmpresasReturn();
            $oUser = auth()->user();
            
            $lnEmpresaSeleccionada =$oUser->EmpresaSeleccionada;
            if(($lnEmpresaSeleccionada === 0)||($lnEmpresaSeleccionada === '0')){
                $lnEmpresaSeleccionada = $oEmpresas[0]->Empresa;
            }

            $oUnidad = DB::table('UNIDADMEDIDA as um')
            ->join('UNIDADMEDIDAEMPRESA as ume', 'ume.Codigo', '=', 'um.Codigo')
            ->where('ume.Empresa', $lnEmpresaSeleccionada)
            ->select('um.*', 'ume.Empresa')
            ->get();
            
            $oProducto = Producto::where('Empresa',$lnEmpresaSeleccionada)
            ->where('Estado',1)->get();

            $oClientes = Cliente::where('Empresa',$lnEmpresaSeleccionada)
            ->get();

            $consultaController = new ConsultaController();
            $oTipoDocumentoSector = $consultaController->empresaTipoDocumentoSector( $lnEmpresaSeleccionada);

            $oTipoDocumentoIdentidad = TipoDocumentoIdentidad::select('TipoDocumentoIdentidad as Tipo', 'Nombre')
            ->get();

            $sqlDescuento="Select d.*
                FROM DESCUENTO as d,PARAMETROS as p
                WHERE (p.Descuento= d.Descuento) or (d.Empresa = ".$lnEmpresaSeleccionada.")";
            $oDescuentos= DB::select($sqlDescuento);
            
            $lcSQLTipoCliente= "SELECT
            TIPOCLIENTE.*
            FROM
            TIPOCLIENTE,PARAMETROS
            WHERE
            (TIPOCLIENTE.TipoCliente = PARAMETROS.TipoCliente) or (TIPOCLIENTE.Empresa = ".$lnEmpresaSeleccionada.")";
            $oTipoCliente= DB::select($lcSQLTipoCliente);
            
            $oPaquete->error = 0;
            $oPaquete->status = 1;
            $oPaquete->messageSistema = "sin errores";
            $oPaquete->message = 'comando ejecutado';
            $oPaquete->values = [
                $oEmpresas,
                $oUnidad,
                $oProducto,
                $oClientes,
                $oTipoDocumentoSector,
                $oTipoDocumentoIdentidad,
                $oDescuentos,
                $lnEmpresaSeleccionada,
                $oTipoCliente,
            ];
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
    
    //metodo dublicado al vendasData
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
                $oPaquete->values = [
                    $oEmpresas,
                    $oEmpresaSucursal,
                    $oPuntoVenta,
                    $oUnidad,
                    $oProducto,
                    $oClientes,
                    $oTipoDocumentoSector,
                    $oTipoDocumentoIdentidad,
                    $oDescuento

                ];
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
        $horaActual = Carbon::now('America/La_Paz');
        $oUser = auth()->user();
        $oEmpresaUsuarioPersonal = EmpresaUsuarioPersonal::where('Usuario',$oUser->Usuario)
                                    ->where('Empresa',$oUser->EmpresaSeleccionada)
                                    ->where('CodigoAmbiente',$oUser->CodigoAmbiente)
                                    ->where('Estado',1)->first();
                        
            $loCliente= Cliente::where('CodigoCliente', strval($tVenta['tnCliente']))
                    ->where('Empresa',strval($oUser->EmpresaSeleccionada))->first();                    
                    
         if($tVenta['tnMetodoPago'] == 2){
            return [
                'Empresa'=> $tVenta['tnEmpresa'],
                'Sucursal'=> $oEmpresaUsuarioPersonal->Sucursal,
                'PuntoVenta'=> $oEmpresaUsuarioPersonal->PuntoVenta,// viendo la posibilidad de anexarlo a empresa usuario personal 
                'Cliente'=> $loCliente->Cliente,
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
                //'TipoPago'=> $tVenta['tnTipoPago'],
            ];
        }else{
            return [
                'Empresa'=> $tVenta['tnEmpresa'],
                'Sucursal'=>  $oEmpresaUsuarioPersonal->Sucursal,
                'PuntoVenta'=> $oEmpresaUsuarioPersonal->PuntoVenta,// viendo la posibilidad de anexarlo a empresa usuario personal
                'Cliente'=> $loCliente->Cliente,
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
                $tdFInicio = substr($request->tdFInicio, 0, 10);
                $tdFFin = substr($request->tdFFin, 0, 10);
                
                
                $lcSQL = "SELECT v.*, em.Nombre AS Empresa, s.CodigoSucursal AS Sucursal, c.CodigoCliente AS Cliente,pv.CodigoPuntoVenta
                            FROM VENTA AS v
                            JOIN EMPRESA AS em ON v.Empresa = em.Empresa
                            JOIN SUCURSAL AS s ON v.Sucursal = s.Sucursal
                            JOIN CLIENTE AS c ON v.Cliente = c.Cliente
                            JOIN PUNTOVENTA AS pv ON v.PuntoVenta = pv.PuntoVenta
                            WHERE v.Empresa = ".$request->tnEmpresa." 
                            AND v.Fecha BETWEEN '".$tdFInicio."' AND '".$tdFFin."' 
                            AND NOT EXISTS (
                                SELECT VENTAFACTURA.Venta
                                FROM VENTAFACTURA
                                WHERE VENTAFACTURA.Venta = v.Venta
                            )";
                                
                if(($request->tnSucursal != 0)|| ($request->tnSucursal != '0')){
                   $lcSQL = $lcSQL."AND s.Sucursal = ".$request->tnSucursal." ";
                    if(($request->tnPuntoVenta != 0) || ($request->tnPuntoVenta != '0')){
                        $lcSQL = $lcSQL."AND p.PuntoVenta = ".$request->tnPuntoVenta." ";
                    }
                }

                $oVenta= DB::select($lcSQL);
                
                $oPaquete->error = 0;
                $oPaquete->status = 1;
                $oPaquete->messageSistema = "sin errores";
                $oPaquete->message = 'comando ejecutado';
                $oPaquete->values = $oVenta;
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

    public function getFacturas(Request $request){
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
                
                $tdFInicio = substr($request->tdFInicio, 0, 10);
                $tdFFin = substr($request->tdFFin, 0, 10);
                
                
                $lcSQL = "SELECT v.*, vf.* ,em.Nombre AS Empresa, s.CodigoSucursal AS Sucursal, c.CodigoCliente AS Cliente,pv.CodigoPuntoVenta
                            FROM VENTA AS v
                            JOIN EMPRESA AS em ON v.Empresa = em.Empresa
                            JOIN SUCURSAL AS s ON v.Sucursal = s.Sucursal
                            JOIN CLIENTE AS c ON v.Cliente = c.Cliente
                            JOIN PUNTOVENTA AS pv ON v.PuntoVenta = pv.PuntoVenta
                            JOIN VENTAFACTURA AS vf ON v.Venta = vf.Venta
                            WHERE v.Empresa = ".$request->tnEmpresa."
                            AND v.Fecha BETWEEN '".$tdFInicio."' AND '".$tdFFin."'";
                
                if(($request->tnSucursal != 0)|| ($request->tnSucursal != '0')){
                    $lcSQL = $lcSQL."AND s.Sucursal = ".$request->tnSucursal." ";
                    if(($request->tnPuntoVenta != 0) || ($request->tnPuntoVenta != '0')){
                        $lcSQL = $lcSQL."AND p.PuntoVenta = ".$request->tnPuntoVenta." ";
                    }
                }
                
                $oVenta= DB::select($lcSQL);
                               
                $oPaquete->error = 0;
                $oPaquete->status = 1;
                $oPaquete->messageSistema = "sin errores";
                $oPaquete->message = 'comando ejecutado';
                $oPaquete->values = $oVenta;
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
                    $oCliente = Cliente::where('Cliente',$oVenta->Cliente)->first();
                    $lcNombreRazonSocial;
                    $lcNit;
                    $lnCodexcepci=0;
                    
                    $lcNombreRazonSocial = $oCliente->RazonSocial;
                    $lcNit = $oCliente->Documento;
                    $lnCodexcepci = $this->validarNitCliente($oCliente);
                    
                    // Realiza las acciones necesarias con $lnCodexcepci
                    $lcNumeroTarjeta = null;
                    if($oVenta->MetodoPago === 2 ){
                        $lcNumeroTarjeta = $oVenta->Nro4Init.'XXXXXXXX'.$oVenta->Nro4Fin;
                    }

                    $horaActual = Carbon::now('America/La_Paz');
                    
                    $oSucursal = EmpresaSucursal::where('Sucursal',$oVenta->Sucursal)->first();
                    
                    $oPuntoVenta = PuntoVenta::where('PuntoVenta',$oVenta->PuntoVenta)->first();
                    
                    if($request->tdFechaEmision === null){
                        $request->tdFechaEmision = $horaActual->format('Y-m-d');
                    }
                    if($request->tdHoraEmision === null){
                        $request->tdHoraEmision = $horaActual->format('H:i:s');
                    }
                    $email;
                    if($oCliente->Email){
                        $email = $oCliente->Email;
                    }else{
                        $email = 'noreply@pagofacil.com.bo';
                    }
                    
                    
                    $data = [
                        'Venta' => $oVenta->Venta,
                        'NumeroFactura' => (10000+$oVenta->Venta),//por definir inicia 2000
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
                        'NumeroDocumento' => $lcNit,
                        'Complemento' => $oCliente->Complemento,
                        'Codexcepci' => $lnCodexcepci,
                        'RazonSocial' => $lcNombreRazonSocial,
                        'Email' => $email,
                        
                        'MetodoPago' => $oVenta->MetodoPago,
                        'NumeroTarjeta' => $lcNumeroTarjeta,
                        'GiftCard' => $oVenta->GiftCard,
                        'FechaCreacion' => $horaActual->format('Y-m-d'),
                        'HoraCreacion' => $horaActual->format('H:i:s'),
                        'EstadoSiat' => 1,
                    ];
                    
                    $ventaFactura = VentaFactura::create($data);
                    $oPaquete->error = 0;
                    $oPaquete->status = 1;
                    $oPaquete->message ="Comando ejecutado";
                    $oPaquete->values = $oVenta->Venta;

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

    public function validarNitCliente(Cliente $toCliente){
        $lnCodigoExcepcion;
        if(($toCliente->TipoDocumento === 5)||($toCliente->TipoDocumento === "5")){
            //aqui se lo manda a validar a impuestos
            //si es valido retorna 0 caso contrario retorna 1
            $lnEmpresa = intval($toCliente->Empresa);
            $SincronizacionSiatController = new SincronizacionSiatController();
            $result = $SincronizacionSiatController->ValidacionNitReturn($lnEmpresa,$toCliente->Documento );
            
            if($result){
                $texto = (string) $result;
                $inicio = strpos($texto, '{');
                $result = substr($texto, $inicio);
                $pattern_activo = "/ACTIVO/";
                $pattern_inactivo = "/INACTIVO/";
                
                if (preg_match($pattern_inactivo, $result)) {
                    $lnCodigoExcepcion = 0;
                } elseif (preg_match($pattern_activo, $result)) {
                    $lnCodigoExcepcion = 1;
                } else {
                    // Si la cadena no contiene "ACTIVO" ni "INACTIVO", puedes manejarlo como desees.
                    // En este ejemplo, estamos asignando un valor predeterminado de -1.
                    $lnCodigoExcepcion = -1;
                }
                $lnCodigoExcepcion = 0; 
            }else{
                $lnCodigoExcepcion = 0;
            }
        }else{  
            $lnCodigoExcepcion = 0;
        }
        return $lnCodigoExcepcion;
    }


}

