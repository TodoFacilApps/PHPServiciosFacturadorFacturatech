<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\TokenServicio;


class TokenServicioController extends Controller
{
    //
    function encryptData($data)
    {
        return Crypt::encryptString($data);
    }

    // Función para desencriptar datos
    public function decryptData($data)
    {
        return Crypt::decryptString($data);
    }
    public function store(Request $request)
    {
        $oInput = $request->validate([
            'ApiToken' => 'required|string',
            'Service' => 'required|string',
            'Secret' => 'required|string',
            'Bearer' => 'required|string',
        ]);

        $requestData = $request->all();
        $requestData['ApiToken'] = $requestData['ApiToken'];
        $requestData['Service'] = $this->encryptData($requestData['Service']);
        $requestData['Secret'] = $this->encryptData($requestData['Secret']);
        $requestData['Bearer'] = $this->encryptData($requestData['Bearer']);

        $token = TokenServicio::create($requestData);
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Token Almacenado",
            'messageMostrar'=> 'token guardado',
            'messageSistema'=> 'consulta realizada',
            'values'=> [$token]
        ]);
    }
    public function show(Request $request)
    {
        $request->validate([
            'ApiToken' => 'required',
        ]);

        //return $request;
        $oInput = TokenServicio :: where('ApiToken',$request->ApiToken)->first();

        // Decrypt the properties of the model instance
        $oInput->Service = $this->decryptData($oInput->Service);
        $oInput->Secret = $this->decryptData($oInput->Secret);
        $oInput->Bearer = $this->decryptData($oInput->Bearer);
        return $oInput;

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message'=> "Token Almacenado",
            'messageMostrar'=> 'token guardado',
            'messageSistema'=> 'consulta realizada',
            'values'=> [$oInput]
        ]);
    }
}
