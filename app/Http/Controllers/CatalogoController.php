<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\EmpresaSucursal;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\EMPRESAUSUARIOPERSONAL;
use App\Models\Catalogo;
use App\Models\CatalogoProducto;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\Moneda;
use App\Models\ImpuestoIva;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
//$oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\TokenServicio;
use App\Http\Controllers\UsuarioEmpresaController;


class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $oCatalogo = Catalogo::where('Estado',1)->get();

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = $oCatalogo ;
        return response()->json($oPaquete);

    }


    /**
     * Almacene un recurso recién creado en el almacenamiento.
     * @metodo store()
     * @autor   jakeline
     * @fecha   09-08-2023
     * @parametro Request
     * @return Object $oPaquete
     */
    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $request->validate([
            'Nombre'=> 'required',
            'Productos'=> 'nullable',
            'Empresa'=> 'nullable',
        ]);
        $oEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;

        $oCatalogo = Catalogo::where('Nombre', $request->Nombre)
                            ->where('Empresa', $oEmpresaSeleccionada)->get();
        // validad si existe

        if (!$oCatalogo->isEmpty()) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Nombre Duplicado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{
            try{
                DB::beginTransaction(); // Iniciar la transacción

                $oCatalogo = Catalogo::create([
                    'Nombre' => $request->Nombre,
                    'Empresa' => $oEmpresaSeleccionada,
                ]);
                if ($request->Productos) {

                    $valores = explode("þ", $request->Productos);

                    for ($valor = 0; $valor < ( count($valores) -1); $valor++) {
                        $dato = explode("ß", $valores[$valor]);
                        $oProducto = Producto::where('Codigo',$dato[0])->get();

                        $oCatalogoproducto = CatalogoProducto::create([
                            'Catalogo' => $oCatalogo->Catalogo,
                            'Producto' => $oProducto[0]->Producto,
                            'UnidadMedida' => $dato[2],
                            'UnidadBulto' => $dato[5],
                            'Moneda' => $dato[6],
                            'PrecioVenta' => $dato[7],
                            'Iva' => $dato[8],
                            'PrecioIva' => $dato[9],
                            'ImpuestoInterno' => $dato[10],
                            'PrecioFinal' => $dato[11],
                            'Estado' => 1,
                        ]);
                    }

                    DB::commit(); // Confirmar la transacción si todo va bien
                    $oPaquete->error = 0; // Error Generico
                    $oPaquete->status = 1; // Sucedio un error
                    $oPaquete->messageSistema = "Comanndo ejecutado";
                    $oPaquete->message = "ejecusion sin inconvenientes";
                    $oPaquete->messageMostrar = "Catalogo agregado con sus productos";
                    $oPaquete->values = 1;
                    return response()->json($oPaquete);
                }

                DB::commit(); // Confirmar la transacción si todo va bien
                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Comanndo ejecutado";
                $oPaquete->message = "ejecusion sin inconvenientes";
                $oPaquete->messageMostrar = "Catalogo agregado sin productos";
                $oPaquete->values = 1;
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
    }

    /**
     * Display the specified resource.
     */
    public function show ($lnCatalogo)
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oCatalogo = Catalogo::find($lnCatalogo);
        // validad si existe
        if (!$oCatalogo) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error No encontrado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{

            $oCatalogoProducto = CatalogoProducto::where('Catalogo', $lnCatalogo)->get();
            /*   ///este ciclo me evita las comaraciones en el show del Forn End pero descuadra en el edit
            //reconendacion traslado de comparcion al fornEnd
            foreach ($oCatalogoProducto as $producto) {
                $producto->UnidadMedida = $producto->unidadMedida->Descripcion;
                $producto->Moneda = $producto->moneda->Nombre;

                $porcentaje = ImpuestoIva::where('Valor', $producto->Iva)->first();
                $producto->Iva = $porcentaje->Porcentaje;
            }
            */
            $oProducto = Producto::all();

            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Comanndo ejecutado";
            $oPaquete->message = "ejecusion sin inconvenientes";
            $oPaquete->messageMostrar = "Catalogo agregado";
            $oPaquete->values = [$oCatalogo, $oCatalogoProducto, $oProducto];
            return response()->json($oPaquete);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $lnCatalogo)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $request->validate([
            'Nombre'=> 'required',
            'Productos'=> 'nullable',
            'Empresa'=> 'nullable',
        ]);

        $oCatalogo = Catalogo::where('Nombre', $request->Nombre)->get();
        // validad si existe

        if (!$oCatalogo->isEmpty()) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Nombre Duplicado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{

            try{
                DB::beginTransaction(); // Iniciar la transacción

                $oCatalogo = Catalogo::create([
                    'Nombre' => $request->Nombre,
                ]);
                if ($request->Productos) {

                    $valores = explode("þ", $request->Productos);

                    for ($valor = 0; $valor < ( count($valores) -1); $valor++) {
                        $dato = explode("ß", $valores[$valor]);
                        $oProducto = Producto::where('Codigo',$dato[0])->get();

                        $oCatalogoproducto = CatalogoProducto::create([
                            'Catalogo' => $oCatalogo->Catalogo,
                            'Producto' => $oProducto[0]->Producto,
                            'UnidadMedida' => $dato[2],
                            'UnidadBulto' => $dato[5],
                            'Moneda' => $dato[6],
                            'PrecioVenta' => $dato[7],
                            'Iva' => $dato[8],
                            'PrecioIva' => $dato[9],
                            'ImpuestoInterno' => $dato[10],
                            'PrecioFinal' => $dato[11],
                            'Estado' => 1,
                        ]);
                    }

                    DB::commit(); // Confirmar la transacción si todo va bien
                    $oPaquete->error = 0; // Error Generico
                    $oPaquete->status = 1; // Sucedio un error
                    $oPaquete->messageSistema = "Comanndo ejecutado";
                    $oPaquete->message = "ejecusion sin inconvenientes";
                    $oPaquete->messageMostrar = "Catalogo agregado con sus productos";
                    $oPaquete->values = 1;
                    return response()->json($oPaquete);
                }

                DB::commit(); // Confirmar la transacción si todo va bien
                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Comanndo ejecutado";
                $oPaquete->message = "ejecusion sin inconvenientes";
                $oPaquete->messageMostrar = "Catalogo agregado sin productos";
                $oPaquete->values = 1;
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Catalogo $catalogo)
    {
        //
    }


        // Función para desencriptar datos
    public function decryptData($data)
    {
        return Crypt::decryptString($data);
    }

    public function catalogoData(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oEmpresaSeleccionada;
        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();

        $oInput = TokenServicio :: where('Empresa',$oEmpresas[0]->Empresa)->get()[0];

        $oEmpresa =  Empresa::select('EMPRESA.*')
        ->leftJoin('EMPRESAUSUARIOPERSONAL', 'EMPRESAUSUARIOPERSONAL.Empresa', '=', 'EMPRESA.Empresa')
        ->leftJoin('USUARIO', 'EMPRESAUSUARIOPERSONAL.Usuario', '=', 'USUARIO.Usuario')
        ->where('USUARIO.Usuario', '=', Auth::user()->Usuario)
        ->orderBy('EMPRESA.Empresa', 'asc')
        ->get();

        $oProducto = Producto::where('Empresa',$oEmpresas[0]->Empresa)->get();
        $oSucursal = EmpresaSucursal::where('Empresa',$oEmpresas[0]->Empresa)->get();

        $tnActividad = [1,18];

        //Parametros necesarios
        $oMoneda = Moneda::all();
        $oImpuestoIva = ImpuestoIva::all();
        $oUnidadMedida = UnidadMedida::all();

        // validad si existe
        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "ejecucion exitosa ";
        $oPaquete->message = "comando ejecutado";
        $oPaquete->values = [$oInput, $oProducto, $oUnidadMedida, $oMoneda, $oImpuestoIva, $oEmpresa, $oSucursal];
        return response()->json($oPaquete);
    }
}
