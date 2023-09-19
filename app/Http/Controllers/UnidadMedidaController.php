<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\SincronizacionSiatController;


class UnidadMedidaController extends Controller
{
    /**
     * Metodo que devuelve todas las unidades de medida
     * @metodo index()
     * @autor   jakeline
     * @fecha   04-08-2023
     * @parametro
     * @return Object $oUnidadMedida
     */
    public function index()
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oUnidadMedida = UnidadMedida::all();
        $oUser = auth()->user();
        $oUnidadMedida = DB::table('UNIDADMEDIDA as um')
        ->join('EMPRESA as e', 'e.Empresa', '=', 'um.Empresa')
        ->join('EMPRESAUSUARIOPERSONAL as eup', 'eup.Empresa', '=', 'e.Empresa')
        ->join('USUARIO as u', 'u.Usuario', '=', 'eup.Usuario')
        ->where('u.Usuario', $oUser->Usuario)
        ->select('um.*')
        ->get();

        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();
        $sincSiatController = new SincronizacionSiatController();
        $oUnidad = $sincSiatController->SincronizacionSiatReturn( $oEmpresas[0]->Empresa,18);
        if($oUnidad){
            $oUnidad =$oUnidad->original->RespuestaListaParametricas->listaCodigos;
        }

        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [$oUnidadMedida,$oEmpresas,$oUnidad,$oUser->EmpresaSeleccionada] ;
        return response()->json($oPaquete);
    }

    /**
     * Muestra el formulario para crear un nuevo recurso.
     */
    public function create()
    {
        //
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     * @metodo store()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro Request
     * @return Object $oUnidadMedida
     */
    public function store(Request $request)
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $request->validate([
            'Empresa' => 'required',
            'CodigoUnidadMedida' => 'nullable',
            'Descripcion' => 'nullable',
            'Abreviatura' => 'nullable',
        ]);

        $oUnidadMedida = UnidadMedida::where('Descripcion', $request->Descripcion)->get();
        // validad si existe
        if (!$oUnidadMedida->isEmpty()) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Unidad de Medida Duplicada ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{
            $oUnidadMedida = UnidadMedida::create([
                'Empresa' => $request->Empresa,
                'Codigo' => $request->Codigo,
                'Descripcion' => $request->Descripcion,
                'Abreviatura' => $request->Abreviatura,
            ]);

            $oUser = auth()->user();
            $oUnidadMedida = DB::table('UNIDADMEDIDA as um')
            ->join('EMPRESA as e', 'e.Empresa', '=', 'um.Empresa')
            ->join('EMPRESAUSUARIOPERSONAL as eup', 'eup.Empresa', '=', 'e.Empresa')
            ->join('USUARIO as u', 'u.Usuario', '=', 'eup.Usuario')
            ->where('u.Usuario', $oUser->Usuario)
            ->select('um.*')
            ->get();


            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Unidad de Medida creada";
            $oPaquete->message = "se creo la Unidad de Medida";
            $oPaquete->values = [$oUnidadMedida];
            return response()->json($oPaquete);
        }
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(UnidadMedida $unidadMedida)
    {
        //
    }

    /**
     * Muestra el formulario para editar el recurso especificado.
     */
    public function edit(Request $request, $InUnidadMedida)
    {
        //

    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     */
    public function update(Request $request, UnidadMedida $unidadMedida)
    {
        //
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy($InUnidadMedida)
    {
        //

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        return $oPaquete;

        $oUnidadMedida = UnidadMedida::find($InUnidadMedida);
        // validad si existe
        if ($oUnidadMedida->isEmpty()) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Unidad de Medida Ineccistente ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{
            if($oUnidadMedida->Estado != 1) {
                $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Unidad de Medida No Habilitada";
                $oPaquete->message = "a ocurrido un error";
                $oPaquete->values = null;
                return response()->json($oPaquete);
            }

            $oUnidadMedida->Estedo = 2;
            $oUnidadMedida->save();

            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Unidad de Medida creada";
            $oPaquete->message = "se creo la Unidad de Medida";
            $oPaquete->values = $oUnidadMedida;
            return response()->json($oPaquete);

        }


    }
}
