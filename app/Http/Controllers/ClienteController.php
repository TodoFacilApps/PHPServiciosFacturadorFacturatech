<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\Cliente;
use App\Models\EmpresaUsuarioPersonal;
use App\Models\TokenServicio;
use App\Models\Asociacion;
use App\Models\Empresa;
use GuzzleHttp\Client;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\ClaseSiat;
use App\Models\ActividadEconomica;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\UsuarioEmpresaController;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class ClienteController extends Controller
{
    //

        /**
     * Mostrar una lista del recurso.
     * @metodo index()
     * @autor   jakeline
     * @fecha   25-08-2023
     * @parametro
     * @return Object $oPaquete
     */
    public function index()
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();

        $oClientes ;
        if($oEmpresas){
            $oClientes = Cliente::where('Empresa',$oEmpresas[0]->Empresa)->get();
        }

        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [$oEmpresas,$oClientes] ;
        return response()->json($oPaquete);
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     * @metodo store()
     * @autor   jakeline
     * @fecha   00-08-2023
     * @parametro Request
     * @return Object $oPaquete
     */
    public function store(Request $request)
    {
      $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
          DB::beginTransaction(); // Iniciar la transacción
          $request->validate([
                'tnEmpresa'=> 'required',
                'tcNombre'=> 'required',
                'tcApellidos'=> 'required',
                'tnTipoDocumento'=> 'required',
                'tcDocumento'=> 'required',
                'tcDireccion'=> 'nullable',
                'tcEmail'=> 'nullable',
                'tcTelefono'=> 'nullable',
            ]);

            $oUser = Auth::user();
            $empresasController = new UsuarioEmpresaController();
            if($empresasController->esMiEmpresa($request->tnEmpresa)==1){

                $oCliente = Cliente::where('TipoDocumento',$request->tcDocumento)
                ->where('Documento',$request->tcDocumento)->exists();
                if($oCliente){
                    $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "a ocurrido un error";
                    $oPaquete->message = "el documento del cliente ya a sido registrado anteriormente";
                    $oPaquete->values = null;
                }else{
                    $oInput = $this->valueTClienteToCliente($request);
                    $oCliente = Cliente::create($oInput);

                    $oPaquete->error = 0; // Error Generico
                    $oPaquete->status = 1; // Sucedio un error
                    $oPaquete->messageSistema = "comando ejecutado";
                    $oPaquete->message = "cliente registrado";
                    $oPaquete->values = null;
                }
                DB::commit(); // Confirmar la transacción si todo va bien
            }else{
                $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "a ocurrido un error  ";
                $oPaquete->message = "el usuario no esta relacionado con la empresa";
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

    /**
     * Muestra el recurso especificado.
     * @metodo show()
     * @autor   jakeline
     * @fecha   00-08-2023
     * @parametro int InProveedor
     * @return Object $oPaquete
     */
    public function show($tnCliente)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();

        $oCliente = Cliente::find($tnCliente);

        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [$oEmpresas,$oCliente] ;
        return response()->json($oPaquete);

    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     * @metodo update()
     * @autor   jakeline
     * @fecha   00-08-2023
     * @parametro Request $request,int InProveedor
     * @return Object $oPaquete
     */
    public function update(Request $request,$tnCliente)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
          DB::beginTransaction(); // Iniciar la transacción
          $request->validate([
                'tnEmpresa'=> 'required',
                'tcNombre'=> 'required',
                'tcApellidos'=> 'required',
                'tnTipoDocumento'=> 'required',
                'tcDocumento'=> 'required',
                'tcDireccion'=> 'nullable',
                'tcEmail'=> 'nullable',
                'tcTelefono'=> 'nullable',
            ]);

            $oUser = Auth::user();
            $empresasController = new UsuarioEmpresaController();
            if($empresasController->esMiEmpresa($request->tnEmpresa)==1){

                $oCliente = Cliente::find($tnCliente);
                if($oCliente){
                    $oInput = $this->valueTClienteToCliente($request);
                    $oCliente->update($oInput);

                    $oPaquete->error = 0; // Error Generico
                    $oPaquete->status = 1; // Sucedio un error
                    $oPaquete->messageSistema = "comando ejecutado";
                    $oPaquete->message = "cliente Actualizado";
                    $oPaquete->values = null;
                }else{
                    $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "a ocurrido un error";
                    $oPaquete->message = "el cliente no a sido encontrado";
                    $oPaquete->values = null;
                }

                DB::commit(); // Confirmar la transacción si todo va bien
            }else{
                $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "a ocurrido un error  ";
                $oPaquete->message = "el usuario no esta relacionado con la empresa";
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

    public function optenerClientes(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'tnEmpresa'=> 'required',
        ]);

        $oClientes = Cliente::where('Empresa',$request->tnEmpresa)->get();

        //respuesta de confirmacion
        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "se actualizo al Proveedor";
        $oPaquete->values = $oClientes;
        return response()->json($oPaquete);

    }

    public function valueTClienteToCliente(Request $request){
        return [
            'Empresa'=> $request->tnEmpresa,
            'Nombre'=> $request->tcNombre,
            'Apellidos'=> $request->tcApellidos,
            'TipoDocumento'=> $request->tnTipoDocumento,
            'Documento'=> $request->tcDocumento,
            'Direccion'=> $request->tcDireccion,
            'Email'=> $request->tcEmail,
            'Telefono'=> $request->tcTelefono,
            'Usr' =>Auth::user()->Usuairo,
            'UsrFecha' => Carbon::now()->toDateString(),
            'UsrHora' =>  Carbon::now()->toTimeString()
        ];
    }

}
