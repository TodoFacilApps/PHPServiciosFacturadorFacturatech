<?php

namespace App\Http\Controllers;

use App\Models\ImpuestoIva;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;

class ImpuestoIvaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $oImpuestosIva = ImpuestoIva::all();
        return $oImpuestosIva;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'Porcentaje'=> 'required',
            'Valor'=> 'required',
        ]);

        $oImpuestosIva = ImpuestoIva::where('Porcentaje', $request->Porcentaje)->get();
        // validad si existe
        if (!$oImpuestosIva->isEmpty()) {
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Impuesto Duplicado ";
            $oPaquete->message = "a ocurrido un error  ";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{

            $oImpuestosIva = ImpuestoIva::create($request->all());

            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Impuesto creado";
            $oPaquete->message = "se creo el Impuesto";
            $oPaquete->values = $oImpuestosIva;
            return response()->json($oPaquete);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ImpuestoIva $impuestoIva)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ImpuestoIva $impuestoIva)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImpuestoIva $impuestoIva)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImpuestoIva $impuestoIva)
    {
        //
    }
}
