<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;

use App\Models\EmpresaCategoriaProducto;

class EmpresaCategoriaProductoController extends Controller
{



    //
/**
     * Muestra todos los registros de EMPRESACATEGORIAPRODUCTO.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $empresasCategoriasProductos = EmpresaCategoriaProducto::all();
        return response()->json($empresasCategoriasProductos);
    }

    /**
     * Almacena un nuevo registro de EMPRESACATEGORIAPRODUCTO.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            $messages = [
                'Valor.unique' => 'El valor ya ha sido tomado.',
                'Empresa.required' => 'Se requiere ingresar el dato Empresa.',
                'Nombre.required' => 'Se requiere ingresar el datos Nombre.',
                'Valor.required' => 'Se requiere ingresar el datos Valor.',
                // Agregar mensajes personalizados para otras reglas si es necesario...
            ];

            $data = $request->validate([
                'Empresa' => 'required',
                'Nombre' => 'required',
                'Valor' => 'required|string|unique:EMPRESACATEGORIAPRODUCTO,Valor',
            ], $messages);

            if(substr($data['Valor'], -1)!== '-'){
                $data['Valor'] = $data['Valor'].'-';
            }

            $empresaCategoriaProducto = EmpresaCategoriaProducto::create($data);
            $empresaCategoriaProducto = EmpresaCategoriaProducto::where('Empresa',$data['Empresa'])->get();


            $oPaquete->error = 0; // Indicar que hubo un error
            $oPaquete->status = 1; // Indicar que hubo un error
            $oPaquete->messageSistema = "comando ejecutado";
            $oPaquete->message = "ejecucion sin inconvenientes";
            $oPaquete->messageMostrar = "0";
            $oPaquete->values = $empresaCategoriaProducto ;

            return response()->json($oPaquete);
        }catch (\Exception $e) {
            // Aquí puedes manejar el error y devolver una respuesta adecuada
            $oPaquete->error = 1; // Indicar que hubo un error
            $oPaquete->status = 0; // Indicar que hubo un error
            $oPaquete->messageSistema = "Error en el proceso";
            $oPaquete->message = $e->getMessage(); // Agregar detalles del error
            return response()->json($oPaquete, 500); // Devolver una respuesta con código 500
        }
    }

    /**
     * Muestra un registro específico de EMPRESACATEGORIAPRODUCTO.
     *
     * @param  int  $EmpresaCategoriaProducto
     * @return \Illuminate\Http\Response
     */
    public function show($EmpresaCategoriaProducto)
    {
        $empresaCategoriaProducto = EmpresaCategoriaProducto::find($EmpresaCategoriaProducto);

        if (!$empresaCategoriaProducto) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($empresaCategoriaProducto);
    }

    /**
     * Actualiza un registro específico de EMPRESACATEGORIAPRODUCTO.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $EmpresaCategoriaProducto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $EmpresaCategoriaProducto)
    {
        $data = $request->validate([
            'Empresa' => 'required',
            'Nombre' => 'required',
            'Valor' => 'required',
        ]);

        $empresaCategoriaProducto = EmpresaCategoriaProducto::find($EmpresaCategoriaProducto);

        if (!$empresaCategoriaProducto) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $empresaCategoriaProducto->update($data);

        return response()->json($empresaCategoriaProducto);
    }

    /**
     * Elimina un registro específico de EMPRESACATEGORIAPRODUCTO.
     *
     * @param  int  $EmpresaCategoriaProducto
     * @return \Illuminate\Http\Response
     */
    public function destroy($EmpresaCategoriaProducto)
    {
        $empresaCategoriaProducto = EmpresaCategoriaProducto::find($EmpresaCategoriaProducto);

        if (!$empresaCategoriaProducto) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $empresaCategoriaProducto->delete();

        return response()->json(['message' => 'Registro eliminado']);
    }

}
