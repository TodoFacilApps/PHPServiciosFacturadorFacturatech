<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Descuento;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\DB;




class DescuentoController extends Controller
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
        
        $oUser = auth()->user();
        $lnEmpresaSeleccionada = $oUser->EmpresaSeleccionada;
        $sql="Select d.*
        FROM DESCUENTO as d,PARAMETROS as p
        WHERE (p.Descuento= d.Descuento) or (d.Empresa = ".$lnEmpresaSeleccionada.")";
        $oDescuentos= DB::select($sql);
        
        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();
        $lnEmpresaSeleccionada = $oUser->EmpresaSeleccionada;
        if(($lnEmpresaSeleccionada===0)||($lnEmpresaSeleccionada==='0')){
            $lnEmpresaSeleccionada = $oEmpresas[0]->Empresa;
        }
        
        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [
            $oDescuentos,
            $oEmpresas,
            $lnEmpresaSeleccionada
        ] ;
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
            'Nombre' => 'required',
            'Tipo' => 'nullable',
            'Valor' => 'nullable',
        ]);
        
        $oDescuento= Descuento::where('Nombre', $request->Nombre)
        ->where('Empresa',$request->Empresa)->get();
        // validad si existe
        if (!$oDescuento->isEmpty()) {
            
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Descuento Duplicada ";
            $oPaquete->message = "a ocurrido un error  ";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{
            $oDescuento = Descuento::create([
                'Empresa' => $request->Empresa,
                'Nombre' => $request->Nombre,
                'Tipo' => $request->Tipo,
                'Valor' => $request->Valor,
                'Estado' => 1,
            ]);
            
            $oUser = auth()->user();
            $sql="Select d.*
FROM DESCUENTO as d,PARAMETROS as p
WHERE (p.Descuento= d.Descuento) or (d.Empresa = ".$request->Empresa.")";
            $oDescuento= DB::select($sql);
            
            
            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Descuento Creado";
            $oPaquete->message = "se creo el Descuento";
            $oPaquete->values = [$oDescuento];
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
