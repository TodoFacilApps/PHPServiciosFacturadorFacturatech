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
use App\Models\claseSiat;



class SincronizacionSiatController extends Controller
{
    //
    const _API = 'http://apirest.facturatech.com.bo/api/';

    public function accederSiat(){

    }

    public function SincronizacionSiat(Request $request){

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            DB::beginTransaction(); // Iniciar la transacción
            $request->validate([
                'tcToken'=> 'required',
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

            $oAsociacion = Asociacion::where('Empresa', $request->tnEmpresa)
            ->where('CodigoAmbiente', 1)
            ->get();
            //enviando credenciales estaticas para las pruevas
            $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
            //$token = request()->bearerToken();
            $url = self::_API . 'servicio/sincronizacionsiat';
            $data = array(
                'tcCredencial' => $oAsociacion[0]->AsociacionCredencial, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                'tnTipo' => $request->tnTipo
             );
            $header=[
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $request->tcToken // Reemplaza esto con tu token
                    ];
            $response = $client->post($url, ['headers' => $header,
                                                'json' => $data]);
            $result = json_decode($response->getBody()->getContents());
        //            $result=$result->values;

            DB::commit(); // Confirmar la transacción si todo va bien
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

            $oUser = Auth::user();
            $oUserApiToken = TokenServicio::where('ApiToken', $oUser->api_token)
            ->get();

            //llega asta aqui
            if ($oUserApiToken->isEmpty()) {//si el usuario ya esta asocioado con un ApiToken
                return 'esta vacio';
            }else{
                $oPaquete->error = 0; // Indicar que hubo un error
                $oPaquete->status = 1; // Indicar que hubo un error
                $oPaquete->messageSistema = "Comando ejecutado";
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
                    $oUserTokenSiat = TokenServicio::create($requestData);
                    //return response()->json($result);
                }else{
                    $oUserApiToken->TokenService=$requestData['TokenService'];
                    $oUserApiToken->TokenSecret=$requestData['TokenSecret'];
                    $oUserApiToken->TokenBearer=$result->values;
                    $oUserApiToken->save();
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


    public function reconecTokenReturn(){

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $oUser = Auth::user();
            //llega asta aqui
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
            if($result->values != null){
                $oUserApiToken->TokenBearer=$result->values;
                $oUserApiToken->save();
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

            $oUser = Auth::user();
            $result;
            do{
                $oUserApiToken = TokenServicio::where('ApiToken', $oUser->api_token)
                ->first();

                //enviando credenciales estaticas para las pruevas
                $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                //$token = request()->bearerToken();
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
            //            $result=$result->values;
                if($result->values==null){
                    $this->reconecTokenReturn();
                }
            } while ($result->values==null);
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
