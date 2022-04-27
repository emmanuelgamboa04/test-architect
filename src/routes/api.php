<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/auth'

], function ($router) {
    Route::post('login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login'])->name('login');
    Route::post('logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->name('logout');
    Route::post('refresh', [\App\Http\Controllers\Api\V1\AuthController::class, 'refresh'])->name('refresh');
    Route::post('me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me'])->name('me');
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1'
], function ($router) {
    Route::post('usuario', [\App\Http\Controllers\Api\V1\UsuarioController::class, 'usuario'])->name('usuario');
    Route::get("usuarios", [\App\Http\Controllers\Api\V1\UsuarioController::class, 'usuarios'])->name('usuarios');
    Route::post('post', [\App\Http\Controllers\Api\V1\PostController::class, 'post'])->name('post');
    Route::get('posts', [\App\Http\Controllers\Api\V1\PostController::class, 'posts'])->name('posts');
    Route::post('video', [\App\Http\Controllers\Api\V1\VideoController::class, 'video'])->name('video');
    Route::get('videos', [\App\Http\Controllers\Api\V1\VideoController::class, 'videos'])->name('videos');
});
