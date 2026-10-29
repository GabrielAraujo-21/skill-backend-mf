<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\SessaoController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['aplicacao' => 'laboratorio-skills']));
Route::prefix('api')->group(function () {
    Route::post('/login', [SessaoController::class, 'login'])->middleware('throttle:10,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [SessaoController::class, 'me']);
        Route::post('/logout', [SessaoController::class, 'logout']);
        Route::apiResource('clientes', ClienteController::class)->except('show');
    });
});
