<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PruevaBlogController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioEmpresaController;
use App\Http\Controllers\TokenServicioController;

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
    Route::get('logout', [UsuarioController::class, 'logout']);
    Route::get('user', [UsuarioController::class, 'user']);

    Route::post('registrarEmpresa', [UsuarioEmpresaController::class, 'store']);
    Route::post('misEmpresa', [UsuarioEmpresaController::class, 'misEmpresas']);
    Route::post('registerToken', [TokenServicioController::class, 'store']);
    Route::post('obtenerToken', [TokenServicioController::class, 'show']);

});
