<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\Ingreso;
use App\Models\Proveedor;
use App\Models\ProductoIngreso;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\Auth;

class IngresoController extends Controller
{
    //
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

        $oEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;

        $oIngreso = Ingreso::where('Empresa', $oEmpresaSeleccionada)->get();

        for ($tnIterador = 0; $tnIterador < count($oIngreso); $tnIterador++) {
            $oIngreso[$tnIterador]['Proveedor'] = $oIngreso[$tnIterador]->proveedor->Nombre;
            $oIngreso[$tnIterador]['Usuario'] = $oIngreso[$tnIterador]->usuario->Nombre;
        }


        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = $oIngreso ;
        return response()->json($oPaquete);
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
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
            'Proveedor'=> 'required',
            'Productos'=> 'required',
        ]);
        $oEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;

        $oProveedor = Proveedor::where('Documenton',$request->Proveedor)
                            ->where('Empresa', $oEmpresaSeleccionada)->get();
        // validad si existe

        if ($oProveedor->isEmpty()) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Proveedor no encontrado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{


            $oIngreso = Ingreso::create([
                'Fecha' => now(),
                'Proveedor' => $oProveedor[0]->Proveedor,
                'Usuario' => auth()->id(),
                'Costo' => 0,
                'Estado' => 3,
                'Empresa' => $oEmpresaSeleccionada,
            ]);

            $lnTotal = 0;
            // Proceso de Insersion de Productos
            $valores = explode("þ", $request->Productos);

            for ($valor = 0; $valor < ( count($valores) -1); $valor++) {
                $dato = explode("ß", $valores[$valor]);
                $oProducto = Producto::where('Codigo',$dato[0])->get();
                $productoIngreso = ProductoIngreso::create([
                    'Ingreso' => $oIngreso->Ingreso,
                    'Producto' => $oProducto[0]->Producto,
                    'UnidadMedida' => $dato[2],
                    'Cantidad' => $dato[5],
                    'Precio' => $dato[7],
                    'Total' => $dato[8],
                    'Estado' => 1,
                ]);

                if($oProducto[0]->ControlStock){
                    $oProducto[0]->Stock += $dato[6] * $dato[5] ;
                    $oProducto[0]->save();

                }

                $lnTotal += $dato[7];
            }
            $oIngreso->Costo = $lnTotal;
            $oIngreso->Estado = 1;
            $oIngreso->save();


            $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Ingreso Registrado Exitosamente";
                $oPaquete->message = "";
                $oPaquete->values = 1;
                return response()->json($oPaquete);



        }
    }

    /**
     * Muestra el recurso especificado.
     * @metodo show()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro int InProducto
     * @return Object $oPaquete
     */
    public function show($InProducto)
    {

        $oIngreso = Producto::find($InProducto);
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        if (!$oIngreso) {
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
            $oPaquete->values = $oIngreso;
            return response()->json($oPaquete);
        }
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     * @metodo update()
     * @autor   jakeline
     * @fecha   05-08-2023
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

        $oIngreso = Producto::find($InProducto)->get();
        // validad si existe
        if ($oIngreso->isEmpty()) {
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Producto No Encontrado";
            $oPaquete->message = "a ocurrido un error  ";
            $oPaquete->values = null;
            return response()->json($oPaquete);
        }else{
            //actualizacion de datoss del producto
            $oIngreso->Codigo = $request->Codigo;
            $oIngreso->Nombre = $request->Nombre;
            $oIngreso->ControlStock = $request->ControlStock;
            $oIngreso->Stock = $request->Stock;
            $oIngreso->Estado = $request->Estado;
            $oIngreso->save();
            //respuesta de confirmacion
            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "Unidad de Medida creada";
            $oPaquete->message = "se creo la Unidad de Medida";
            $oPaquete->values = $oIngreso;
            return response()->json($oPaquete);
        }
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     * @metodo destroy()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro int InProducto
     * @return Object $oPaquete
     */
    public function destroy($InProducto)
    {
        //
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oIngreso = Producto::find($InProducto);
        // validad si existe
        if (!$oIngreso) {

            $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "Error Producto no Encontrado ";
                $oPaquete->message = "a ocurrido un error  ";
                $oPaquete->values = null;
                return response()->json($oPaquete);
        }else{
            // validad si existe
            if ($oIngreso->Estado != 1) {

                $oPaquete->error = 1; // Error Generico
                    $oPaquete->status = 0; // Sucedio un error
                    $oPaquete->messageSistema = "Error Producto Inabilitado";
                    $oPaquete->message = "a ocurrido un error  ";
                    $oPaquete->values = null;
                    return response()->json($oPaquete);
            }else{

                $oIngreso->Estado = 2;
                $oIngreso->save();

                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "Unidad de Medida creada";
                $oPaquete->message = "se creo la Unidad de Medida";
                $oPaquete->values = $oIngreso;
                return response()->json($oPaquete);
            }
        }

    }


    /**
     * Elimina el recurso especificado del almacenamiento.
     * @metodo destroy()
     * @autor   jakeline
     * @fecha   05-08-2023
     * @parametro int InProducto
     * @return Object $oPaquete
     */
    public function valoresPrevios()
    {
        //
        $oEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;

        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oProductos = Producto::where('Empresa',$oEmpresaSeleccionada)->get();
        $oProveedor = Proveedor::where('Empresa',$oEmpresaSeleccionada)->get();
        $oUnidadMedida = UnidadMedida::all();
        // validad si existe

        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "Unidad de Medida creada";
        $oPaquete->message = "se creo la Unidad de Medida";
        $oPaquete->values = [$oProductos,$oProveedor,$oUnidadMedida];
        return response()->json($oPaquete);

    }

}
