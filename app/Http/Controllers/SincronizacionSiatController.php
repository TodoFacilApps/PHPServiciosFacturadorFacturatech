<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Modelos\mPaquetePagoFacil;
use App\Models\EMPRESAUSUARIOPERSONAL;
use App\Models\Asociacion;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use App\Models\TokenServicio;
use App\Models\EmpresaToken;
use App\Models\claseSiat;
use App\Http\Controllers\UsuarioEmpresaController;



class SincronizacionSiatController extends Controller
{
    //
    const _API = 'http://apirest.facturatech.com.bo/api/';

    public function accederSiat(){

    }

    public function SincronizacionSiat(Request $request){

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $request->validate([
                'tnEmpresa'=> 'required',
                'tnTipo'=> 'required',
            ]);

            $oUser = $request->user();
            $empresaPersonal = EMPRESAUSUARIOPERSONAL::where('Empresa', $request->tnEmpresa)
            ->where('Usuario', $oUser->Usuario)
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
            $oPaquete->values = $result ;

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

    // Función para desencriptar datos
    public function decryptData($data)
    {
        return Crypt::decryptString($data);
    }


    public function userApiToken(){
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $empresasController = new UsuarioEmpresaController();
            $oEmpresas = $empresasController->misEmpresasReturn();

            $oUserApiToken = TokenServicio::where('Empresa', $oEmpresas[0]->Empresa)
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
            $oUserApiToken = EmpresaToken::where('Empresa',$tnEmpresa)
            ->where('Serial',1)->first();
            if(!$oUserApiToken){
                return ([
                    'values'=>'la Empresa no tiene habilitado sus credenciales para la comnectar a Siat',
                    'error'=>1
                ]);
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
            $oPaquete->values = 'comando ejecutado'; // Agregar detalles del error
            return response()->json($result->values);
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
            $oAsociacion = Asociacion::where('Empresa', $tnEmpresa)
            ->where('CodigoAmbiente', 1)
            ->get();

            $result =null;
            do{
                $oUserApiToken = TokenServicio::where('Empresa', $tnEmpresa)->first();
                if($oUserApiToken){
                    //enviando credenciales estaticas para las pruevas
                    $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                    $url = self::_API . 'servicio/sincronizacionsiat';
                    $data = array(
                        'tcCredencial' => $oAsociacion[0]->AsociacionCredencial, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                        'tnTipo' => $tnTipo
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
            return response()->json($result->values);

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

}
