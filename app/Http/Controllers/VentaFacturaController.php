<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Modelos\mPaquetePagoFacil;
use Carbon\Carbon;
use App\Models\VentaFactura;
use App\Models\Venta;
use App\Models\Empresa;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\EmpresaSucursal;
use App\Models\Asociacion;
use App\Models\PuntoVenta;
use App\Models\VentaDetalle;
use App\Models\Producto;

use App\Soporte\Error;
use App\Http\Controllers\SincronizacionSiatController; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\SMSAdjunto;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;



class VentaFacturaController extends Controller
{
    public $gnCodigoAmbiente =2;//usando credecinales de prueva para la emision de facturas
    
    //emision Multisector
    public function emitirSiat(Request $request){
        
        try{
            
            $request->validate([
                'tnVentaFactura'=> 'required',
            ]);
            
            $tnVentaFactura = $request->tnVentaFactura;
            Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Venta a emitir " ,  $tnVentaFactura );
             
            
            $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
            DB::transaction(function () use (&$tnVentaFactura, &$oPaquete) {
                $loFactura = VentaFactura::find($tnVentaFactura);
                $loVenta = Venta::find($loFactura->Venta);
                $oUser = auth()->user();
                Error::guardarLog(true,"VentaFactura_.$tnVentaFactura"
                    ,"Datos del usuario" ,  $oUser
                    ,"Datos de la venta" ,  $loVenta
                    ,"Datos para la factura",$loFactura);
                
                if (is_null($loFactura->Cuf))
                {
                    $lcEmpresa;
                    $lcPuntoVenta;
                    $lcFactura;
                    $lcFacturaDetalle;
                    $lcFacturaSector;
                    $lcFacturaDetalleSector;
                    $SincronizacionSiatController = new SincronizacionSiatController();
                    
                    /// tcEmpresa
                    $laConsultaCredenciales = DB::table('ASOCIACIONTIPODOCUMENTOSECTOR as atds')
                    ->join('ASOCIACION as a', 'a.Asociacion', '=', 'atds.Asociacion')
                    ->where( 'atds.TipoDocumentoSector', $loFactura->TipoDocumentoSector)
                    ->where('a.CodigoAmbiente', $oUser->CodigoAmbiente)
                    ->where( 'a.Empresa', $loVenta->Empresa)
                    ->select('a.*', 'atds.*')
                    ->distinct()
                    ->get();
                    
                    $lcCredencial ="";
                    $lcVacio = "";
                    $lcActividadEconomica ="";
                    $lcTipoDocumentoSector;
                    $lcCodigoproducto = "";
                    $oPaquete->message = 'Error inesperado al cargar los datos de la empresa'; // Agregar detalles del error
                    
                    if(count($laConsultaCredenciales)>0)
                    {
                        $lcCredencial=$laConsultaCredenciales[0]->AsociacionCredencial;
                        $lcTipoDocumentoSector=$laConsultaCredenciales[0]->TipoDocumentoSector;
                        
                        $lcEmpresa=$lcCredencial
                                    ."ß".$lcVacio
                                    ."ß".$lcActividadEconomica
                                    ."ß".$lcTipoDocumentoSector
                                    ."ß".$lcCodigoproducto."þ";

                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos y credenciales de la empresa con las que se emitira la fafctura" ,  $laConsultaCredenciales[0] );
                    }else{
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos de la Empresa " ,  "no se Obtuvo los datos de la empresa" );
                        return  0;
                    }
                    
                    
                    /// tcPuntoVenta
                    $lcPuntoVenta = 
                    $loFactura->CodigoSucursal
                    ."ß".$loFactura->CodigoPuntoVenta."ßþ";
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Codigo Sucursal" , $loFactura->CodigoSucursal,  "Codigo CodigoPuntoVenta" , $loFactura->CodigoPuntoVenta  );
                    
                    $loEmpresa = Empresa::find($loVenta->Empresa);
                    
                    $loCiudad = Ciudad::find($loVenta->Ciudad);
                    
                    $lcNitEmisor ;                     //269322027
                    $lcRazonSocialEmisor ;             //ßAGROINDUSTRIA NAYADE S.A.
                    $lcMunicipio;                      //ß Andres Ibaez
                    if($oUser->CodigoAmbiente === 2){
                        $lcNitEmisor = '3879294013';
                        $lcRazonSocialEmisor ='Cesar Corvera Murakami';
                        $lcMunicipio = 'Santa cruz';
                    }else{
                        $lcNitEmisor = $loEmpresa->Nit;
                        $lcRazonSocialEmisor = $loEmpresa->RazonSocial;
                        $lcMunicipio = $loCiudad->Nombre;
                    }
                    $lcTelefono = $loEmpresa->Telefono;                     //ß 3435780ß 101ß 0
                    $lcNumeroFactura = $loFactura->NumeroFactura;                  //ß Av.Cumavi N s/n Barrio Bolivar U.V
                    $lcCodigoSucursal = $loFactura->CodigoSucursal;                 //ß 0
                    $lcDireccion = $loEmpresa->Direccion;                //ß 2023-06-19
                    $lcCodigoPuntoVenta = $loFactura->CodigoPuntoVenta;               //ß 08:41:29.000
                    
                    $fecha = Carbon::now('America/La_Paz');
                    
                    $lcFechaEmision = $fecha->format('Y-m-d');              //ß Urgel Coronado Chela
                    $lcHoraEmision = $fecha->format("H:i:s.v");             //ß4
                    //datos del cliente 
                    
                    
                    $lcCodigoCliente = $loFactura->CodigoCliente;
                    $loCliente = Cliente::where('CodigoCliente',$lcCodigoCliente)->first();
                    $lcNombreRazonSocial = $loFactura->RazonSocial;            //ß 2001
                    $lcCodigoTipoDocumentoIdentidad = $loFactura->DocumentoIdentidad;   //ß
                    $lcNumeroDocumento = $loFactura->NumeroDocumento;                //ß 2001
                    $lcComplemento = $loFactura->Complemento;
                    
                    $lcCodigoExcepcion;            //ß 0.00
                    if($lcCodigoTipoDocumentoIdentidad== 5)
                    {
                        $lcCodigoExcepcion="1";
                        $result = $SincronizacionSiatController->ValidacionNitReturn($loVenta->Empresa,$loFactura->NumeroDocumento);
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos de Validacion de Nit " ,  $result );
                        
                        if($result){
                            $texto = (string) $result;
                            $inicio = strpos($texto, '{');
                            $result = substr($texto, $inicio);
                            $pattern_activo = "/ACTIVO/";
                            $pattern_inactivo = "/INACTIVO/";
                            if (preg_match($pattern_inactivo, $result)) {
                                $lcCodigoExcepcion = "0";
                            } else{
                                if (preg_match($pattern_activo, $result)) {
                                    $lcCodigoExcepcion = "1";
                                } else {
                                    $lcCodigoExcepcion = "0";
                                }
                            }
                        }else{
                            $lcCodigoExcepcion = "0";
                        }
                    }else{
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Tipo de Documento identidad: ".$loFactura->DocumentoIdentidad ,  "Numero de Documento: ".$loFactura->NumeroDocumento );
                        $lcCodigoExcepcion="0";
                    }
                    
                    
                    
                    
                    //metodo de pago 
                    $lcCodigoMetodoPago = $loFactura->MetodoPago;               //ß 6
                    $lcNumeroTarjeta = $loFactura->NumeroTarjeta;              //ß
                    $lcMontoTotal = $loVenta->TotalPagar;             //ß 500.00
                    $lcMontoTotalSujetoIva = $loVenta->ImporteIva;            //ß 500.00
                    
                    $lcCodigoMoneda = $loFactura->Moneda;           //ß 1
                    $lcMontoGiftCard = $loVenta->GiftCard;                  //ß 1
                    $lcDescuentoAdicional = $loVenta->TotalDesc;             //ß 0.00
                    $lcCorreoCliente;
                    if($loCliente->Email){
                        $lcCorreoCliente = $loCliente->Email;
                    }else{
                        $lcCorreoCliente = 'noreply@pagofacil.com.bo';
                    }
                    $lcTipoCambio = 1;
                     $lcSector = $loFactura->TipoDocumentoSector;          //
                     $lcUsuario = $oUser->Usuario.' '.$oUser->Nombre.' '.$oUser->Apellido;           //
                    
                    
                    
                    $lcFactura = $lcNitEmisor              //269322027
                    ."ß".$lcRazonSocialEmisor              //ßAGROINDUSTRIA NAYADE S.A.
                    ."ß".$lcMunicipio                      //ß Andres Ibaez
                    ."ß".$lcTelefono                       //ß 3435780ß 101ß 0
                    ."ß".$lcNumeroFactura                  //ß Av.Cumavi N s/n Barrio Bolivar U.V
                    ."ß".$lcCodigoSucursal                 //ß 0
                    ."ß".$lcDireccion                      //ß 2023-06-19
                    ."ß".$lcCodigoPuntoVenta               //ß 08:41:29.000
                    ."ß".$lcFechaEmision                   //ß Urgel Coronado Chela
                    ."ß".$lcHoraEmision                    //ß4
                    ."ß".$lcNombreRazonSocial              //ß 2001
                    ."ß".$lcCodigoTipoDocumentoIdentidad   //ß
                    ."ß".$lcNumeroDocumento                //ß 2001
                    ."ß".$lcComplemento
                    ."ß".$lcCodigoCliente
                    ."ß".$lcCodigoMetodoPago               //ß 6
                    ."ß".$lcNumeroTarjeta                  //ß
                    ."ß".$lcMontoTotal                     //ß 500.00
                    ."ß".$lcMontoTotalSujetoIva            //ß 500.00
                    ."ß".$lcCodigoMoneda                   //ß 1
                    ."ß".$lcTipoCambio
                    ."ß".$lcMontoGiftCard                  //ß 1
                    ."ß".$lcDescuentoAdicional             //ß 0.00
                    ."ß".$lcCodigoExcepcion                //ß 0.00
                    ."ß".$lcUsuario                        //ß 0
                    ."ß".$lcSector                         //ß Cesar Corvera Murakami
                    ."ß".$lcCorreoCliente."þ";               //ß 24ß þ
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos de la Factura a enviar: ".$lcFactura);
                    
                    
                    /// tc Factura Detalle
                    $laConsultaDetalles = DB::table('VENTADETALLE as vd')
                    ->join('PRODUCTO as p', 'p.CodigoProductoOrigen', '=', 'vd.Producto')
                    ->select('vd.*', 'p.*')
                    ->where('p.Empresa', $loVenta->Empresa)
                    ->where('vd.Venta', $loVenta->Venta)
                    ->get();
                    
                    $lcFacturaDetalle="";
                    foreach ($laConsultaDetalles as $oVentaDetalle) {
                        $tcCodigoProducto= $oVentaDetalle->CodigoProductoOrigen.'¦'.$oVentaDetalle->ActividadEconomica.'¦'.$oVentaDetalle->CatalogoImpuestos;
                        $tcDescripcion= $oVentaDetalle->Nombre;
                        $tnCantidad= $oVentaDetalle->Cantidad;
                        $tnUnidadMedida= $oVentaDetalle->UnidadMedida;
                        $tnPrecioUnitario= $oVentaDetalle->PrecioUnitario;
                        $tnMontoDescuento= $oVentaDetalle->MontoDescuento;
                        $tnSubTotal= $oVentaDetalle->SubTotal ;
                        $tnNumeroSerie= $oVentaDetalle->NumeroSerie;
                        $tcNumeroImei= $oVentaDetalle->NumeroImei;
                        
                        $lcFacturaDetalle.=
                        $tcCodigoProducto
                        ."ß".mb_convert_encoding($tcDescripcion, 'latin1')
                        ."ß".$tnCantidad.
                        "ß".$tnUnidadMedida.
                        "ß".$tnPrecioUnitario.
                        "ß".$tnMontoDescuento.
                        "ß".$tnSubTotal.
                        "ß".$tnNumeroSerie.
                        "ß".$tcNumeroImei."þ";                        
                    }
                    
                    // tc Factura Sector
                    $lcFacturaSector = "";
                    // tc Factura Detalle Sector
                    $lcFacturaDetalleSector = "";
                    $oPaquete->message = 'Error inesperado al procesasr los datos'; // Agregar detalles del error
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",'Parametros Enviados Para la Emision MultiSector'
                        , 'Datos a Enviar de la Empresa', $lcEmpresa
                        , 'Datos a Enviar del Punto de Venta', $lcPuntoVenta
                        , 'Datos a Enviar de la Factura', $lcFactura
                        , 'Datos a Enviar del Detalle de la Factura', $lcFacturaDetalle
                        , 'Datos a Enviar de la Factura Sector', $lcFacturaSector
                        , 'Datos a Enviar del Detalle de la Factura Sector', $lcFacturaDetalleSector);
                    
                    
                    //--------------------Envio a FacturaTech---------------------
                    $result = $SincronizacionSiatController->EmisionEnLineaMultiSectorReturn($loVenta->Empresa, $lcEmpresa, $lcPuntoVenta, $lcFactura, $lcFacturaDetalle, $lcFacturaSector, $lcFacturaDetalleSector);
                    
                    $result = $result->original;

                  
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",'Resultados de consulta', $result);

                    
                    
                    if(!is_null($result->values) &&  isset($result->values->CUF )   &&   !is_null($result->values->CUF) &&	$result->values->CUF != "")
                    {
                        //Error::guardarLog(true,"Transaccion_$tnTransaccionDePago",  "INGRESO A ACTUALIZAR " ,  $tnNumeroFactura , $tnTransaccionDePago );
                        $llOk = DB::table('VENTAFACTURA')
                        //->where('Numero', $tnNumeroFactura )
                        ->where('Venta', $loVenta->Venta)
                        ->update(['Cuf' => $result->values->CUF,
                            'FacturaNube' => $result->values->FacturaNube,
                            'EstadoSiat' => 2
                        ]);
                        
                        $factura = VentaFactura::find($tnVentaFactura);
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura" , "VentaFactura" ,$loFactura->NumeroFactura , "Actualizada por ",$factura);
                        $result->values  = $factura;
                        //Error::guardarLog(true,"ServicioFacturaTech","DEVOLVIO CON EXITO : ",$tnTransaccionDePago, @$result->values->CUF , @$llOk  );
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura","DEVOLVIO CON EXITO : ","Numero de Venta: ".$loVenta->Venta, @$result->values->CUF , @$llOk  );
                        $oPaquete->message = 'Factura Emitida Correctamete'; // Agregar detalles del error
                        $oPaquete = $result;
                    }else{
                        if($result->status === 1){
                            $oPaquete = $result;
                        }
                    }
                    
                    
                    
                    
                    
                    return response()->json($oPaquete);
                    return $tnNumeroFactura ;
                    
                }else{
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura","pero este  ya tiene cuf", $tnVentaFactura);

                    $oPaquete->error = 1; // Indicar que hubo un error
                    $oPaquete->status = 0; // Indicar que hubo un error
                    $oPaquete->messageSistema = "Error en el proceso";
                    $oPaquete->message = "la factura ya tiene cuf"; // Agregar detalles del error
                }
            });
                
                return response()->json($oPaquete);
                
                //--------------------Envio a FacturaTech---------------------            
        }catch (\Throwable $th) {
            Error::guardarLog(true,"VentaFactura_.$tnVentaFactura","Error : ".$th->getMessage()." Linea ".$th->getLine(),  $tnVentaFactura  );
            if(isset($th->original)){
                $result = $th->original();
                if($result->status === 1){
                    return response()->json($result, 500); // Devolver una respuesta con codigo 500
                }
            }
            
            $mensaje = "Error : ".$th->getMessage()." Linea ".$th->getLine() ;
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $mensaje; // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con codigo 500
        }
    }
                
    public function emitirSiatCompraVenta(Request $request){
        
        try{
            
            $request->validate([
                'tnVentaFactura'=> 'required',
            ]);
            
            $tnVentaFactura = $request->tnVentaFactura;
            Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Venta a emitir " ,  $tnVentaFactura );
            
            
            $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
            DB::transaction(function () use (&$tnVentaFactura, &$oPaquete) {
                $registro = VentaFactura::find($tnVentaFactura);
                $oUser = auth()->user();
                Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos del usuario" ,  $oUser,"Datos para la factura",$registro );
                
                if (is_null($registro->Cuf))
                {
                    $lcEmpresa;
                    $lcPuntoVenta;
                    $lcFactura;
                    $lcFacturaDetalle;
                    
                    $oVenta = Venta::find($tnVentaFactura);
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos de la Venta" ,  $oVenta);
                    // Realiza la llamada al servicio para obtener el Cuf
                    //-------------------tcEmpresa---------------------------
                    // estos datos tengo q sacarlo de alguna tbal pero son dtos de la empresa
                    
                    $laConsultaCredenciales = DB::table('ASOCIACIONTIPODOCUMENTOSECTOR as atds')
                    ->join('ASOCIACION as a', 'a.Asociacion', '=', 'atds.Asociacion')
                    ->join('EMPRESA as e', 'e.Empresa', '=', 'a.Empresa')
                    ->join('REGION as r', 'r.Region', '=', 'e.Region')
                    ->where( 'atds.TipoDocumentoSector', $registro->TipoDocumentoSector)
                    ->where('a.CodigoAmbiente', $oUser->CodigoAmbiente)
                    ->where( 'a.Empresa', $oVenta->Empresa)
                    ->select('e.*', 'r.Nombre as Region', 'a.AsociacionCredencial as Credenciales')
                    ->distinct()
                    ->get();
                    
                    
                    $lcCredencial;
                    $tnNit;
                    $tcRazonSocial;
                    $tcDireccion;
                    $tcLocalidad;
                    $tnTelefono;
                    
                    if(count($laConsultaCredenciales)>0)
                    {
                        $tnNit;
                        if($oUser->CodigoAmbiente ===2){
                            $tnNit='3879294013';
                        }else{
                            $tnNit=$laConsultaCredenciales[0]->Nit;
                        }
                        $lcCredencial=$laConsultaCredenciales[0]->Credenciales;
                        $tcRazonSocial=$laConsultaCredenciales[0]->RazonSocial;
                        $tcDireccion=$laConsultaCredenciales[0]->Direccion;
                        $tcLocalidad=$laConsultaCredenciales[0]->Region;
                        $tnTelefono=$laConsultaCredenciales[0]->Telefono;
                        
                        $lcEmpresa=
                        $lcCredencial
                        ."ß".$tnNit
                        ."ß".$tcRazonSocial
                        ."ß".$tcDireccion
                        ."ß".$tcLocalidad
                        ."ß".$tnTelefono."þ";
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos y credenciales de la empresa con las que se emitira la fafctura" ,  $laConsultaCredenciales[0] );
                    }else{
                        return  0;
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos de la Empresa " ,  "no se Obtuvo los datos de la empresa" );
                    }
                    //-------------------tcEmpresa--------- -------------------
                    
                    
                    
                    //-------------------tcPuntoVenta-----------------------------
                    //son datos que tenemos de facturatech se guardara en alguna tabla
                    $laConsultaParametros = PuntoVenta::find($oVenta->PuntoVenta);
                    $tcCodigoSucursal=$laConsultaParametros->CodigoSucursal;
                    $tnCodigoPuntoVenta=$laConsultaParametros->CodigoPuntoVenta;
                    $tcNombreSucursal="Sucursal Casa Matriz";
                    $tcNombrePuntoVenta="Usuario Comercio";
                    $tcDireccion=$tcDireccion;
                    $tnTelefono=$tnTelefono;
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "punto de venta" ,  $laConsultaParametros);
                    
                    $lcPuntoVenta=
                    $tcCodigoSucursal
                    ."ß".$tnCodigoPuntoVenta
                    ."ß".$tcNombreSucursal
                    ."ß".$tcNombrePuntoVenta
                    ."ß".$tcDireccion
                    ."ß".$tnTelefono."þ";
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos del punto de venta a enviar" ,  $lcPuntoVenta );
                    //-------------------tcPuntoVenta-----------------------------
                    //-------------------tcFactura--------------------------------
                    //aqui de la transaccion y un poco de la empresa
                    if($registro)
                    {
                        $SincronizacionSiatController = new SincronizacionSiatController();
                        
                        $lcCorreoCliente= $registro->Email;
                        /// aqui obtengo el metodo depago segun el siat
                        
                        
                        $tcNumeroTarjeta=$registro->NumeroTarjeta;
                        $tnNitEmisor=$tnNit;
                        $tcRazonSocialEmisor=$tcRazonSocial;
                        $tcMunicipio=$tcLocalidad;
                        $tnTelefono=$tnTelefono;
                        
                        
                        $tnNumeroFactura= $registro->NumeroFactura ; //VariablesGlobales::$mNro_fac;
                        
                        $tcCodigoSucursal=$registro->CodigoSucursal;
                        $tcDireccion=$tcDireccion;
                        $tnCodigoPuntoVenta=$tnCodigoPuntoVenta;
                        $d = Carbon::now('America/La_Paz');
                        $tcFechaEmision= $d->format('Y-m-d');
                        $tcHoraEmision= $d->format("H:i:s.v");
                        $tcNombreRazonSocial=$registro->RazonSocial;
                        
                        
                        
                        $tcCodigoTipoDocumentoIdentidad=$registro->DocumentoIdentidad;
                        $tcNumeroDocumento=$registro->NumeroDocumento;
                        
                        if($tcNumeroDocumento == 0)
                        {
                            $tcNumeroDocumento=$registro->CodigoCliente;
                        }
                        
                        //validacion del tipo de documento entidad
                        if($tcCodigoTipoDocumentoIdentidad== 5)
                        {
                            $tcCodigoExcepcion="1";
                            $result = $SincronizacionSiatController->ValidacionNitReturn($lnEmpresa,$registro->NumeroDocumento);
                            Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos de Validacion de Nit " ,  $result );
                            
                            if($result){
                                $texto = (string) $result;
                                $inicio = strpos($texto, '{');
                                $result = substr($texto, $inicio);
                                $pattern_activo = "/ACTIVO/";
                                $pattern_inactivo = "/INACTIVO/";
                                
                                if (preg_match($pattern_inactivo, $result)) {
                                    $tcCodigoExcepcion = "0";
                                } else{
                                    if (preg_match($pattern_activo, $result)) {
                                        $tcCodigoExcepcion = "1";
                                    } else {
                                        $tcCodigoExcepcion = "0";
                                    }
                                }
                            }else{
                                $tcCodigoExcepcion = "0";
                            }
                        }else{
                            Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Tipo de Documento identidad: ".$registro->DocumentoIdentidad ,  "Numero de Documento: ".$registro->NumeroDocumento );
                            
                            $tcCodigoExcepcion="0";
                        }
                        
                        
                        
                        $tcComplemento=$registro->Complemento;
                        $tcCodigoCliente=$registro->CodigoCliente;
                        //$tcCodigoMetodoPago= $registro->MetodoPago;
                        $tcCodigoMetodoPago= $registro->MetodoPago; // $laTransaccionDePago->MetodoPago;
                        $tcNumeroTarjeta=$tcNumeroTarjeta ; // "0";
                        $tnMontoTotal=$oVenta->TotalPagar;
                        $tnMontoTotalSujetoIva=$oVenta->ImporteIva;
                        $tcCodigoMoneda=$registro->Moneda;
                        
                        $tnTipoCambio=1;
                        $tnMontoGiftCard= $oVenta->GiftCard;
                        $tnDescuentoAdicional= $oVenta->TotalDesc;
                        $tcUsuario="PagoFacil";
                        $tnSector=1;
                        $tcCorreoCliente=$lcCorreoCliente;
                        
                    }
                    
                    //ejemplo comentado
                    $lcFactura=	$tnNitEmisor    //269322027
                    ."ß".$tcRazonSocialEmisor   //ßAGROINDUSTRIA NAYADE S.A.
                    ."ß".$tcMunicipio           //ß Andres Ibaez
                    ."ß".$tnTelefono            //ß 3435780
                    ."ß".$tnNumeroFactura       //ß 101
                    ."ß".$tcCodigoSucursal      //ß 0
                    ."ß".$tcDireccion           //ß Av.Cumavi N s/n Barrio Bolivar U.V
                    ."ß".$tnCodigoPuntoVenta    //ß 0
                    ."ß".$tcFechaEmision        //ß 2023-06-19
                    ."ß".$tcHoraEmision         //ß 08:41:29.000
                    ."ß".$tcNombreRazonSocial   //ß Urgel Coronado Chela
                    ."ß".$tcCodigoTipoDocumentoIdentidad    //ß4
                    ."ß".$tcNumeroDocumento     //ß 2001
                    ."ß".$tcComplemento         //ß
                    ."ß".$tcCodigoCliente       //ß 2001
                    ."ß".$tcCodigoMetodoPago    //ß 6
                    ."ß".$tcNumeroTarjeta       //ß
                    ."ß".$tnMontoTotal          //ß 500.00
                    ."ß".$tnMontoTotalSujetoIva //ß 500.00  //revision asta este punto
                    ."ß".$tcCodigoMoneda        //ß 1
                    ."ß".$tnTipoCambio          //ß 1
                    ."ß".$tnMontoGiftCard       //ß 0.00
                    ."ß".$tnDescuentoAdicional  //ß 0.00
                    ."ß".$tcCodigoExcepcion     //ß 0
                    ."ß".$tcUsuario             //ß Cesar Corvera Murakami
                    ."ß".$tnSector              //ß 24
                    ."ß".$tcCorreoCliente."þ";  //ß þ
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos de la factura " ,  $lcFactura );
                    
                    //-------------------tcFactura--------------------------------
                    //-------------------tcFacturaDetalle-------------------------
                    //de la transaccion y de algun lado lo de codigo      
                    $laConsultaDetalles = DB::table('VENTADETALLE as vd')
                    ->join('PRODUCTO as p', 'p.CodigoProductoOrigen', '=', 'vd.Producto')
                    ->select('vd.*', 'p.*')
                    ->where('p.Empresa', $oVenta->Empresa)
                    ->where('vd.Venta', $oVenta->Venta)
                    ->get();
                    $lcFacturaDetalle="";
                    
                    foreach ($laConsultaDetalles as $oVentaDetalle) {
                        $tnCodigoProducto= $oVentaDetalle->CodigoProductoOrigen.'¦'.$oVentaDetalle->ActividadEconomica.'¦'.$oVentaDetalle->CatalogoImpuestos.'ß';
                        $tcDescripcion= $oVentaDetalle->Nombre;
                        $tnCantidad= $oVentaDetalle->Cantidad;
                        $tnUnidadMedida= $oVentaDetalle->UnidadMedida;
                        $tnPrecioUnitario= $oVentaDetalle->PrecioUnitario;
                        $tnMontoDescuento= $oVentaDetalle->MontoDescuento;
                        $tnSubTotal= $oVentaDetalle->SubTotal ;
                        $tnNumeroSerie= $oVentaDetalle->NumeroSerie;
                        $tcNumeroImei= $oVentaDetalle->NumeroImei;
                        
                        $lcFacturaDetalle.=
                        $tnCodigoProducto
                        ."ß".$tcDescripcion
                        ."ß".$tnCantidad.
                        "ß".$tnUnidadMedida.
                        "ß".$tnPrecioUnitario.
                        "ß".$tnMontoDescuento.
                        "ß".$tnSubTotal.
                        "ß".$tnNumeroSerie.
                        "ß".$tcNumeroImei."þ";
                        
                    }
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Datos del detalle de factura " ,  $lcFacturaDetalle );
                    
                    
                    
                    $data = array(
                        'tcEmpresa'=>$lcEmpresa,
                        'tcPuntoVenta'=>$lcPuntoVenta,
                        'tcFactura'=>$lcFactura,
                        'tcFacturaDetalle'=>$lcFacturaDetalle,
                        //	'tnTipoEmision'=>2,
                        
                    );
                    
                    
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",  "Empresa a Emitir" ,  $oVenta->Empresa
                        ,  "Datos de lcEmpresa" ,  $lcEmpresa
                        ,  "Datos de lcPuntoVenta" ,  $lcPuntoVenta
                        ,  "Datos de lcFactura" ,  $lcFactura
                        ,  "Datos de lcFacturaDetalle" ,  $lcFacturaDetalle);
                    
                    //-------------------tcFacturaDetalle-------------------------
                    //--------------------Envio a FacturaTech---------------------
                    $result = $SincronizacionSiatController->EmisionEnLineaReturn($oVenta->Empresa,$lcEmpresa, $lcPuntoVenta, $lcFactura, $lcFacturaDetalle);
                    
                    $result = $result->original;
                    /*resultado estatico para la primera prueba
                     *                     $result =$oPaquete;
                     
                     $result->error = 0;
                     $result->status = 1;
                     $result->message = "factura emitida correctamente "; // Agregar detalles del error
                     $result->messageSistema = "Se obtuvo correctamente ";
                     //$oPaquete->values = [$lcEmpresa, $lcPuntoVenta, $lcFactura, $lcFacturaDetalle];
                     
                     
                     //$oPaquete->values = [$lcEmpresa, $lcPuntoVenta, $lcFactura, $lcFacturaDetalle];
                     $values = (object)[
                     'CUF' => "1096E9BFA907AB21193C7B093A4535C48402DCEB0A5544FC592ADC6D74",
                     'FacturaXML' => "",
                     'FacturaPDF' => "",
                     'Transaccion' => true,
                     'Estado' => true,
                     'TipoEmision' => 1,
                     'FacturaNube' => 48896,
                     ];
                     $result->values = $values;
                     */
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura",'Resultados de consulta', $result ,"Data" , $data);
                    
                    if(!is_null($result->values) &&  isset($result->values->CUF )   &&   !is_null($result->values->CUF) &&	$result->values->CUF != "")
                    {
                        //Error::guardarLog(true,"Transaccion_$tnTransaccionDePago",  "INGRESO A ACTUALIZAR " ,  $tnNumeroFactura , $tnTransaccionDePago );
                        $llOk = DB::table('VENTAFACTURA')
                        //->where('Numero', $tnNumeroFactura )
                        ->where('Venta', $oVenta->Venta)
                        ->update(['Cuf' => $result->values->CUF,
                            'FacturaNube'=>$result->values->FacturaNube
                        ]);
                        
                        $factura = VentaFactura::find($tnVentaFactura);
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura" , "VentaFactura" ,$tnNumeroFactura , "Actualizada por ",$factura);
                        $result->values  = $factura;
                        //Error::guardarLog(true,"ServicioFacturaTech","DEVOLVIO CON EXITO : ",$tnTransaccionDePago, @$result->values->CUF , @$llOk  );
                        Error::guardarLog(true,"VentaFactura_.$tnVentaFactura","DEVOLVIO CON EXITO : ","Numero de Venta: ".$oVenta->Venta, @$result->values->CUF , @$llOk  );
                    }else{
                        if($result->status === 1){
                            $oPaquete = $result;
                        }
                    }
                    
                    return response()->json($oPaquete);
                    return $tnNumeroFactura ;
                    
                }else{
                    
                    Error::guardarLog(true,"VentaFactura_.$tnVentaFactura","pero este  ya tiene cuf", $tnVentaFactura);
                    
                    $oPaquete->error = 1; // Indicar que hubo un error
                    $oPaquete->status = 0; // Indicar que hubo un error
                    $oPaquete->messageSistema = "Error en el proceso";
                    $oPaquete->message = "la factura ya tiene cuf"; // Agregar detalles del error
                }
            });
                
                return response()->json($oPaquete);
                
                //--------------------Envio a FacturaTech---------------------
        }catch (\Throwable $th) {
            Error::guardarLog(true,"VentaFactura_.$tnVentaFactura","Error : ".$th->getMessage()." Linea ".$th->getLine(),  $tnVentaFactura  );
            $result = $th->original();
            if($result->status === 1){
                return response()->json($result, 500); // Devolver una respuesta con codigo 500
            }
            $mensaje = "Error : ".$th->getMessage()." Linea ".$th->getLine() ;
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $mensaje; // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con codigo 500
        }
    }
    
    
    
