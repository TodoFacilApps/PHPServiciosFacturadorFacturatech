<?php

namespace App\Http\Controllers;

use App\Models\EmpresaUsuarioPersonal;
use App\Models\TokenServicio;
use App\Models\Asociacion;
use App\Models\Empresa;
use GuzzleHttp\Client;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\ClaseSiat;
use App\Models\ActividadEconomica;
use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oEmpresaSeleccionada = Auth::user()->EmpresaSeleccionada;

        //return Auth::user()->EmpresaSeleccionada;
        $oProducto = Producto::where('Empresa', $oEmpresaSeleccionada)->get();
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
            if($this->FiltrosRegistroProducto($request)){
                $oProducto = Producto::where('CodigoProductoOrigen', $request->tcCodigoProductoOrigen)->
                where('Empresa', $request->tnEmpresa)->get();

                $lxImage;$lxImageData;$lcImagenNombre;

                if (!$oProducto->isEmpty()) {
                    $oPaquete->error = 1; // Error Generico
                        $oPaquete->status = 0; // Sucedio un error
                        $oPaquete->messageSistema = "Error en el proceso";
                        $oPaquete->message = "Error Codigo del Producto Existente en la empresa ";
                        $oPaquete->values = null;
                        return response()->json($oPaquete);
                }else{

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
                    $oProducto = Producto::create($oInput);
                    if($llImagen){
                        $newImageName = str_replace(' ', '_', $oProducto->Nombre);
                        $oProducto->UrlImagen =  'imagenes/empresa/' . $loEmpresa->Empresa. '/productos/' . $oProducto->Producto . $newImageName . $lcImagenNombre;
                        Storage::disk('public')->put($oProducto->UrlImagen, $lxImage);
                    }
                    $oProducto->UrlImagen = env('APP_URL') . '/' . $oProducto->UrlImagen;
                    $oProducto->save();

                }
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
                $oPaquete->messageSistema = "Unidad de Medida creada";
                $oPaquete->message = "se creo la Unidad de Medida";
                $oPaquete->values = $oProducto;
                return response()->json($oPaquete);
            }
        }

    }


    public function productosEmpresa(Request $request)
    {
        //
        //        return $request;
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $oEmpresaSeleccionada = $request->tcEmpresa;

        //        return Auth::user()->EmpresaSeleccionada;
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



    public function productosValores()
    {
        $oUser = Auth::user();
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);
        //empresas relacionadas conel usuario
        try{

            $oClaseSiat = ClaseSiat::all();
            $oUserApiToken = TokenServicio::where('ApiToken', $oUser->api_token)
            ->first();

            $oEmpresas = Empresa::select('EMPRESA.*')
            ->leftJoin('EMPRESAUSUARIOPERSONAL', 'EMPRESAUSUARIOPERSONAL.Empresa', '=', 'EMPRESA.Empresa')
            ->leftJoin('USUARIO', 'EMPRESAUSUARIOPERSONAL.Usuario', '=', 'USUARIO.Usuario')
            ->where('USUARIO.Usuario', '=', $oUser->Usuario)
            ->where('EMPRESAUSUARIOPERSONAL.Estado', '=', 1 )
            ->orderBy('EMPRESA.Empresa', 'asc')
            ->get();
            $oActividadEconomica;
            $oUnidadMedida;
            $oCatalogoIm;

            if(!$oEmpresas->isEmpty()){
                $tnActividades = [1,6,18];
                $lnEmpresa = $oEmpresas[0]->Empresa;

                $oAsociacion = Asociacion::where('Empresa', $lnEmpresa)
                ->where('CodigoAmbiente', 1)
                ->get();


                foreach ($tnActividades as $tnActividad) {
                    $lltokenHabilitado = true;
                    do {
                        //enviando credenciales estaticas para las pruevas
                        $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                        //$token = request()->bearerToken();
                        $url = self::_API . 'servicio/sincronizacionsiat';
                        $data = array(
                            'tcCredencial' => $oAsociacion[0]->AsociacionCredencial, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                            'tnTipo' => $tnActividad
                        );
                        $header=[
                                'Accept'        => 'application/json',
                                'Authorization' => 'Bearer ' . $oUserApiToken->TokenBearer // Reemplaza esto con tu token
                                ];

                        $response = $client->post($url, ['headers' => $header,
                                                            'json' => $data]);
                        $result = json_decode($response->getBody()->getContents());
                                //verifica si su token a exiprado y si es asi este lo actusliza
                        if ($result->values == null){

                            //enviando credenciales estaticas para las pruevas
                            $client = new Client(['headers' => ['X-Foo' => 'Bar']]);
                            //$token = request()->bearerToken();
                            $url = self::_API . 'login';
                            $data = array(
                                'TokenService' => $oUserApiToken->TokenService, //'4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
                                'TokenSecret' => $oUserApiToken->TokenSecret
                            );
                            $header=[
                                    'Accept'        => 'application/json',
                                    ];
                            $response = $client->post($url, ['headers' => $header,
                                                                'json' => $data]);
                            $result = json_decode($response->getBody()->getContents());

                            //llega asta aqui
                            $oUserApiToken->TokenBearer = ($result->values);
                            $oUserApiToken->save();

                            return $result;
                            $lltokenHabilitado =true;
                        }else{
                            $lltokenHabilitado =false;
                        }
                    } while ($lltokenHabilitado);

                    switch ($tnActividad) {
                        case 1:
                            $oActividadEconomica = $result->values;
                            break;

                        case 6:
                            $oCatalogoIm = $result->values;
                            break;

                        case 18:
                            $oUnidadMedida = $result->values;
                            break;

                        default:
                            # code...
                            break;
                    }
                }
            }else{
                echo('no hay empresas');
            }

            //llega asta aqui
            $oPaquete->error = 0; //
            $oPaquete->status = 1; //
            $oPaquete->messageSistema = "comando ejecutado";
            $oPaquete->message = "ejecusion sin inconvenientes";
            $oPaquete->values = [$oEmpresas,$oClaseSiat, $oActividadEconomica, $oCatalogoIm, $oUnidadMedida] ;
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


