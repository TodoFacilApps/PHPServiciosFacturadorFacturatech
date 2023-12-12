<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Carbon\Carbon;

use App\Modelos\mPaquetePagoFacil;
use App\Models\EmpresaUsuarioPersonal;
use App\Models\Asociacion;
use App\Models\TokenServicio;
use App\Models\EmpresaToken;
use App\Models\claseSiat;
use App\Models\EmpresaSucursal;
use App\Models\PuntoVenta;
use App\Http\Controllers\UsuarioEmpresaController;

use Exception;
use App\Soporte\Error;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;


class SincronizacionSiatController extends Controller
{
    const _API = 'http://apirest.facturatech.com.bo/api/';
    
    public function SincronizacionSiat(Request $request){

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $request->validate([
                'tnEmpresa'=> 'required',
                'tnTipo'=> 'required',
            ]);

            $oUser = $request->user();
            $empresaPersonal = EmpresaUsuarioPersonal::where('Empresa', $request->tnEmpresa)
            ->where('Usuario', $oUser->Usuario)
            ->where('CodigoAmbiente', $oUser->CodigoAmbiente)
            ->where('Estado', 1)
            ->get();

        if ($empresaPersonal->isEmpty()) {//si la empresa no esta asocioada con el usuario
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = "empresa invalida para el usuario";
            $oPaquete->messageMostrar = "0";
            $oPaquete->values = 1;
        } else {
            $result = $this->SincronizacionSiatReturn($request->tnEmpresa, $request->tnTipo);

            $oPaquete->error = 0; // Indicar que hubo un error
            $oPaquete->status = 1; // Indicar que hubo un error
            $oPaquete->messageSistema = "comando ejecutado";
            $oPaquete->message = "ejecucion sin inconvenientes";
            $oPaquete->messageMostrar = "0";
            $oPaquete->values = $result;

            return response()->json($oPaquete);
        }

        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }


    function encryptData($data)
    {
        return Crypt::encryptString($data);
    }
 
    public function decryptData($data)
    {
        return Crypt::decryptString($data);
    }

    public function userApiToken(){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $empresasController = new UsuarioEmpresaController();
            $oEmpresas = $empresasController->misEmpresasReturn();
            $oUser = $request->user();
            
            $oUserApiToken = TokenServicio::where('Empresa', $oEmpresas[0]->Empresa)
            ->where('CodigoAmbiente',$oUser->CodigoAmbiente)
            ->get();
            
            //llega asta aqui
            if ($oUserApiToken->isEmpty()) {//si el usuario ya esta asocioado con un ApiToken
                return 'esta vacio';
            }else{
                $oPaquete->error = 0; // Indicar que hubo un error
                $oPaquete->status = 1; // Indicar que hubo un error
                $oPaquete->message = "Comando ejecutado";
                $oPaquete->values = $oUserApiToken[0];
            }

            return response()->json($oPaquete); // Devolver una respuesta con código 500
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }

