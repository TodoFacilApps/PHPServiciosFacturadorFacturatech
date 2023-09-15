<?php

namespace App\Http\Controllers;

use App\Models\EmpresaSucursal;
use App\Models\PuntoVenta;
use App\Models\EmpresaUsuarioPersonal;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;


class SucursalController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tnEmpresa'=> 'required',
            'tcDireccion'=> 'required',
            'tcLocalidad'=> 'required',
            'tcTelefono'=> 'required',
        ]);
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            DB::beginTransaction(); // Iniciar la transacción
            $oSucursal = DB::table('EMPRESASUCURSAL')
                ->where('Empresa', $request->tnEmpresa)
                ->select(DB::raw('MAX("CodigoSucursal") as "CodigoSucursal"'))
                ->first();

            $olnSucursal = DB::table('EMPRESASUCURSAL')
                ->select(DB::raw('MAX("Sucursal") as "Sucursal"'))
                ->first();

            $lnSucursal = $olnSucursal ? $olnSucursal->Sucursal + 1 : 1;

            $nextCodigoSucursal = $oSucursal ? $oSucursal->CodigoSucursal + 1 : 0;

            $oSucursal = EmpresaSucursal::create([
                'Empresa' => $request->tnEmpresa,
                'Sucursal' => $lnSucursal,
                'CodigoSucursal' => $nextCodigoSucursal,
                'Direccion' => $request->tcDireccion,
                'Localidad' => $request->tcLocalidad,
                'Telefono' => $request->tcTelefono,
                'Estado' => 1,
            ]);


            DB::commit(); // Confirmar la transacción si todo va bien
            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Comanndo ejecutado";
            $oPaquete->message = "ejecusion sin inconvenientes";
            $oPaquete->messageMostrar = "Catalogo agregado sin productos";
            $oPaquete->values = $oSucursal;
            return response()->json($oPaquete);

        }catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción en caso de error

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($e, 500); // Devolver una respuesta con código 500
        }

    }
    /**
     * Display the specified resource.
     */
    public function show(Sucursal $sucursal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sucursal $sucursal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sucursal $sucursal)
    {
        //

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sucursal $sucursal)
    {
        //
    }

    public function empresaSucursal(Request $request)
    {
        //
        $request->validate([
            'tnEmpresa' => 'required',
        ]);
        //return $request;
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $InUser = auth()->id();
            $usuarioEmpresa = EmpresaUsuarioPersonal::where('Empresa',$request->tnEmpresa)
            ->where('Usuario',$InUser)
            ->get();

            // validad si existe
            if ($usuarioEmpresa->isEmpty()) {
                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "error en el Comanndo ";
                $oPaquete->message = "usuario no relacionado con la empresa";
                $oPaquete->messageMostrar = "";
                $oPaquete->values = null;
                }else{
                    $oEmpresaSucursal = EmpresaSucursal::where('Empresa', $request->tnEmpresa)->get();

                    $oPaquete->error = 0; // Error Generico
                    $oPaquete->status = 1; // Sucedio un error
                    $oPaquete->messageSistema = "Comanndo ejecutado";
                    $oPaquete->message = "ejecusion sin inconvenientes";
                    $oPaquete->messageMostrar = "";
                    $oPaquete->values = $oEmpresaSucursal;
                    }
            return response()->json($oPaquete);

        }catch (\Exception $e) {

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($e, 500); // Devolver una respuesta con código 500
        }

    }

    public function sucursalPuntoVenta(Request $request)
    {
        //
        $request->validate([
            'tnSucursal' => 'required',
        ]);
        //        return $request;
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{

            $oSucursal = EmpresaSucursal::where('Sucursal',$request->tnSucursal)->get()[0];

            $InUser = auth()->id();

            $usuarioEmpresa = EmpresaUsuarioPersonal::where('Empresa',$oSucursal->Empresa)
            ->where('Usuario',$InUser)
            ->get();

            // validad si existe
            if ($usuarioEmpresa->isEmpty()) {
                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "error en el Comanndo ";
                $oPaquete->message = "usuario no relacionado con la empresaSucursal";
                $oPaquete->messageMostrar = "";
                $oPaquete->values = null;
                }else{
                    $oPuntosVenta = PuntoVenta::where('Estado',2)
                    ->where('Sucursal', $request->tnSucursal)->get();
                    $oPaquete->error = 0; // Error Generico
                    $oPaquete->status = 1; // Sucedio un error
                    $oPaquete->messageSistema = "Comanndo ejecutado";
                    $oPaquete->message = "ejecusion sin inconvenientes";
                    $oPaquete->messageMostrar = "";
                    $oPaquete->values = $oPuntosVenta;
                    }
            return response()->json($oPaquete);

        }catch (\Exception $e) {

            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($e, 500); // Devolver una respuesta con código 500
        }

    }

}