/**
     * tcFacturaDetalle :
     */
    public function getTcFacturaDetalle($tnFactura) {
        
        $lcFacturaDetalle = ''; // Inicializa la variable como una cadena vacía
        
        $oVenta = Venta::find($tnFactura);
        $oVentaDetalles = VentaDetalle::where('Venta', $tnFactura)->get();
        
        foreach ($oVentaDetalles as $oVentaDetalle) {
            $oProducto = Producto::where('CodigoProductoOrigen',$oVentaDetalle->Producto)
            ->where('Empresa',$oVenta->Empresa)->first();
            $lcFacturaDetalle .= $oProducto->CodigoProductoOrigen.'¦'.$oProducto->ActividadEconomica.'¦'.$oProducto->CatalogoImpuestos.'ß';
            
            $lcFacturaDetalle .= $oProducto->Nombre.'ß'.$oVentaDetalle->Cantidad.'ß'.$oVentaDetalle->UnidadMedida.'ß'.$oVentaDetalle->PrecioUnitario.'ß'.$oVentaDetalle->MontoDescuento.'ß'.$oVentaDetalle->SubTotal.'ß'.$oVentaDetalle->NumeroSerie.'ß'.$oVentaDetalle->NumeroImei.'þ';
        }
        
        return $lcFacturaDetalle; // Devuelve la cadena generada
        
        
    }
    
    public function estadoSiat(Request $request){
        
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $request->validate([
                'tcCUF'=> 'required',
                'tnNumeroFactura'=> 'required',
                'tnCodigoSucursal'=> 'required',
                'tnCodigoPuntoVenta'=> 'required',
            ]);
            
            $lcCUF = $request->tcCUF;
            $lnNumeroFactura = $request->tnNumeroFactura;
            $lnCodigoSucursal = $request->tnCodigoSucursal;
            $lnCodigoPuntoVenta = $request->tnCodigoPuntoVenta;
            
            $oUser = Auth::user();
            $lnEmpresa=$oUser->EmpresaSeleccionada;
            
            
            $SincronizacionSiatController = new SincronizacionSiatController();
            $result = $SincronizacionSiatController->consultarEstadoReturn($lnEmpresa,$lcCUF, $lnNumeroFactura, $lnCodigoSucursal, $lnCodigoPuntoVenta);
            $result = $result->original;
            
            return response()->json($result);
        }catch (\Exception $e) {
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage().$e->getFile().$e->getLine(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con cÃ³digo 500
        }
    }
 

    public function anularFacturaSiat(Request $request){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            if(isset($request->tcCUF)){
                $oUser = Auth::user();
                $lcCUF = $request->tcCUF;
                $lnEmpresa=$oUser->EmpresaSeleccionada;
                
                Error::guardarLog(true,"Anuladas_Empresa_.$lnEmpresa",  "Solicitud de Anulacion por"
                    ,'Usuario',$oUser
                    ,'CUF',$lcCUF
                    ,'Empresa',$lnEmpresa
                    );
                
                $SincronizacionSiatController = new SincronizacionSiatController();
                $result = $SincronizacionSiatController->anularFacturaReturn($lnEmpresa,$lcCUF);
                $result = $result->original;
                Error::guardarLog(true,"Anuladas_Empresa_.$lnEmpresa",  "Solicitud de Anulacion por"
                    ,'Resultados',$result
                    );
                $oPaquete =$result;
                
            }else{
                $oPaquete->error = 1; 
                $oPaquete->status = 0; 
                $oPaquete->message = "la Factura no tiene CUF";
                $oPaquete->values = 1;
            }
            
            return response()->json($oPaquete);
        }catch (\Exception $e) {
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con cÃ³digo 500
        }
    }
    
    
    //ejemplo proporcionado por leo
    public static function  EmitirFacturaFacturaTech( $tnTransaccionDePago)
    {
        
        try {
            
            Error::guardarLog(true,"ServicioFacturaTech","INGRESO : ", $tnTransaccionDePago);
            Error::guardarLog(true,"Transaccion_$tnTransaccionDePago",  "Ingreso a emitir " ,  $tnTransaccionDePago );
            DB::transaction(function () use (&$tnTransaccionDePago) {
                $registro = DB::table('FACTURA')
                ->where('TablaID', $tnTransaccionDePago  )
                ->lockForUpdate()
                ->first();
                
                if (is_null($registro->Cuf))
                {
                    
                    
                    // Realiza la llamada al servicio para obtener el Cuf
                    //-------------------tcCredencial---------------------------
                    // estos datos tengo q sacarlo de alguna tbal pero son dtos de la empresa
                    $lcSQLCredenciales = "SELECT
													*
												FROM
													CREDENCIALESFACTURATECH
												WHERE
													Estado = 2";
                    $laConsultaCredenciales= DB::select($lcSQLCredenciales);
                    if(count($laConsultaCredenciales)>0)
                    {
                        $lcCredencial=$laConsultaCredenciales[0]->Credenciales;
                        $tnNit=$laConsultaCredenciales[0]->Nit;
                        $tcRazonSocial=$laConsultaCredenciales[0]->RazonSocial;
                        $tcDireccion=$laConsultaCredenciales[0]->Direccion;
                        $tcLocalidad=$laConsultaCredenciales[0]->Localidad;
                        $tnTelefono=$laConsultaCredenciales[0]->Telefono;
                    }else{
                        return  0;
                    }
                    
                    $tcCredencial=$lcCredencial."ß".$tnNit."ß".$tcRazonSocial."ß".$tcDireccion."ß".$tcLocalidad."ß".$tnTelefono."þ";
                    //-------------------tcCredencial--------- -------------------
                    
                    
                    
                    //-------------------tcPuntoVenta-----------------------------
                    //son datos que tenemos de facturatech se guardara en alguna tabla
                    $lcSQLParametros = "SELECT
														*
													FROM
														PARAMETROS";
                    
                    $laConsultaParametros= DB::select($lcSQLParametros);
                    $tcCodigoSucursal=0;
                    $tnCodigoPuntoVenta=$laConsultaParametros[0]->CodigoPuntoVenta;
                    $tcNombreSucursal="Sucursal Casa Matriz";
                    $tcNombrePuntoVenta="Usuario Comercio";
                    $tcDireccion=$tcDireccion;
                    $tnTelefono=$tnTelefono;
                    $tcPuntoVenta=$tcCodigoSucursal."ß".$tnCodigoPuntoVenta."ß".$tcNombreSucursal."ß".$tcNombrePuntoVenta."ß".$tcDireccion."ß".$tnTelefono."þ";
                    //-------------------tcPuntoVenta-----------------------------
                    
                    
                    //-------------------tcFactura--------------------------------
                    //aqui de la transaccion y un poco de la empresa
                    $lcSQLCredenciales = "SELECT
                    *
                    FROM
                    TRANSACCIONDEPAGO
                    WHERE
                    TransaccionDePago =$tnTransaccionDePago";
                    
                    $laTransaccionDePago= DB::select($lcSQLCredenciales);
                    if(count($laTransaccionDePago)>0)
                    {
                        $laTransaccionDePago = $laTransaccionDePago[0];
                        
                        $lcSQL = "SELECT * FROM PARAMETROS";
                        Cursores::$aPARAPAFA = DB::select($lcSQL);
                        
                        VariablesGlobales::$mID_ordeFac = CrearVenta::GetOrdeFac(1);
                        if(VariablesGlobales::$mID_ordeFac===null || strlen(VariablesGlobales::$mID_ordeFac)==0){
                            Error::guardarLog(false,"NoHayOrderDosificacion", VariablesGlobales::$mID_ordeFac);
                            return;
                        }
                        VariablesGlobales::$mTipoFactur = 2;
                        VariablesGlobales::$mNro_fac = CrearVenta::GetNFacturasinUpdate(VariablesGlobales::$mID_ordeFac, 1);
                        VariablesGlobales::$mNro_fac=VariablesGlobales::$mNro_fac -1;
                        $loTransaccionDePagoEnvio = DB::select("SELECT *
                            FROM TRANSACCIONDEPAGOENVIO
                            WHERE TransaccionDePago=$tnTransaccionDePago");
                        $lcCorreoCliente="";
                        $lcUsuario="";
                        if(count($loTransaccionDePagoEnvio)== 0  )
                        {
                            $lnCliente=	$laTransaccionDePago->Cliente;
                            $loCliente = DB::select("SELECT *
                                FROM CLIENTE
                                WHERE Cliente=$lnCliente");
                            $lcCorreoCliente=$loCliente[0]->correo;
                        }else{
                            $lcCorreoCliente=$loTransaccionDePagoEnvio[0]->Correo;
                        }
                        /// aqui obtengo el metodo depago segun el siat
                        
                        $lnMetodoPagoTransaccion=$laTransaccionDePago->MetodoPago;
                        $lcSQLMetodoPago = "SELECT
                        *
                        FROM
                        METODOPAGO
                        WHERE
                        MetodoPago =$lnMetodoPagoTransaccion ";
                        $laMetodoPago= DB::select($lcSQLMetodoPago);
                        $tcNumeroTarjeta=0;
                        if(count($laMetodoPago)>0)
                        {
                            if($laTransaccionDePago->MetodoPago == 6)
                            {
                                $lcSQLTrajetaLinkser = "SELECT
                                *
                                FROM
                                TRANSACCIONDEPAGOLINKSER
                                WHERE
                                TransaccionDePago =$tnTransaccionDePago";
                                $laTarjeta= DB::select($lcSQLTrajetaLinkser);
                                $tcNumeroTarjeta=@$laTarjeta[0]->Tarjeta;
                            }
                            if($laTransaccionDePago->MetodoPago == 9 ||  $laTransaccionDePago->MetodoPago == 10  )
                            {
                                $lcSQLTrajeta = "SELECT
                                CYBERSOURCETARJETA.NroTarjeta as Tarjeta
                                FROM
                                TRANSACCIONDEPAGOCYBERSOURCETARJETA,
                                CYBERSOURCETARJETA
                                WHERE
                                TRANSACCIONDEPAGOCYBERSOURCETARJETA.CybersourceTarjeta = CYBERSOURCETARJETA.CybersourceTarjeta
                                
                                AND TRANSACCIONDEPAGOCYBERSOURCETARJETA.TransaccionDePago =$tnTransaccionDePago";
                                $laTarjeta= DB::select($lcSQLTrajeta);
                                $tcNumeroTarjeta=@$laTarjeta[0]->Tarjeta;
                                
                            }
                            
                        }
                        
                        // aqui obtyengo el numero de factura de la tabla afactura
                        // comentado por leo 23062023
                        // estoy comentando esto por que ya hago una consulta al iniciar
                        /*$lcSQLFactura = "SELECT
                         *
                         FROM
                         FACTURA
                         WHERE
                         TablaID =$tnTransaccionDePago";
                         $laFacturaPagoFacil= DB::select($lcSQLFactura);
                         if( count($laFacturaPagoFacil) > 0)
                         {
                         Error::guardarLog(true,"Transaccion_$tnTransaccionDePago","DatosDevueltos:", "facturapagofacil antes de emitir " ,$laFacturaPagoFacil );
                         $lcCUFFactura= $laFacturaPagoFacil[0]->Cuf ;
                         if(!is_null($lcCUFFactura)   ){
                         return 0;
                         }
                         }
                         */
                        
                        $tnNitEmisor=$tnNit;
                        $tcRazonSocialEmisor=$tcRazonSocial;
                        $tcMunicipio=$tcLocalidad;
                        $tnTelefono=$tnTelefono;
                        
                        
                        $tnNumeroFactura= $tnTransaccionDePago ; //VariablesGlobales::$mNro_fac;
                        
                        
                        $tcCodigoSucursal=0;
                        $tcDireccion=$tcDireccion;
                        $tnCodigoPuntoVenta=$tnCodigoPuntoVenta;
                        $tcFechaEmision=date('Y-m-d');
                        $d = new DateTime();
                        $tcHoraEmision=$d->format("H:i:s.v");
                        $tcNombreRazonSocial=$laTransaccionDePago->FacturaA;
                        $tcCodigoTipoDocumentoIdentidad=$laTransaccionDePago->TipoDocumentoIdentidad;
                        $tcNumeroDocumento=$laTransaccionDePago->CiNit;
                        if($tcNumeroDocumento == 0)
                        {
                            $tcNumeroDocumento=$laTransaccionDePago->CodigoClienteEmpresa;
                        }
                        
                        if($tcCodigoTipoDocumentoIdentidad== 5)
                        {
                            $tcCodigoExcepcion="1";
                        }else{
                            $tcCodigoExcepcion="0";
                        }
                        
                        
                        
                        $tcComplemento="";
                        $tcCodigoCliente=$laTransaccionDePago->Cliente;
                        $tcCodigoMetodoPago= $laMetodoPago[0]->CodigoMetodoPagoSIAT; // $laTransaccionDePago->MetodoPago;
                        $tcNumeroTarjeta=$tcNumeroTarjeta ; // "0";
                        $tnMontoTotal=$laTransaccionDePago->MontoClienteSYSCOOP;
                        $tnMontoTotalSujetoIva=$laTransaccionDePago->MontoClienteSYSCOOP;
                        $tcCodigoMoneda=$laTransaccionDePago->Moneda;
                        
                        if($tcCodigoMoneda==2)
                        {
                            // en pagofacil es moneda 2 boliviando
                            //pero en facturatech moneda 1 es boliviano
                            $tcCodigoMoneda=1;
                        }
                        
                        $tnTipoCambio=1;
                        $tnMontoGiftCard=0;
                        $tnDescuentoAdicional=0;
                        //$tcCodigoExcepcion="0";
                        $tcUsuario="PagoFacil";
                        $tnSector=1;
                        $tcCorreoCliente=$lcCorreoCliente;
                    }
                    
                    $tcFactura=	$tnNitEmisor
                    ."ß".$tcRazonSocialEmisor
                    ."ß".$tcMunicipio
                    ."ß".$tnTelefono
                    ."ß".$tnNumeroFactura
                    ."ß".$tcCodigoSucursal
                    ."ß".$tcDireccion
                    ."ß".$tnCodigoPuntoVenta
                    ."ß".$tcFechaEmision
                    ."ß".$tcHoraEmision
                    ."ß".$tcNombreRazonSocial
                    ."ß".$tcCodigoTipoDocumentoIdentidad
                    ."ß".$tcNumeroDocumento
                    ."ß".$tcComplemento
                    ."ß".$tcCodigoCliente
                    ."ß".$tcCodigoMetodoPago
                    ."ß".$tcNumeroTarjeta
                    ."ß".$tnMontoTotal
                    ."ß".$tnMontoTotalSujetoIva
                    ."ß".$tcCodigoMoneda
                    ."ß".$tnTipoCambio
                    ."ß".$tnMontoGiftCard
                    ."ß".$tnDescuentoAdicional
                    ."ß".$tcCodigoExcepcion
                    ."ß".$tcUsuario
                    ."ß".$tnSector
                    ."ß".$tcCorreoCliente."þ";
                    
                    //-------------------tcFactura--------------------------------
                    //-------------------tcFacturaDetalle-------------------------
                    //de la transaccion y de algun lado lo de codigo
                    $tnCodigoProducto="8-1-1-001";
                    $tcDescripcion="SERVICIO COBRANZA DE FACTURAS";
                    $tnCantidad=1;
                    $tnUnidadMedida=58;
                    $tnPrecioUnitario=$tnMontoTotal;
                    $tnMontoDescuento="0";
                    $tnSubTotal=$tnPrecioUnitario * $tnCantidad ;
                    $tnNumeroSerie="";
                    $tcNumeroImei="";
                    $tcFacturaDetalle=$tnCodigoProducto."ß".$tcDescripcion."ß".$tnCantidad."ß".$tnUnidadMedida."ß".$tnPrecioUnitario."ß".$tnMontoDescuento."ß".$tnSubTotal."ß".$tnNumeroSerie."ß".$tcNumeroImei."þ";
                    //-------------------tcFacturaDetalle-------------------------
                    //--------------------Envio a FacturaTech---------------------
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url="http://facturacionenlinea.facturatech.com.bo/api/facturacion/crearfactura";
                    $data = array(  'tcEmpresa'=>$tcCredencial,
                        'tcPuntoVenta'=>$tcPuntoVenta,
                        'tcFactura'=>$tcFactura,
                        'tcFacturaDetalle'=>$tcFacturaDetalle,
                        //	'tnTipoEmision'=>2,
                        
                    );
                    $header=[
                        'Accept'        => 'application/json',
                    ];
                    Error::guardarLog(true,"Transaccion_$tnTransaccionDePago", "INGRESO"  );
                    $response = $client->post($url, ['headers' => $header,
                        'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                    
                    
                    
                    
                    Error::guardarLog(true,"Transaccion_$tnTransaccionDePago","DatosDevueltos:",  $result ,"Data" , $data  ,$laTransaccionDePago ,  $result->values  );
                    if(!is_null($result->values)   &&  isset($result->values->CUF )   &&   !is_null($result->values->CUF)   &&   	$result->values->CUF != ""   )
                    {
                        Error::guardarLog(true,"Transaccion_$tnTransaccionDePago",  "INGRESO A ACTUALIZAR " ,  $tnNumeroFactura , $tnTransaccionDePago );
                        $llOk = DB::table('FACTURA')
                        //->where('Numero', $tnNumeroFactura )
                        ->where('TablaID', $tnTransaccionDePago  )
                        ->update(['Cuf' => $result->values->CUF]);
                        Error::guardarLog(true,"ServicioFacturaTech","DEVOLVIO CON EXITO : ",$tnTransaccionDePago, @$result->values->CUF , @$llOk  );
                    }
                    
                    return $tnNumeroFactura ;
                    
                    
                    
                    
                }else{
                    Error::guardarLog(true,"ServicioFacturaTech","pero este  ya tiene cuf", $tnTransaccionDePago);
                    return 0 ;
                }
            });
                //--------------------Envio a FacturaTech---------------------
                
        } catch (\Throwable $th) {
            //throw $th;
            Error::guardarLog(true,"ServicioFacturaTech","Error : ".$th->getMessage()." Linea ".$th->getLine(),  $tnTransaccionDePago  );
            return "Error : ".$th->getMessage()." Linea ".$th->getLine() ;
        }
        
        
    }
    
    public function envioFactura(Request $request){
        
        try {
            $data = $request->FacturaDatos;
            
            $empresa = $data['empresa'];
            $factura = $data['factura'];
            $factura =$factura[0];
            $empresa  =$empresa [0];
            
            $mensaje = [
                'name' => $factura['NombreRazonSocial'],
                'email' => $factura['EmailCliente'],
                'subjet' => 'Envio de Facturas',
                'content' => $empresa,
                'archivoPDF' => $request->FacturaPDF,
            ];
            Mail::to($mensaje['email'])->send(new SMSAdjunto($mensaje));
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "la Factura se envio Correctamente",
                'values'=>null
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> $e->getMessage(),
                'values'=>null
            ]);
        }
    }

    public function crearPDF(Request $request){
        try {
            $data = $request->pdf;
            $name = $request->name;
            
            $pdf = base64_decode($data);
            $url =  'pdf/temporal/' .$name. ".pdf";
            Storage::disk('public')->put($url, $pdf);
            
            $url = env('APP_URL') . '/' . $url;
            
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message'=> "el PDF fue creado exitosamente",
                'values'=>$url
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> $e->getMessage(),
                'mesajeSitema'=>'Error en el Proceso de Crear PDF',
                'values'=>null
            ]);
        }
    }
    
    public function eliminarPDF(Request $request){
        try {
            $name = $request->name;
            
            $url =  'pdf/temporal/' .$name. ".pdf";
            Storage::disk('public')->delete($url);
            return response()->json([
                    'error' => 1,
                    'status' => 1,
                    'message'=> "el PDF fue eliminado corectamente",
                    'values'=>null
                ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message'=> $e->getMessage(),
                'values'=>null
            ]);
        }
        
    }
    
    
}