    public function claseSiat(){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $oClaseSiat = claseSiat::all();

            $oPaquete->error = 0; // Indicar que hubo un error
            $oPaquete->status = 1; // Indicar que hubo un error
            $oPaquete->messageSistema = "comando ejecutado";
            $oPaquete->values = $oClaseSiat; // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }   

    public function reconnect(){

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oUser = Auth::user();


        try{
            $oUserApiToken = TokenServicio::where('ApiToken', $oUser->api_token)
            ->first();

            //enviando credenciales estaticas para las pruevas
            $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
            //$token = request()->bearerToken();
            $url = self::_API . 'login';
            $data = array(
                'TokenService' => $oUserApiToken->TokenService, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                'TokenSecret' => $oUserApiToken->TokenSecret
             );
            $header=[
                    'Accept'        => 'application/json',
                    ];
            $response = $client->post($url, ['headers' => $header,
                                                'json' => $data]);
            $result = json_decode($response->getBody()->getContents());

            //llega asta aqui
            $oUserApiToken->TokenBearer = ($result->values);
            $oUserApiToken->save();




            return response()->json($result); // Devolver una respuesta con código 500
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }

    public function loginApiToken(Request $request){

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            DB::beginTransaction(); // Iniciar la transacción
            $request->validate([
                'tcService'=> 'required',
                'tcSecret'=> 'required',
            ]);
            $oUser = Auth::user();

            //enviando credenciales estaticas para las pruevas
            $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
            //$token = request()->bearerToken();
            $url = self::_API . 'login';
            $data = array(
                'TokenService' => $request->tcService, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                'TokenSecret' => $request->tcSecret
             );

            $header=[
                    'Accept'        => 'application/json',
                    ];
            $response = $client->post($url, ['headers' => $header,
                                                'json' => $data]);
            $result = json_decode($response->getBody()->getContents());
            if($result->values != null){
                $requestData = ([
                    'ApiToken' => $oUser->api_token,
                    'TokenService' => ($request->tcService),
                    'TokenSecret' => ($request->tcSecret),
                    'TokenBearer' => ($result->values),
                ]);
                //llega asta aqui
                $oUserApiToken = TokenServicio::where('ApiToken', $oUser->api_token)
                    ->first();
                if ($oUserApiToken) {//si el usuario ya esta asocioado con un ApiToken
                    $oUserApiToken->TokenService=$requestData['TokenService'];
                    $oUserApiToken->TokenSecret=$requestData['TokenSecret'];
                    $oUserApiToken->TokenBearer=$result->values;
                    $oUserApiToken->save();
                    //return response()->json($result);
                }else{
                    $oUserTokenSiat = TokenServicio::create($requestData);
                }

            }
            DB::commit(); // Confirmar la transacción si todo va bien
            return response()->json($result);

        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }

    
    public function reconecTokenReturn($tnEmpresa){

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $oUser = Auth::user();
            
            $oUserApiToken = EmpresaToken::where('Empresa',$tnEmpresa)
            ->where('Serial',1)->first();
            
            if(!$oUserApiToken){
                $oPaquete->error = 0; // Indicar que hubo un error
                $oPaquete->status = 1; // Indicar que hubo un error
                $oPaquete->message = 'la Empresa no tiene habilitado sus credenciales para la comnectar a Siat'; // Agregar detalles del error
                $oPaquete->values = null; // Agregar detalles del error
                return response()->json($response);
            }
            //enviando credenciales estaticas para las pruevas
            $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
            //$token = request()->bearerToken();
            $url = self::_API . 'login';
            $data = array(
                'TokenService' => $oUserApiToken->TokenService, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                'TokenSecret' => $oUserApiToken->TokenSecret
            );
            $header=[
                'Accept'        => 'application/json',
            ];
            $response = $client->post($url, ['headers' => $header,
            'json' => $data]);
            $result = json_decode($response->getBody()->getContents());
            if($result->values != null){
                $oTokenService = ToKenServicio::where('Empresa',$tnEmpresa)->first();
                if($oTokenService){
                    $oTokenService->TokenBearer=$result->values;
                    $oTokenService->save();
                }else{
                    $oTokenService=TokenServicio::create([
                       'TokenService' =>  $oUserApiToken->TokenService,
                       'TokenSecret' =>  $oUserApiToken->TokenSecret,
                       'TokenBearer' =>  $result->values,
                       'Empresa' =>  $tnEmpresa,
                    ]);
                }
            }
            $oPaquete->error = 0; // Indicar que hubo un error
            $oPaquete->status = 1; // Indicar que hubo un error
            $oPaquete->message = 'comando ejecutado'; // Agregar detalles del error
            $oPaquete->values = $result->values; // Agregar detalles del error
            return response()->json($oPaquete);
        }catch (\Exception $e) {

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    }

    public function SincronizacionSiatReturn($tnEmpresa, $tnTipo){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $oUser = Auth::user();
            //para el modulo de sincronizacion con siat // se requiere manejar credenciales con ambiente de produccion
            $oAsociacion = Asociacion::where('Empresa', $tnEmpresa)
            ->where('CodigoAmbiente', 1) // Codigo de ambiente de produccion 1 y de prueba 2  
            ->first();
            $result=null;
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();    
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/sincronizacionsiat';
                    $data = array(
                        'tcCredencial' => $oAsociacion->AsociacionCredencial, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                        'tnTipo' => $tnTipo
                    );
                    $header=[
                            'Accept'        => 'application/json',
                            'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                            ];
                    $response = $client->post($url, ['headers' => $header,
                                                        'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                }
                if( (!$result) || ($result===null) || ($result->values === null) ){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    if( (!$tieneToken) ){
                        $tnEmpresa=1;
                    }
                    if($result->values === 1){
                        return response()->json($result);
                    }
                }
            } while ( ($result===null) || ($result->values === null) );
            return response()->json($result->values);

        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage().',fallo en la coneccion '; // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    }

    public function ValidacionNitReturn($tnEmpresa, $tnNitVerificacion){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $oUser = Auth::user();
            
            $oAsociacion = Asociacion::where('Empresa', $tnEmpresa)
            ->where('CodigoAmbiente', $oUser->CodigoAmbiente) // Codigo de ambiente de produccion 1 y de prueba 2  
            ->get();

            $result =null;
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/verificarnit';
                    $data = array(
                        'tcCredencial' => $oAsociacion[0]->AsociacionCredencial, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                        'tnNitVerificacion' => $tnNitVerificacion
                    );
                    $header=[
                            'Accept'        => 'application/json',
                            'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                            ];
                    $response = $client->post($url, ['headers' => $header,
                                                        'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                //    $result=$result->values;
                }
                if(($result===null)||($result->values === null)){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    if(($tieneToken['error']==1)){
                        $tnEmpresa=1;
                    }
                }
            } while (($result===null)||($result->values === null));
            return response()->json($result->values->RespuestaVerificarNit);

        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    return $oUser;
    }
    
    //($oVenta->Empresa, $lcEmpresa, $lcPuntoVenta, $lcFactura, $lcFacturaDetalle, $lcFacturaSector, $lcFacturaDetalleSector)
    public function EmisionEnLineaMultiSectorReturn($tnEmpresa, $tcEmpresa, $tcPuntoVenta, $tcFactura, $tcFacturaDetalle,$tcFacturaSector,$tcFacturaDetalleSector){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $oUser = Auth::user();
            
            $result =null; 
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                Error::guardarLog(true,"Emision_Empresa_.$tnEmpresa",  "Parametro Token Servicio",$oUserApiToken);
                    
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/emitirfacturaonlinemultisector';
                    Error::guardarLog(true,"Emision_Empresa_.$tnEmpresa",  "Parametro Individuales Para el Registro de Emision Multi Sector"
                        ,"tcEmpresa" ,  $tcEmpresa
                        ,"tcPuntoVenta" ,  $tcPuntoVenta
                        ,"tcFactura" ,  $tcFactura
                        ,'tcDetalles',$tcFacturaDetalle
                        ,'tcFacturaSector',$tcFacturaSector
                        ,'tcDetallesSector',$tcFacturaDetalleSector
                        );
                    
                    $data = array(
                        'tcEmpresa' => utf8_encode($tcEmpresa),
                        'tcPuntoVenta' => utf8_encode($tcPuntoVenta),
                        'tcFactura' => utf8_encode($tcFactura),
                        'tcFacturaDetalle' => utf8_encode($tcFacturaDetalle),
                        'tcFacturaSector' => utf8_encode($tcFacturaSector),
                        'tcFacturaDetalleSector' => utf8_encode($tcFacturaDetalleSector)
                    );

                    Error::guardarLog(true,"Emision_Empresa_.$tnEmpresa",  "Parametro Mandados" 
                        ,  "data" ,  json_encode($data));

                    $header=[
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                    ];
                    $response = $client->post($url, ['headers' => $header,
                        'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                    //    $result=$result->values;
                    Error::guardarLog(true,"Emision_Empresa_.$tnEmpresa",  "Resultados Obtenidos"
                        ,  "result" ,  $result );
                }
                if(($result->messageSistema === 'Token has expired')){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    $tieneToken = $tieneToken->original;
                    Error::guardarLog(true,"Emision_Empresa_.$tnEmpresa",  "Token Expirdado Actualizando"
                        ,  "result" ,  $tieneToken );
                    if(($tieneToken->error === 1)){
                        $tnEmpresa=1;
                    }
                    $result = null;
                }
            } while (($result===null));
            
            if($result->error ===1 ){
                if($result->status ===1 ){
                    return response()->json($result);
                }
                throw new CustomException($result->message);
            }else {
                return response()->json($result);
            }
         
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error
            Error::guardarLog(true,"Error_Emision_Empresa_.$tnEmpresa",  "Resultados Obtenidos"
                ,  "error" ,  $e->getMessage().$e->getLine());
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }

    
    public function EmisionEnLineaReturn($tnEmpresa, $tcEmpresa, $tcPuntoVenta, $tcFactura, $tcFacturaDetalle){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            
            $oUser = Auth::user();
            
            $oAsociacion = Asociacion::where('Empresa', $tnEmpresa)
            ->where('CodigoAmbiente', $oUser->CodigoAmbiente) // Codigo de ambiente de produccion 1 y de prueba 2
            ->get();
            
            $result =null;
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/emitirfacturaonline';
                    Error::guardarLog(true,"Emision_Empresa_.$tnEmpresa",  "Parametro Individuales"
                        ,"tcEmpresa" ,  $tcEmpresa
                        ,"tcPuntoVenta" ,  $tcPuntoVenta
                        ,"Factura" ,  $tcFactura
                        ,'Detalles',$tcFacturaDetalle);
                    
                    $data = array(
                        'tcEmpresa' => utf8_encode($tcEmpresa),
                        'tcPuntoVenta' => utf8_encode($tcPuntoVenta),
                        'tcFactura' => utf8_encode($tcFactura),
                        'tcFacturaDetalle' => utf8_encode($tcFacturaDetalle)
                    );
                    
                    Error::guardarLog(true,"Emision_Empresa_.$tnEmpresa",  "Parametro Mandados"
                        ,  "data" ,  json_encode($data));
                    
                    $header=[
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                    ];
                    $response = $client->post($url, ['headers' => $header,
                        'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                    //    $result=$result->values;
                    Error::guardarLog(true,"Emision_Empresa_.$tnEmpresa",  "Resultados Obtenidos"
                        ,  "result" ,  $result );
                }
                if(($result===null)){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    if(($tieneToken['error']==1)){
                        $tnEmpresa=1;
                    }
                }
            } while (($result===null));
            if($result->error ===1 ){
                if($result->status ===1 ){
                    return response()->json($result);
                }
                throw new CustomException($result->message);
            }else {
                return response()->json($result);
            }
            
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error
            Error::guardarLog(true,"Error_Emision_Empresa_.$tnEmpresa",  "Resultados Obtenidos"
                ,  "error" ,  $e->getMessage().$e->getLine());
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }
    
    
    
    
    public function buscarFactura(Request $request){
        
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        set_time_limit(300);
        ini_set('memory_limit', '38192M');
        try{
            $request->validate([
                'tcFechaIni'=> 'required',
                'tcFechaFin'=> 'required',
                'tnEstado'=> 'required',
            ]);
            $oUser = Auth::user();
            $oAsociacion = Asociacion::where('Empresa', $oUser->EmpresaSeleccionada)
            ->where('CodigoAmbiente', $oUser->CodigoAmbiente)
            ->get();
            
            $tcFechaIni = substr($request->tcFechaIni, 0, 10);
            $tcFechaFin = substr($request->tcFechaFin, 0, 10);
            
            $result =null;
            $tnEmpresa = $oUser->EmpresaSeleccionada;
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/busquedafacturas';
                    $data = array(
                        'tcCredencial' => $oAsociacion[0]->AsociacionCredencial, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                        'tcFechaIni' => $tcFechaIni,
                        'tcFechaFin'=> $tcFechaFin,
                        'tnEstado'=> $request->tnEstado,  
                        );
                    
                     $header=[
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                    ];
                    $response = $client->post($url, ['headers' => $header,'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());

                    if ($result->messageSistema === "Token has expired"){
                        $result = null;
                    }
                }
                if(($result===null)){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    $tieneToken = json_decode($tieneToken->content());
                    if(($tieneToken->error === 1)){
                        $tnEmpresa=1;
                    }
                }
            } while (($result===null));
            
            // Truncar la tabla (eliminar todos los datos)
            //DB::table('FACTURAAUXILIAR')->truncate();
            // Insertar datos masivos en la tabla 
            $facturas = $result->values;
            //DB::table('FACTURAAUXILIAR')->insertOrIgnore($factura);
            $filtro=[];
            if(filled($facturas)){
                //  Aplicando mas Filtros
                if (filled($request->tnSucursal) && $request->tnSucursal != "0" ) {
                    $Sucursal = +(EmpresaSucursal::where('Sucursal',$request->tnSucursal)->first()->CodigoSucursal);
                    array_push($filtro, ['Titulo' => 'CodigoSucursal', 'valor' => $Sucursal]);
                }
                
                if (filled($request->tnPuntoVenta) && $request->tnPuntoVenta != "0" ) {
                    $puntoVenta = +(PuntoVenta::where('PuntoVenta',$request->tnPuntoVenta)->first()->CodigoPuntoVenta);
                    array_push($filtro, ['Titulo' => 'CodigoPuntoVenta', 'valor' => $puntoVenta]);
                }
                
                if (filled($request->tcCliente) && $request->tcCliente != "" ) {
                    array_push($filtro, ['Titulo' => 'NombreRazonSocial', 'valor' => $request->tcCliente ]);
                }
                
                if (filled($request->tcTiempo) && $request->tcTiempo = "periodo" ) {
                    if((filled($request->tcHoraInicio) && $request->tcHoraInicio != "" )&&(filled($request->tcHoraFin) && $request->tcHoraFin != "" )){
                        $dato = implode(',', [$request->tcHoraInicio, $request->tcHoraFin]);
                        array_push($filtro, ['Titulo' => 'HoraEmision', 'valor' => $dato]);
                    }
                }
                
                if (filled($request->tbDuplicado) && $request->tbDuplicado === true ) {
                    // Obtener la frecuencia de cada edad
                    $facturaFrecuencia = array_count_values(array_map(function($factura) {
                        return implode(',', [$factura->NumeroFactura, $factura->CodigoSucursal, $factura->CodigoPuntoVenta]);
                    }, $facturas));
                        
                        $facturaRepetidas = array_keys(array_filter($facturaFrecuencia, function ($frecuencia) {
                            return $frecuencia > 1;
                        }));
                            array_push($filtro, ['Titulo' => 'Duplicado', 'valor' => $facturaRepetidas]);
                }   
            }

            $facturasFiltradas;
            if($filtro!=[]){
                $facturasFiltradas = array_filter($facturas, function ($factura) use ($filtro) {
                    foreach ($filtro as $filtroItem){
                        $titulo = $filtroItem['Titulo'];
                        $valor = $filtroItem['valor'];
                        // Verifica si la factura cumple con el filtro actual
                        switch ($titulo) {
                            case 'Duplicado':
                                $currentFactura = implode(',', [$factura->NumeroFactura, $factura->CodigoSucursal, $factura->CodigoPuntoVenta]);
                                if (!in_array($currentFactura, $valor)) {
                                    return false;
                                }
                                break;
                                
                            case 'NombreRazonSocial':
                                if (!Str::contains($factura->NombreRazonSocial, $valor)) {
                                    return false;
                                }
                                break;
                                
                            case 'HoraEmision':
                                $aHora = explode(',', $valor);
                                $horaInicio = Carbon::parse($aHora[0]);
                                $horaFin = Carbon::parse($aHora[1]);
                                $hora = Carbon::parse($factura->HoraEmision);
                                
                                if (!$hora->between($horaInicio, $horaFin)) {
                                    return false;
                                }
                                break;
                                
                            default:
                                if ($factura->$titulo != $valor) {
                                    return false;
                                }
                                break;
                        }
                    }
                    return true; // Si cumple con todos los filtros, se incluye en los resultados
                }); 
                $facturasFiltradas = array_values($facturasFiltradas);
                $numeroDeTuplas = count($facturasFiltradas);
                $result->message = " Cantidad de facturas = $numeroDeTuplas";
                    
            }else{
                $facturasFiltradas = $facturas;
            }
            


            $result->values = $facturasFiltradas;
            return response()->json($result);
            
           
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }
    
    
    
    
    public function buscarFacturaReturn($tdFechaInicio,$tdFechaFin,$tnEstado){
        set_time_limit(300);
        ini_set('memory_limit', '38192M');
        $oUser = Auth::user();
        $tnEmpresa = $oUser->EmpresaSeleccionada;
        try{
        
            $oAsociacion = Asociacion::where('Empresa', $tnEmpresa)
            ->where('CodigoAmbiente', $oUser->CodigoAmbiente)
            ->get();
            $result =null;
            
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/busquedafacturas';
                    $data = array(
                        'tcCredencial' => $oAsociacion[0]->AsociacionCredencial, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                        'tcFechaIni' => $tdFechaInicio,
                        'tcFechaFin'=> $tdFechaFin,
                        'tnEstado'=> $tnEstado,
                    );
                    $header=[
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                    ];
                    $response = $client->post($url, ['headers' => $header,'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                    if ($result->messageSistema === "Token has expired"){
                        $result = null;
                    }
                    
                }
                if(($result===null)){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    $tieneToken = json_decode($tieneToken->content());
                    if(($tieneToken->error === 1)){
                        $tnEmpresa=1;
                    }
                }
            } while (($result===null));
            
            return $result; 
        
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error

            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    
    }
        
    
    
    
   /*  intentanr no trabaja con parametro en las consultas Sino con objetos Dinamicos para no tener problemas con las futuras actualizacione 
    * 
    * 
    * Objet= (
    *      [0] => tdFechaInicio;
    *      [1] => tdFechaFin,
    *      [2] => tdHoraInicio,
    *      [3] => tdHoraFin,
    *      [4] => tnSucursal,
    *      [5] => tnPuntoVenta,
    *      [6] => tnCliente,
    *      [7] => //Dupliados
    *      ;
    * 
    * 
    * 
    * 
    * Mantener un 
    * */
    
    
   public function consultarEstadoReturn($tnEmpresa,$tcCUF, $tnNumeroFactura, $tnCodigoSucursal, $tnCodigoPuntoVenta){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
          $oUser = Auth::user();
          $oEmpresaUsuarioPersonal = EmpresaUsuarioPersonal:: where('Usuario',$oUser->Usuario)
          ->where('Empresa',$oUser->EmpresaSeleccionada)
          ->where('CodigoAmbiente',$oUser->CodigoAmbiente)
          ->first();
          $oPuntoVenta = PuntoVenta:: find($oEmpresaUsuarioPersonal->PuntoVenta);
          
          $oAsociacion = Asociacion::where('Empresa', $oUser->EmpresaSeleccionada)
          ->where('CodigoSistema', $oPuntoVenta->CodigoSistema)
          ->where('CodigoAmbiente', $oUser->CodigoAmbiente)
          ->get();
                           
          $result =null;
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/consultarestadofactura';
                    $data = array(
                        'tcCredencial' => $oAsociacion[0]->AsociacionCredencial,
                        'tcCUF' => $tcCUF,
                        'tnNumeroFactura' => $tnNumeroFactura,
                        'tnCodigoSucursal' => $tnCodigoSucursal,
                        'tnCodigoPuntoVenta' => $tnCodigoPuntoVenta
                    );
                    $header=[
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                    ];
                    $response = $client->post($url, ['headers' => $header,
                        'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                    //    $result=$result->values;
                    if ($result->messageSistema === "Token has expired"){
                        $result = null;
                    }
                }
                if(($result===null)){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    if(($tieneToken['error']==1)){
                        $tnEmpresa=1;
                    }
                }
            } while (($result===null));
            if($result->error ===1 ){
                throw new Exception($result->message);
            }else {
                return response()->json($result);
            }
            
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }


    public function obtenerFactura(Request $request){
        
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $request->validate([
                'tcCuf'=> 'required',
            ]);
            $oUser = Auth::user();
            
            $oEmpresaUsuarioPersonal = EmpresaUsuarioPersonal:: where('Usuario',$oUser->Usuario)
            ->where('Empresa',$oUser->EmpresaSeleccionada)
            ->where('CodigoAmbiente',$oUser->CodigoAmbiente)
            ->first();
            $oPuntoVenta = PuntoVenta:: find($oEmpresaUsuarioPersonal->PuntoVenta);
            
            $oAsociacion = Asociacion::where('Empresa', $oUser->EmpresaSeleccionada)
            ->where('CodigoSistema', $oPuntoVenta->CodigoSistema)
            ->where('CodigoAmbiente', $oUser->CodigoAmbiente)
            ->get();
            $result =null;
            $tnEmpresa = $oUser->EmpresaSeleccionada;
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/obtenerfacturadigital';
                    $data = array(
                        'tcCUF' => $request->tcCuf,
                    );
                    $header=[
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                    ];
                    $response = $client->post($url, ['headers' => $header,'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                    
                    if ($result->messageSistema === "Token has expired"){
                        $result = null;
                    }
                }
                if(($result===null)){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    $tieneToken = json_decode($tieneToken->content());
                    if(($tieneToken->error === 1)){
                        $tnEmpresa=1;
                    }
                }
            } while (($result===null));
            return response()->json($result);
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error
            
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage().' '.$e->getFile().' '.$e->getLine(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
        return $oUser;
    }

    

    public function anularFacturaReturn($tnEmpresa, $tcCUF){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $oUser = Auth::user();
            $oEmpresaUsuarioPersonal = EmpresaUsuarioPersonal:: where('Usuario',$oUser->Usuario)
            ->where('Empresa',$oUser->EmpresaSeleccionada)
            ->where('CodigoAmbiente',$oUser->CodigoAmbiente)
            ->first();
            $oPuntoVenta = PuntoVenta:: find($oEmpresaUsuarioPersonal->PuntoVenta);
            
            $oAsociacion = Asociacion::where('Empresa', $oUser->EmpresaSeleccionada)
            ->where('CodigoSistema', $oPuntoVenta->CodigoSistema)
            ->where('CodigoAmbiente', $oUser->CodigoAmbiente)
            ->first();
            Error::guardarLog(true,"Anuladas_Empresa_.$tnEmpresa",  "Peticion Individuales Para la Anulacion de Factura"
                ,'Asociacion',$oAsociacion
                ,'CUF',$tcCUF
                );
            
            $result =null;
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/anularfactura';
                    $data = array(
                        'tcCredencial' => $oAsociacion->AsociacionCredencial, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                        'tcCUF' => $tcCUF
                    );
                    Error::guardarLog(true,"Anuladas_Empresa_.$tnEmpresa",  "Parametros Para la Anulacion de Factura"
                        ,'data',$data
                        );
                    
                    $header=[
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                    ];
                    $response = $client->post($url, ['headers' => $header,
                        'json' => $data]);
                    $result = json_decode($response->getBody()->getContents());
                    
                    Error::guardarLog(true,"Anuladas_Empresa_.$tnEmpresa",  "Parametro Devueltos de la Anulacion"
                        ,'Resultado',$result
                        );
                }
                if(($result===null)){
                    $tieneToken = $this->reconecTokenReturn($tnEmpresa);
                    if(($tieneToken['error']==1)){
                        $tnEmpresa=1;
                    }
                }
            } while (($result===null));
            
            return response()->json($result);
            
        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error
            Error::guardarLog(true,"Anuladas_Empresa_.$tnEmpresa",  "Error Devueltos de la Anulacion"
                ,'error',$e->getMessage().' '.$e->getFile().' '.$e->getLine()
                ,'dato',$e
                );
            
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage().',fallo en la coneccion '; // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    }
    
    
    
    
    
    
}