<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RutaController;
use Illuminate\Support\Facades\Route;

// Publica
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Requiere token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/cambiar-password', [AuthController::class, 'cambiarPassword']);

    // Rutas y categorias
    Route::get('/rutas', [RutaController::class, 'index'])->middleware('can:rutas.ver');
    Route::get('/categorias', [CategoriaController::class, 'index'])->middleware('can:productos.ver');

    // Clientes (cada accion exige su permiso)
    Route::get('/clientes', [ClienteController::class, 'index'])->middleware('can:clientes.ver');
    Route::get('/clientes/{id}', [ClienteController::class, 'show'])->whereUuid('id')->middleware('can:clientes.ver');
    Route::post('/clientes', [ClienteController::class, 'store'])->middleware('can:clientes.crear');
    Route::put('/clientes/{id}', [ClienteController::class, 'update'])->whereUuid('id')->middleware('can:clientes.editar');
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->whereUuid('id')->middleware('can:clientes.eliminar');

    // Productos
    Route::get('/productos', [ProductoController::class, 'index'])->middleware('can:productos.ver');
    Route::get('/productos/{id}', [ProductoController::class, 'show'])->whereUuid('id')->middleware('can:productos.ver');
    Route::post('/productos', [ProductoController::class, 'store'])->middleware('can:productos.crear');
    Route::put('/productos/{id}', [ProductoController::class, 'update'])->whereUuid('id')->middleware('can:productos.editar');
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->whereUuid('id')->middleware('can:productos.eliminar');
});
