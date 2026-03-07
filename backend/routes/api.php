<?php

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\MecanicoController;
use App\Http\Controllers\Api\OrdenServicioController;
use App\Http\Controllers\Api\VehiculoController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('api.token')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::apiResource('mecanicos', MecanicoController::class);
    Route::apiResource('ordenes-servicio', OrdenServicioController::class)
        ->parameters(['ordenes-servicio' => 'ordenServicio']);
    Route::patch('ordenes-servicio/{ordenServicio}/estado', [OrdenServicioController::class, 'actualizarEstado']);
});
