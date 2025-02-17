<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PedidoController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});


Route::prefix('pedidos')->middleware('auth:api')->group(function () {
    Route::get('', [PedidoController::class, 'index']);
    Route::post('', [PedidoController::class, 'store']);
    Route::post('/cancel', [PedidoController::class, 'cancel']);
    Route::patch('/update', [PedidoController::class, 'update']);
    Route::get('/filter', [PedidoController::class, 'filter']);
    Route::get('/report', [PedidoController::class, 'reporte']);
});
