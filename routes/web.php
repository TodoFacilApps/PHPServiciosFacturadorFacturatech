<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController    ;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/form', function () {
    return view('form');
});
// ruta al enviar correo
Route::post('send', [EmailController::class, 'send']);

Route::post('mensajeStatic',  [EmailController::class, 'mensaje']);
Route::get('mensajeStatic',  [EmailController::class, 'mensajeStatico']);
Route::post('/password/email', [EmailController::class, 'sendResetLinkEmail']);

Route::get('mensajeAdjunto',  [EmailController::class, 'mensajeAdjunto']);
