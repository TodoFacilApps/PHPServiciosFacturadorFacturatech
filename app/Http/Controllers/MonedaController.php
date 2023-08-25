<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;

class MonedaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $oMoneda = Moneda::all();
        return $oMoneda;
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'Nombre'=> 'required',
        ]);

        $oMoneda = Moneda::where('Nombre', $request->Nombre)->get();
        // validad si existe
        if (!$oMoneda->isEmpty()) {
            return 'llega';

            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Codigo Duplicado ";
            $oPaquete->message = "a ocurrido un error  ";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{

            $oMoneda = Moneda::create($request->all());

            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Moneda creada";
            $oPaquete->message = "se creo la Moneda";
            $oPaquete->values = $oMoneda;
            return response()->json($oPaquete);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Moneda $moneda)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Moneda $moneda)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Moneda $moneda)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Moneda $moneda)
    {
        //
    }
}
