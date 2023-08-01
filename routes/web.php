<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::post('/register', [UsuarioController::class, 'register']);

// Ruta para el inicio de sesión
//Route::post('/login', [UsuarioController::class, 'login']);

Route::post('login-init', [UsuarioController::class, 'loginInit'])->name('api.login-init');
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::post('loginL', [UsuarioController::class, 'login'])->name('loginL');

