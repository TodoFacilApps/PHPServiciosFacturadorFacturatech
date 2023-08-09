<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PruevaBlogController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\TokenServicioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\ProveedorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Ruta para el inicio de sesión
//Route::post('login', [UsuarioController::class, 'login']);


// Rutas públicas (no requieren autenticación)
Route::post('signup', [UsuarioController::class, 'signup']);
// Ruta para procesar el registro de usuario
Route::post('/register', [UsuarioController::class, 'register']);
Route::post('login', [UsuarioController::class, 'login']);

Route::get('/empresas', [PruevaBlogController::class, 'ApiIndex'] );




// Rutas protegidas (requieren autenticación con el middleware 'auth:api')
Route::middleware('auth:api')->group(function () {
    //rutas Usuario
    Route::get('logout', [UsuarioController::class, 'logout']);
    Route::get('user', [UsuarioController::class, 'user']);
    // rutas relacionadas con la empresa
    Route::post('registrarEmpresa', [UsuarioEmpresaController::class, 'store']);
    Route::get('misEmpresa', [UsuarioEmpresaController::class, 'misEmpresas']);
    Route::get('/misEmpresa/{id}', [UsuarioEmpresaController::class, 'show'])->name('empresas.show');

    //rutas relacionadas con el negocion principal
    Route::post('registerToken', [TokenServicioController::class, 'store']);
    Route::post('obtenerToken', [TokenServicioController::class, 'show']);


    //rutas de productos o servicios
    //unidad de medida
    Route:: resource( 'unidadMedidas', UnidadMedidaController::class);
    Route:: resource( 'productos', ProductoController::class);
    Route:: resource( 'ingresos', IngresoController::class);
    Route:: get( 'ingresos-valoresprevios', [IngresoController::class, 'valoresPrevios']);
    Route:: resource( 'proveedores', ProveedorController::class);
    // usuarios del sistema

    //proveedores

    //rutas del los catalogos
    //ejemplo precios unitarios
    //         precios por docena
            // precios por paquete
            // precios por caja
            //


    // rutas de pedidos


    // rutas de ventas

    // rutas de remito

    //rutas de facturas


});
