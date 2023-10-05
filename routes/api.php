<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PruevaBlogController;
use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\TokenServicioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\MonedaController;
use App\Http\Controllers\ImpuestoIvaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\PuntoVentaController;
use App\Http\Controllers\SincronizacionSiatController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\TipoClienteController;
use App\Http\Controllers\EmpresaCategoriaProductoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Rutas públicas (no requieren autenticación)
Route::post('register', [UsuarioController::class, 'register']);
Route::post('login', [UsuarioController::class, 'login']);
Route::get('envioMensaje', [EmailController::class, 'prueva']);




// Rutas protegidas (requieren autenticación con el middleware 'auth:api')
Route::middleware('auth:api')->group(function () {
    //rutas Usuario
    Route::resource('user', UsuarioController::class);
    Route::get('logout', [UsuarioController::class, 'logout']);
    Route::get('user', [UsuarioController::class, 'user']);
    Route::post('userPass', [UsuarioController::class, 'userPass']);
    Route::post('selecionarEmpresa', [UsuarioController::class, 'selecionarEmpresa']);

    // rutas relacionadas con la empresa
    Route::post('registrarEmpresa', [UsuarioEmpresaController::class, 'store']);
    Route::get('misEmpresa', [UsuarioEmpresaController::class, 'misEmpresas']);
    Route::get('misEmpresa/ver', [UsuarioEmpresaController::class, 'ver']);
    Route::get('/misEmpresa/{id}', [UsuarioEmpresaController::class, 'show'])->name('empresas.show');
    Route:: resource( 'sucursales', SucursalController::class);
    Route:: post( 'empresaSucursal', [SucursalController::class, 'empresaSucursal']);
    Route:: post( 'sucursalPuntoVenta', [SucursalController::class, 'sucursalPuntoVenta']);
    Route:: resource( 'puntoVentas', PuntoVentaController::class);

    //rutas Home
    Route::get('presentacion', [HomeController::class, 'dashboard']);

    Route::post('home', [HomeController::class, 'dashboardEmpresa']);

    //rutas relacionadas con el negocion principal
    Route::post('registerToken', [TokenServicioController::class, 'store']);
    Route::post('obtenerToken', [TokenServicioController::class, 'show']);
    Route::post('servicio/sincronizacionsiat', [SincronizacionSiatController::class, 'SincronizacionSiat']);
    Route::post('loginApiToken', [SincronizacionSiatController::class, 'loginApiToken']);
    Route::get('reconnect', [SincronizacionSiatController::class, 'reconnect']);
    Route::get('userApiToken', [SincronizacionSiatController::class, 'userApiToken']);
    Route::get('claseSiat', [SincronizacionSiatController::class, 'claseSiat']);
    Route::get('actividadEconomica', [SincronizacionSiatController::class, 'actividadEconomica']);


    //rutas de productos o servicios
    Route:: resource( 'clientes', ClienteController::class);
    Route:: resource( 'tipoClientes', TipoClienteController::class);
    Route:: post( 'empresa/clientes', [ClienteController::class, 'optenerClientes']);
    Route:: post( 'clientesEditar', [ClienteController::class, 'editar']);
    Route:: post( 'clientesValidarNit', [ClienteController::class, 'clientesValidarNit']);
    Route:: resource( 'productos', ProductoController::class);
    Route:: resource( 'empresaCategoriaProducto', EmpresaCategoriaProductoController::class);
    Route:: post( 'productosEmpresa', [ProductoController::class, 'productosEmpresa']);
    Route:: post( 'movimientos', [ProductoController::class, 'movimientoProducto']);
    Route:: get( 'productosValores', [ProductoController::class, 'productosValores']);


    Route:: resource( 'ingresos', IngresoController::class);
    Route:: get( 'ingresos-valoresprevios', [IngresoController::class, 'valoresPrevios']);
    Route:: resource( 'proveedores', ProveedorController::class);
    Route:: resource( 'catalogos', CatalogoController::class);
    Route:: get( 'catalogosData', [CatalogoController::class,'catalogoData']);
    // usuarios del sistema

    //proveedores


    // rutas de pedidos


    // rutas de ventas
    Route:: resource( 'ventas', VentaController::class);
    Route:: get( 'ventasData', [VentaController::class,'ventasData']);
    Route:: post( 'ventasData', [VentaController::class,'ventasDataEmpresa']);
    Route:: post( 'getVentas', [VentaController::class,'getVentas']);
    Route:: post( 'emitirSiat', [VentaController::class,'emitirSiat']);
    Route:: post( 'estadoSiat', [VentaController::class,'estadoSiat']);
    //    Route:: get( 'reconecTokenReturn', [SincronizacionSiatController::class,'reconecTokenReturn']);


    //rutas de facturas
    Route:: post( 'crearFactura', [VentaController::class,'crearFactura']);


    //rutas de Parametros
    Route:: resource( 'unidadMedidas', UnidadMedidaController::class);
    Route:: resource( 'monedas', MonedaController::class);
    Route:: resource( 'impuestos-iva', ImpuestoIvaController::class);

});
