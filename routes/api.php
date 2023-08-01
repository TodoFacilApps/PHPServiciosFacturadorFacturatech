<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PruevaBlogController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioEmpresaController;

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



Route::get('/index', function () {
    return view('auth.registrer');
});
Route::get('/vlogin', function () {
    return view('auth.login');
});
Route::get('/empresas', [PruevaBlogController::class, 'ApiIndex'] );

// Ruta para procesar el registro de usuario
Route::post('/register', [UsuarioController::class, 'register']);

// Ruta para el inicio de sesión
Route::post('login', [UsuarioController::class, 'login']);
Route::post('registrarEmpresa', [UsuarioEmpresaController::class, 'store']);
Route::post('misEmpresa', [UsuarioEmpresaController::class, 'misEmpresas']);

Route::get('logout', [UsuarioController::class, 'logout'])->middleware('auth:api');
Route::get('user', [UsuarioController::class, 'logout'])->middleware('auth:api');



Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'UsuarioController@login');
    Route::post('signup', 'UsuarioController@signUp');

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'UsuarioController@logout');
        Route::get('user', 'UsuarioController@user');
    });
});
