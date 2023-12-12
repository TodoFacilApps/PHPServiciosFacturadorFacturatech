<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\Auth;
use App\Models\PuntoVenta;


class PuntoVentaController extends Controller
{

    /**
     * Mostrar una lista del recurso.
     * @metodo index()
     * @autor   jakeline
     * @fecha   14-08-2023
     * @parametro
     * @return Object $oPaquete
     */
    public function index()
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;

        $oPuntoVenta = PuntoVenta::all();


        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = $oPuntoVenta ;
        return response()->json($oPaquete);
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     * @metodo store()
     * @autor   jakeline
     * @fecha   14-08-2023
     * @parametro Request
     * @return Object $oPaquete
     */
    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;


        $request->validate([
            'tcPuntoVenta'=> 'required',
        ]);

        $dato = explode("ß", $request->tcPuntoVenta);
        $oProducto = PuntoVenta::where('Codigo', $dato[0])->get();

        // validad si existe
        if (!$oProducto->isEmpty()) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Codigo Duplicado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{

            $oPuntoVenta = PuntoVenta::where('Nombre', $dato[1])->get();
            // validad si existe
            if (!$oProducto->isEmpty()) {

                $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "Error Codigo Nombre Dublicado";
                    $oPaquete->message = "a ocurrido un error  ";
                    $oPaquete->values = null;
                    return response()->json($oPaquete);
            }else{
            //return $oEmpresaSeleccionada;
            //return $dato;

                $oPuntoVenta = PuntoVenta::create([
                    'Codigo'=> $dato[0],
                    'Nombre'=> $dato[1],
                    'Sucursal'=> $dato[2]
                ]);

                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Punto de Venta creada";
                $oPaquete->message = "se creo el Punto de";
                $oPaquete->values = 1;
                return response()->json($oPaquete);
            }
        }
    }

    /**
     * Muestra el recurso especificado.
     * @metodo show()
     * @autor   jakeline
     * @fecha   14-08-2023
     * @parametro int InProducto
     * @return Object $oPaquete
     */
    public function show($InProducto)
    {

        $oProducto = Producto::find($InProducto);
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        if (!$oProducto) {
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Producto no encontrado";
            $oPaquete->message = "a ocurrido un error";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{
            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "sin errores";
            $oPaquete->message = "Producto encontrado";
            $oPaquete->values = $oProducto;
            return response()->json($oPaquete);
        }
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     * @metodo update()
     * @autor   jakeline
     * @fecha   14-08-2023
     * @parametro Request $request,int InProducto
     * @return Object $oPaquete
     */
    public function update(Request $request,$InProducto)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $request->validate([
            'Codigo'=> 'required',
            'Nombre'=> 'required',
            'ControlStock'=> 'required',
            'Stock'=> 'required',
            'Estado' => 'nullable',
        ]);

        $oProducto = Producto::find($InProducto)->get();
        // validad si existe
        if ($oProducto->isEmpty()) {
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Producto No Encontrado";
            $oPaquete->message = "a ocurrido un error  ";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{
            //actualizacion de datoss del producto
            $oProducto->Codigo = $request->Codigo;
            $oProducto->Nombre = $request->Nombre;
            $oProducto->ControlStock = $request->ControlStock;
            $oProducto->Stock = $request->Stock;
            $oProducto->Estado = $request->Estado;
            $oProducto->save();
            //respuesta de confirmacion
            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Unidad de Medida creada";
            $oPaquete->message = "se creo la Unidad de Medida";
            $oPaquete->values = $oProducto;
            return response()->json($oPaquete);
        }
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     * @metodo destroy()
     * @autor   jakeline
     * @fecha   14-08-2023
     * @parametro int InProducto
     * @return Object $oPaquete
     */
    public function destroy($InProducto)
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oProducto = Producto::find($InProducto);
        // validad si existe
        if (!$oProducto) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Producto no Encontrado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{
            // validad si existe
            if ($oProducto->Estado != 1) {

                $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "Error Producto Inabilitado";
                    $oPaquete->message = "a ocurrido un error  ";
                    $oPaquete->values = null;
                    return response()->json($oPaquete);
            }else{

                $oProducto->Estado = 2;
                $oProducto->save();

                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Unidad de Medida creada";
                $oPaquete->message = "se creo la Unidad de Medida";
                $oPaquete->values = $oProducto;
                return response()->json($oPaquete);
            }
        }

    }

}
