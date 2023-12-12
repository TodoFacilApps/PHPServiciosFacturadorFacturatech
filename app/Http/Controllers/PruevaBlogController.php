<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;


class PruevaBlogController extends Controller
{
    //




    public function ApiIndex()
    {
        try {
            $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
            //$token = request()->bearerToken();
            $url = 'http://apirest.facturatech.com.bo/api/servicio/obtenerempresasfacturatech';
            $data = array('tcCodigoCliente'=> 3859 );
            $header=[
                    'Accept'        => 'application/json'
                    ];
            $response = $client->post($url, ['headers' => $header,
                                                'json' => $data]);
            $result = json_decode($response->getBody()->getContents());
//            $result=$result->values;
            return response()->json($result);

        } catch (\Throwable $th) {
            return 'Error:TRY/CATCH]: '. $th->getMessage(). ' Linea: '. $th->getLine();
        }
    }

}
