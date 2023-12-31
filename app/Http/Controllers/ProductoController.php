<?php

namespace App\Http\Controllers;

use App\Models\EmpresaUsuarioPersonal;
use App\Models\TokenServicio;
use App\Models\Asociacion;
use App\Models\Empresa;
use GuzzleHttp\Client;
use App\Models\Producto;
use App\Models\Movimineto;
use App\Models\UnidadMedida;
use App\Models\ClaseSiat;
use App\Models\ActividadEconomica;
use App\Models\EmpresaCategoriaProducto;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\ConsultaController;


class ProductoController extends Controller
{
    const _API = 'http://apirest.facturatech.com.bo/api/';

    public $message;

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
        $loPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $loEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;

        if(+$loEmpresaSeleccionada === 0 ){
            $loPaquete->error = 0; // Error Generico
            $loPaquete->status = 1; // Sucedio un error
            $loPaquete->messageSistema = "comando ejecutado";
            $loPaquete->message = "no a Seleccionado ni una empresa";
            $loPaquete->values =null;
        }else{
            
            //return Auth::user()->EmpresaSeleccionada;
            $loProducto = Producto::where('Empresa', $loEmpresaSeleccionada)->get();
            $loEmpresa = Empresa::where('Empresa',$loEmpresaSeleccionada)->get();
            
            
            $loPaquete->error = 0; // Error Generico
            $loPaquete->status = 1; // Sucedio un error
            $loPaquete->messageSistema = "comando ejecutado";
            $loPaquete->message = "ejecusion sin inconvenientes";
            $loPaquete->values = [$loEmpresaSeleccionada, $loEmpresa, $loProducto];
        }
        return response()->json($loPaquete);
    }

    /**
     * Almacene un recurso recién creado.
     * @metodo store()
     * @autor   jakeline
     * @fecha   24-08-2023
     * @parametro Request
     * @return Object $oPaquete
     */

    public function store(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        try{
            DB::beginTransaction(); // Iniciar la transacción

            if($request->tcDescripcion === null){
                $request->merge(['tcDescripcion' => 'SIN DESCRIPCION']);
            }
            if($request->tcCodigoProductoOrigen === null){
                $request->merge(['tcCodigoProductoOrigen' => '1']);
            }
            if($this->FiltrosRegistroProducto($request)){
                $lxImage;$lxImageData;$lcImagenNombre;

                $loEmpresa = Empresa::find($request->tnEmpresa);
                $llImagen;
                if($request->txUrlImagen){
                    //almacenamiento del nombre de la imagen y decodificando la imagen
                    $lxImageData = $request->input('txUrlImagen');
                    $lxImage = base64_decode($lxImageData);
                    $lcImagenNombre = '_' . time() . '.jpg'; // Genera un nombre único para la imagen
                    //actualizando los valores
                    $request->merge(['file' => $lxImage]);
                    $request->merge(['txUrlImagen' => $lcImagenNombre]);
                    $llImagen = true;
                }else{
                    $request->merge(['txUrlImagen' => 'imagenes/default/prodductoServicio.jpg']);
                    $llImagen = false;
                }

                //insercion del producto
                $oInput = [
                    'Producto' => null,
                    'TipoProducto' => $request->tnTipoProducto,
                    'ControlaStock' => $request->tnControlaStock,
                    'ClaseSiat' => $request->tnClaseSiat,
                    'Empresa' => $request->tnEmpresa,
                    'ActividadEconomica' => $request->tnActividadEconomica,
                    'Unidad' => $request->tnUnidad,
                    'UrlImagen' => $request->txUrlImagen,
                    'CodigoProductoOrigen' => $request->tcCodigoProductoOrigen,
                    'Nombre' => $request->tcNombre,
                    'Descripcion' => $request->tcDescripcion,
                    'Precio' => $request->tnPrecio,
                    'CatalogoImpuestos' => $request->tnCatalogoImpuestos,
                    'CodigoProductoEmpresa' => $request->tcCodigoProductoEmpresa,
                    'DecimalesCantidad' => $request->tnDecimalesCantidad,
                    'TipoProductoEmpresa' => $request->tnTipoProductoEmpresa,
                    'Saldo' => $request->tnSaldo,
                    'MaximoStock' => $request->tnMaximoStock,
                    'PrecioPorMayor' => $request->tnPrecioPorMayor,
                    'PrecioOferta' => $request->tnPrecioOferta,
                    'PrecioRemate' => $request->tnPrecioRemate,
                    'NumeroOpciones' => $request->tnNumeroOpciones,
                    'Novedad' => $request->tnNovedad,
                    'Oferta' => $request->tnOferta,
                    'Posicion' => $request->tcPosicion,
                    'NroVersion' => $request->tnNroVersion,
                    'Estado' => $request->tnEstado,
                    'Usr' => Auth::user()->Usuario,
                    'UsrHora' => date('H:i:s'),//hora actual
                    'UsrFecha' => date('Y-m-d'),//fecha actual
                ];

                //Generacion de una nueva  serie vasado en un codigo
                $producto = Producto::where('CodigoProductoOrigen', 'like', $oInput['CodigoProductoOrigen'] . '%')
                        ->where('CodigoProductoOrigen', 'NOT LIKE', $oInput['CodigoProductoOrigen'].'%-%')
                        ->latest('CodigoProductoOrigen')
                        ->first();

                if($producto){
                    $cadenaUltimoCodigo = explode("-", $producto->CodigoProductoOrigen);
                    $lSerie =end($cadenaUltimoCodigo);
                    $producto = intval($lSerie);
                }else{
                    $producto = 0;
                }
                $producto =sprintf("%04d", ($producto + 1));

                if (strpos($oInput['CodigoProductoOrigen'], '-') !== false) {
                    $oInput['CodigoProductoOrigen'] = $oInput['CodigoProductoOrigen'].$producto;
                } else {
                    $oInput['CodigoProductoOrigen'] = $producto;
                }
                //fin del generador de codigo

                $oProducto = Producto::create($oInput);
                if($llImagen){
                    $newImageName = str_replace(' ', '_', $oProducto->Nombre);
                    $oProducto->UrlImagen =  'imagenes/empresa/' . $loEmpresa->Empresa. '/productos/' . $oProducto->Producto . $newImageName . $lcImagenNombre;
                    Storage::disk('public')->put($oProducto->UrlImagen, $lxImage);
                }
                $oProducto->UrlImagen = env('APP_URL') . '/' . $oProducto->UrlImagen;
                $oProducto->save();

                $oPaquete->error = 0; // Error Generico
                $oPaquete->status = 1; // Sucedio un error
                $oPaquete->messageSistema = "codigo ejecutado";
                $oPaquete->message = "ejecucion exitosa";
                $oPaquete->values = 1;

            }else{
                $oPaquete->error = 1;
                $oPaquete->status = 0;
                $oPaquete->messageSistema = "Error en el proceso";
                $oPaquete->message = $this->message;
            }
            DB::commit(); // Confirmar la transacción si todo va bien
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
     * verifica que los datos sean correctos antes de registrar.
     * @metodo FiltrosRegistroProducto()
     * @autor   jakeline
     * @fecha   23-08-2023
     * @parametro Request
     * @return Object $oPaquete
     */
    public function FiltrosRegistroProducto(Request $request)
    {
        try{

            if($request->tnEstado == 2){
                $this->message = 'no se puede registrar un producto con el estado Deshabilitado';
                return false;
            }
            //verifica la relacion del cliente con la empresa
            if($request->tnEmpresa == NULL){
                $this->message = 'No se puede registrar producto sin seleccionar su empresa';
                return false;
            }else{
                $oUser = Auth::user();
                $oEmpresas = EmpresaUsuarioPersonal::where('Usuario', $oUser->Usuario)
                ->where('Empresa', $request->tnEmpresa)
                ->first();
                if(!$oEmpresas){
                    $this->message = 'la empresa no esta relacionada con el usuario';
                    return false;
                }
            }

            if($request->tnActividadEconomica == null){
                $this->message = 'se requiere de una actividad economica valida para la empresa';
                return false;
            }

            if($request->tcNombre == null){
                $this->message = 'Nombre de producto es requerido';
                return false;
            }

              $request->validate([
                'tnTipoProducto' => 'required',
                'tnControlaStock' => 'required',
                'tnClaseSiat' => 'required',
                'tnActividadEconomica' => 'required',
                'tnUnidad' => 'required',
                'txUrlImagen' => 'nullable',
                'tcCodigoProductoOrigen' => 'required',
                'tcDescripcion' => 'required',
                'tnPrecio' => 'required',
                'tnCatalogoImpuestos' => 'required',
                'tcCodigoProductoEmpresa' => 'required',
                'tnDecimalesCantidad' => 'required',
                'tnTipoProductoEmpresa' => 'required',
                'tnSaldo' => 'required',
                'tnMaximoStock' => 'required',
                'tnPrecioPorMayor' => 'required',
                'tnPrecioOferta' => 'required',
                'tnPrecioRemate' => 'required',
                'tnNumeroOpciones' => 'required',
                'tnNovedad' => 'required',
                'tnOferta' => 'required',
                'tcPosicion' => 'required',
                'tnNroVersion' => 'required',
                ]);

                return true;
        }catch (\Exception $e) {
            $this->message = $e->getMessage(); // Agregar detalles del error
            return false; // Devolver una respuesta con código 500
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

        $oProducto = Producto::find($InProducto);
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        $oUser = auth()->user();
        $lnEmpresaSeleccionada = +$oUser->EmpresaSeleccionada;
        if (!$oProducto) {
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "Error Producto no encontrado";
            $oPaquete->message = "a ocurrido un error";
            $oPaquete->values = null;
        }else{
            if(($lnEmpresaSeleccionada) ===(+$oProducto->Empresa)){

                $oClaseSiat = ClaseSiat::all();
                $oEmpresaCategoriaProd = DB::table('EMPRESACATEGORIAPRODUCTO as ecp')
                ->where('ecp.Empresa', $lnEmpresaSeleccionada)
                ->select('ecp.*')
                ->get();
                
                $oEmpresas = Empresa::find($lnEmpresaSeleccionada);
                

                $sql = "Select ae.*
                    From ACTIVIDADECONOMICA as ae, EMPRESAACTIVIDADECONOMICA as eae
                    Where ae.ActividadEconomica = eae.ActividadEconomica and eae.Empresa = ".$lnEmpresaSeleccionada.";";
                
                $oActividadEconomica = DB::select($sql);
                
                $oUnidadMedida = DB::table('UNIDADMEDIDA as um')
                ->join('UNIDADMEDIDAEMPRESA as ume', 'um.Codigo', '=', 'ume.Codigo')
                ->where('ume.Empresa', $lnEmpresaSeleccionada)
                ->select('um.*','ume.Empresa')
                ->get();
                
                $sincSiatController = new SincronizacionSiatController();
                $result = $sincSiatController->SincronizacionSiatReturn( $lnEmpresaSeleccionada,6);
                $oCatalogoIm = $result->original;
                
                
                //llega asta aqui
                $oPaquete->error = 0; //
                $oPaquete->status = 1; //
                $oPaquete->messageSistema = "comando ejecutado";
                $oPaquete->message = "ejecusion sin inconvenientes";
                $oPaquete->values = [
                    [$oEmpresas],
                    $oClaseSiat,
                    $oActividadEconomica,
                    $oCatalogoIm,
                    $oUnidadMedida,
                    $oEmpresaCategoriaProd,
                    $lnEmpresaSeleccionada,
                    $oProducto
                ] ;
                
                
            }else{
                $oPaquete->error = 1; // Error Generico
                $oPaquete->status = 0; // Sucedio un error
                $oPaquete->messageSistema = "El producto no pertenece a la empresa seleccionada.";
                $oPaquete->message = "a ocurrido un error";
                $oPaquete->values = null;             
            }
        }
        return response()->json($oPaquete);
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
     * @fecha   05-08-2023
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
                $oPaquete->messageSistema = "comando ejecutado";
                $oPaquete->message = "Se Desabilito el producto";
                $oPaquete->values = 1;
                return response()->json($oPaquete);
            }
        }
    }

    public function productosEmpresa(Request $request)
    {
        //
        //        return $request;
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oEmpresaSeleccionada = $request->tnEmpresa;

        $oProducto = DB::table('PRODUCTO as p')
        ->select('p.*', 'um.Descripcion as Unidad','e.Nombre as Empresa',
            'ae.DescripcionActividad as ActividadEconomica','cs.Nombre as ClaseSiat')
        ->join('UNIDADMEDIDA as um', 'p.Unidad', '=', 'um.Codigo')
        ->join('EMPRESA as e', 'e.Empresa', '=', 'p.Empresa')
        ->join('ACTIVIDADECONOMICA as ae', 'ae.ActividadEconomica', '=', 'p.ActividadEconomica')
        ->join('CLASESIAT as cs', 'cs.ClaseSIAT', '=', 'p.ClaseSIAT')
        ->where('p.Empresa', $oEmpresaSeleccionada)
        ->where('p.Estado', 1)
        ->orderBy('p.CodigoProductoOrigen')
        ->orderBy('p.ActividadEconomica')
        ->orderBy('p.CatalogoImpuestos')
        ->get();


        if($oEmpresaSeleccionada == 0 ){
            $oProducto = Producto::all();
        }


        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = $oProducto ;
        return response()->json($oPaquete);
    }

    public function productosValores()
    {
        $oUser = Auth::user();
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        //empresas relacionadas conel usuario
        try{
            $oClaseSiat = ClaseSiat::all();
            $oEmpresaCategoriaProd = DB::table('EMPRESACATEGORIAPRODUCTO as ecp')
            ->where('Empresa', $oUser->EmpresaSeleccionada)
            ->select('ecp.*')
            ->get();


            $empresasController = new UsuarioEmpresaController();
            $oEmpresas = $empresasController->misEmpresasReturn();
            $lnEmpresaSeleccionada = auth()->user()->EmpresaSeleccionada;

            $sql = "Select ae.* 
                    From ACTIVIDADECONOMICA as ae, EMPRESAACTIVIDADECONOMICA as eae
                    Where ae.ActividadEconomica = eae.ActividadEconomica and eae.Empresa = ".$lnEmpresaSeleccionada.";";
            
            $oActividadEconomica = DB::select($sql);
            
            $oUnidadMedida = DB::table('UNIDADMEDIDA as um')
            ->join('UNIDADMEDIDAEMPRESA as ume', 'um.Codigo', '=', 'ume.Codigo')
            ->where('ume.Empresa', $lnEmpresaSeleccionada)
            ->select('um.*','ume.Empresa')
            ->get();
            
            $sincSiatController = new SincronizacionSiatController();
            $result = $sincSiatController->SincronizacionSiatReturn( $lnEmpresaSeleccionada,6);
            $oCatalogoIm = $result->original;
            

            //llega asta aqui
            $oPaquete->error = 0; //
            $oPaquete->status = 1; //
            $oPaquete->messageSistema = "comando ejecutado";
            $oPaquete->message = "ejecusion sin inconvenientes";
            $oPaquete->values = [
                $oEmpresas,
                $oClaseSiat,
                $oActividadEconomica,
                $oCatalogoIm,
                $oUnidadMedida,
                $oEmpresaCategoriaProd,
                $lnEmpresaSeleccionada
                ] ;
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


    public function movimientoProducto(Request $request)
    {
        //
        return $request;
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oEmpresaSeleccionada = $request->tcEmpresa;

        // return Auth::user()->EmpresaSeleccionada;
        $oProducto = DB::table('PRODUCTO as p')
        ->select('p.*', 'um.Descripcion as Unidad')
        ->join('UNIDADMEDIDA as um', 'p.Unidad', '=', 'um.UnidadMedida')
        ->where('p.Empresa', $oEmpresaSeleccionada)
        ->where('p.Estado', 1)
        ->orderBy('p.CodigoProductoOrigen')
        ->orderBy('p.ActividadEconomica')
        ->orderBy('p.CatalogoImpuestos')
        ->get();


        if($oEmpresaSeleccionada == 0 ){
            $oProducto = Producto::all();
        }


        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = $oProducto ;
        return response()->json($oPaquete);
    }



}


