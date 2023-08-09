<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;

class ProveedorController extends Controller
{
    /**
     * Mostrar una lista del recurso.
     * @metodo index()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro
     * @return Object $oPaquete
     */
    public function index()
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oProveedor = Proveedor::all();


        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = $oProveedor ;
        return response()->json($oPaquete);
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     * @metodo store()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro Request
     * @return Object $oPaquete
     */
    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $request->validate([
            'TipoDocumento'=> 'required',
            'Documenton'=> 'required',
            'Nombre'=> 'required',
            'CodigoInterno'=> 'required',
            'Correo' => 'required',
            'DomicilioFiscal' => 'nullable',
            'ContactoFiscal' => 'required',
            'NombrePersonalAcargo' => 'required',
            'ContactoPersonalAcargo' => 'nullable',

        ]);

        $oProveedor = Proveedor::where('TipoDocumento', $request->TipoDocumento)
        ->where('Documenton', $request->Documenton)
        ->get();
        // validad si existe
        if (!$oProveedor->isEmpty()) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Proveedor dublicado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{

            $oProveedor = Proveedor::where('Nombre', $request->Nombre)->get();
            // validad si existe
            if (!$oProveedor->isEmpty()) {

                $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "Error Codigo Nombre Dublicado";
                    $oPaquete->message = "a ocurrido un error  ";
                    $oPaquete->values = null;
                    return response()->json($oPaquete);
            }else{
                $oProveedor = Proveedor::create([
                    'TipoDocumento'=> $request->TipoDocumento,
                    'Documenton'=> $request->Documenton,
                    'Nombre'=> $request->Nombre,
                    'CodigoInterno'=> $request->CodigoInterno,
                    'Correo' => $request->Correo,
                    'DomicilioFiscal' => $request->DomicilioFiscal,
                    'ContactoFiscal' => $request->ContactoFiscal,
                    'NombrePersonalAcargo' => $request->NombrePersonalAcargo,
                    'ContactoPersonalAcargo' => $request->ContactoPersonalAcargo,
                ]);

                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Proveedir creado";
                $oPaquete->message = "se creo al proveedor";
                $oPaquete->values = $oProveedor;
                return response()->json($oPaquete);
            }
        }
    }

    /**
     * Muestra el recurso especificado.
     * @metodo show()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro int InProveedor
     * @return Object $oPaquete
     */
    public function show($InProveedor)
    {

        $oProveedor = Proveedor::find($InProveedor);
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        if (!$oProveedor) {
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Proveedor no encontrado";
            $oPaquete->message = "a ocurrido un error";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{
            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "sin errores";
            $oPaquete->message = "Proveedor encontrado";
            $oPaquete->values = $oProveedor;
            return response()->json($oPaquete);
        }
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     * @metodo update()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro Request $request,int InProveedor
     * @return Object $oPaquete
     */
    public function update(Request $request,$InProveedor)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'TipoDocumento'=> 'required',
            'Documenton'=> 'required',
            'Nombre'=> 'required',
            'CodigoInterno'=> 'required',
            'Correo' => 'required',
            'DomicilioFiscal' => 'nullable',
            'ContactoFiscal' => 'required',
            'NombrePersonalAcargo' => 'required',
            'ContactoPersonalAcargo' => 'nullable',
            'Estado' => 'nullable',

        ]);

        $oProveedor = Proveedor::find($InProveedor)->get();
        // validad si existe
        if ($oProveedor->isEmpty()) {
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Proveedor No Encontrado";
            $oPaquete->message = "a ocurrido un error  ";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{
            //actualizacion de datoss del Proveedor
            $oProveedor->TipoDocumento = $request->TipoDocumento;
            $oProveedor->Documenton = $request->Documenton;
            $oProveedor->Nombre = $request->Nombre;
            $oProveedor->CodigoInterno = $request->CodigoInterno;
            $oProveedor->Correo = $request->Correo;
            $oProveedor->DomicilioFiscal = $request->DomicilioFiscal;
            $oProveedor->ContactoFiscal = $request->ContactoFiscal;
            $oProveedor->NombrePersonalAcargo = $request->NombrePersonalAcargo;
            $oProveedor->ContactoPersonalAcargo = $request->ContactoPersonalAcargo;
            $oProveedor->Estado = $request->Estado;
            $oProveedor->save();
            //respuesta de confirmacion
            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Proveedor Actualizado";
            $oPaquete->message = "se actualizo al Proveedor";
            $oPaquete->values = $oProveedor;
            return response()->json($oPaquete);
        }
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     * @metodo destroy()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro int InProveedor
     * @return Object $oPaquete
     */
    public function destroy($InProveedor)
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oProveedor = Proveedor::find($InProveedor);
        // validad si existe
        if (!$oProveedor) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Proveedor no Encontrado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{
            // validad si existe
            if ($oProveedor->Estado != 1) {

                $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "Error Proveedor Inabilitado";
                    $oPaquete->message = "a ocurrido un error  ";
                    $oPaquete->values = null;
                    return response()->json($oPaquete);
            }else{

                $oProveedor->Estado = 2;
                $oProveedor->save();

                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Proveedor Suspeendido";
                $oPaquete->message = "se suspendio al proveedor";
                $oPaquete->values = $oProveedor;
                return response()->json($oPaquete);
            }
        }

    }

}
