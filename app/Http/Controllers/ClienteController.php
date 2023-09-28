<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\TipoCliente;
use App\Models\Cliente;
use App\Models\EmpresaUsuarioPersonal;
use App\Models\TokenServicio;
use App\Models\Asociacion;
use App\Models\Empresa;
use GuzzleHttp\Client;
use App\Models\Producto;
use App\Models\Descuento;
use App\Models\UnidadMedida;
use App\Models\ClaseSiat;
use App\Models\ActividadEconomica;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\ConsultaController;
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

        $sincSiatController = new SincronizacionSiatController();
        $oTipoDocumentoIdentidad = $sincSiatController->SincronizacionSiatReturn( $oEmpresas[0]->Empresa,10);

        $oTipoDocumentoIdentidad = $oTipoDocumentoIdentidad->original->RespuestaListaParametricas->listaCodigos;
        $oTipoDocumentoIdentidad = array_map(function ($oTipoDocumentoIdentidad) {
        return [
                    'Tipo' => $oTipoDocumentoIdentidad->codigoClasificador,
                    'Nombre' => $oTipoDocumentoIdentidad->descripcion,
                ];
            },
            $oTipoDocumentoIdentidad);

        $oDescuento = Descuento::where('Empresa',$oEmpresas[0]->Empresa)
        ->where('Estado',1)->get();
        $oUser = auth()->user();
        $oTipoCliente = DB::table('TIPOCLIENTE as tc')
        ->join('EMPRESA as e', 'e.Empresa', '=', 'tc.Empresa')
        ->join('EMPRESAUSUARIOPERSONAL as eup', 'eup.Empresa', '=', 'e.Empresa')
        ->join('USUARIO as u', 'u.Usuario', '=', 'eup.Usuario')
        ->where('u.Usuario', $oUser->Usuario)
        ->select('tc.*')
        ->get();


        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [
            $oEmpresas,
            $oClientes,
            $oTipoDocumentoIdentidad,
            $oTipoCliente,
            $oUser->Usuario];
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
                'tnTipoCliente'=> 'required',
                'tcRazonSocial'=> 'required',
                'tnTipoDocumento'=> 'required',
                'tcDocumento'=> 'required',
                'tcComplemento'=> 'nullable',
                'tcNitEspecial'=> 'nullable',
                'tcCodigoCliente'=> 'nullable',
                'tcEmail'=> 'nullable',
                'tcTelefono'=> 'nullable',
            ]);

            $oUser = Auth::user();
            $empresasController = new UsuarioEmpresaController();
            if($empresasController->esMiEmpresa($request->tnEmpresa)==1){
                $oCliente = Cliente::where('TipoDocumento',$request->tnTipoDocumento)
                ->where('Empresa',$request->tnEmpresa)
                ->where('Documento',$request->tcDocumento)->exists();

                if($oCliente){
                    $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "a ocurrido un error";
                    $oPaquete->message = "el documento del cliente ya a sido registrado anteriormente";
                    $oPaquete->values = null;
                }else{
                    $oTipoCliente=Tipocliente::where('Empresa',$request->tnEmpresa)
                            ->where('TipoCliente',$request->tnTipoCliente)
                            ->count();
                    if($oTipoCliente>0){
                        $oInput = $this->valueTClienteToCliente($request);

                        $oCliente = Cliente::create($oInput);
                        if($oCliente->Complemento){
                            $oCliente->CodigoCliente = $oCliente->Documento.$oCliente->Complemento;
                        }
                        $oCliente->save();

                        $oPaquete->error = 0; // Error Generico
                        $oPaquete->status = 1; // Sucedio un error
                        $oPaquete->messageSistema = "comando ejecutado";
                        $oPaquete->message = "cliente registrado";
                        $oPaquete->values = $oCliente;

                    }else{
                        $oPaquete->error = 1; // Error Generico
                        $oPaquete->status = 0; // Sucedio un error
                        $oPaquete->messageSistema = "comando ejecutado";
                        $oPaquete->message = "Tipo de Cliente no habilitado para la Empresa";
                        $oPaquete->values = null;
                    }
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

        $oTipoCliente = TipoCliente::find($oCliente->TipoCliente);
        $oCliente->TipoCliente =$oTipoCliente->Descripcion;
        $oClienteEmpresa =$oCliente->empresa->Nombre;
        $oCliente->Empresa =$oClienteEmpresa;

        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = $oCliente ;
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
            'tcRazonSocial'=> 'required',
            'tnTipoDocumento'=> 'required',
            'tcDocumento'=> 'required',
            'tcComplemento'=> 'required',
            'tcNitEspecial'=> 'nullable',
            'tcCodigoCliente'=> 'nullable',
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

    public function clientesValidarNit(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'tnEmpresa'=> 'required',
            'tnNit'=> 'required',
        ]);

        $SincronizacionSiatController = new SincronizacionSiatController();
        $result = $SincronizacionSiatController->ValidacionNitReturn($request->tnEmpresa,$request->tnNit);

        //respuesta de confirmacion
        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->message = "comando ejecutado";
        $oPaquete->values = $result->original;
        return response()->json($oPaquete);

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
            'TipoCliente'=> $request->tnTipoCliente,
            'CodigoCliente'=> $request->tcDocumento,
            'RazonSocial'=> $request->tcRazonSocial,
            'TipoDocumento'=> $request->tnTipoDocumento,
            'Documento'=> $request->tcDocumento,
            'Complemento'=> $request->tcComplemento,
            'Email'=> $request->tcEmail,
            'Telefono'=> $request->tcTelefono,
            'Usr' =>Auth::user()->Usuario,
            'UsrFecha' => Carbon::now()->toDateString(),
            'UsrHora' =>  Carbon::now()->toTimeString()
        ];
    }

    public function editar(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'tnCliente'=> 'required',
        ]);
        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();

        $oCliente ;
        if($oEmpresas){
            $oCliente = Cliente::find($request->tnCliente);
        }

        $sincSiatController = new SincronizacionSiatController();
        $oTipoDocumentoIdentidad = $sincSiatController->SincronizacionSiatReturn( $oEmpresas[0]->Empresa,10);

        $oTipoDocumentoIdentidad = $oTipoDocumentoIdentidad->original->RespuestaListaParametricas->listaCodigos;
        $oTipoDocumentoIdentidad = array_map(function ($oTipoDocumentoIdentidad) {
        return [
                    'Tipo' => $oTipoDocumentoIdentidad->codigoClasificador,
                    'Nombre' => $oTipoDocumentoIdentidad->descripcion,
                ];
            },
            $oTipoDocumentoIdentidad);

        $oUser = auth()->user();
        $oTipoCliente = DB::table('TIPOCLIENTE as tc')
        ->join('EMPRESA as e', 'e.Empresa', '=', 'tc.Empresa')
        ->join('EMPRESAUSUARIOPERSONAL as eup', 'eup.Empresa', '=', 'e.Empresa')
        ->join('USUARIO as u', 'u.Usuario', '=', 'eup.Usuario')
        ->where('u.Usuario', $oUser->Usuario)
        ->select('tc.*')
        ->get();


        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [
            $oEmpresas,
            $oCliente,
            $oTipoDocumentoIdentidad,
            $oTipoCliente,
            $oUser->Usuario];
        return response()->json($oPaquete);
    }
}
