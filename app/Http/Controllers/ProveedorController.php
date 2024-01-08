<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;
use App\Models\TipoDocumentoIdentidad;
use App\Models\EmpresaUsuarioPersonal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\ConsultaController;
use Carbon\Carbon;



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
        $oUser = auth()->user();
        $oProveedor = Proveedor::where('Empresa',$oUser->EmpresaSeleccionada)->get();

        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = $oProveedor ;
        return response()->json($oPaquete);
    }

    /**
     * Mostrar una lista del recurso.
     * @metodo create()
     * @autor   jakeline
     * @fecha   04-01-2024
     * @parametro
     * @return Object $oPaquete
     */
    public function create()
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        
        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();
        
        $oUser = auth()->user();
        $lnEmpresaSeleccionada = $oUser->EmpresaSeleccionada;
        
        $oProveedor ;
        if($oEmpresas){
            $oProveedor = Proveedor::where('Empresa',$lnEmpresaSeleccionada)->get();
        }
        $oTipoDocumentoIdentidad = TipoDocumentoIdentidad::select('TipoDocumentoIdentidad as Tipo','Nombre')->get();
        
        
        
        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [
            $oEmpresas,
            $oProveedor,
            $oTipoDocumentoIdentidad,
            $lnEmpresaSeleccionada];
        return response()->json($oPaquete);
    }
    
    /**
     * Almacene un recurso reciÃ©n creado en el almacenamiento.
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
            'Empresa'=> 'required',
            'TipoDocumento'=> 'required',
            'Nombre'=> 'required',
            'Documenton'=> 'required',
            'NombrePersonalAcargo'=> 'required',
            'CodigoInterno'=> 'required',
            'ContactoPersonalAcargo'=> 'required'
        ]);

        $oEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;

        $oProveedor = Proveedor::where('TipoDocumento', $request->TipoDocumento)
        ->where('Documenton', $request->Documenton)
        ->where('Empresa', $request->Empresa)
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

            if($oEmpresaSeleccionada){
                $oProveedor = Proveedor::where('Nombre', $request->Nombre)
                ->where('TipoDocumento', $request->TipoDocumento)
                ->where('Documenton', $request->Documenton)
                ->where('Empresa', $oEmpresaSeleccionada)
                ->get();
            }


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
                    'Empresa' => $oEmpresaSeleccionada,
                ]);

                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Proveedir creado";
                $oPaquete->message = "se creo al proveedor";
                $oPaquete->values = 1;
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
     * Muestra el recurso especificado.
     * @metodo edit()
     * @autor   jakeline
     * @fecha   05-01-2024
     * @parametro int tnProveedor
     * @return Object $oPaquete
     */
    public function edit($tnProveedor)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        
        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();
        
        $oUser = auth()->user();
        $lnEmpresaSeleccionada = $oUser->EmpresaSeleccionada;
        
        $oProveedor ;
        $oProveedor = Proveedor::find($tnProveedor);
        $oTipoDocumentoIdentidad = TipoDocumentoIdentidad::select('TipoDocumentoIdentidad as Tipo','Nombre')->get();
        
        
        
        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [
            $oEmpresas,
            $oProveedor,
            $oTipoDocumentoIdentidad,
            $lnEmpresaSeleccionada];
        return response()->json($oPaquete);
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
            'Empresa' => 'nullable',
        ]);
        $oProveedor = Proveedor::find($InProveedor);
        // validad si existe
        if (!$oProveedor) {
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Proveedor No Encontrado";
            $oPaquete->message = "a ocurrido un error  ";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{
            //actualizacion de datoss del Proveedor            
            $sql= "UPDATE PROVEEDOR
                    SET TipoDocumento = '".$request->TipoDocumento."',
                        Documenton = '".$request->Documenton."',
                        Nombre = '".$request->Nombre."',
                        CodigoInterno = '".$request->CodigoInterno."',
                        Correo = '".$request->Correo."',
                        DomicilioFiscal = '".$request->DomicilioFiscal."',
                        ContactoFiscal = '".$request->ContactoFiscal."',
                        NombrePersonalAcargo = '".$request->NombrePersonalAcargo."',
                        ContactoPersonalAcargo = '".$request->ContactoPersonalAcargo."'
                    WHERE Proveedor =".$InProveedor.";";
            
            DB::select($sql);
            $oProveedor = Proveedor::find($InProveedor);
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
