<?php

use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('clientes', [ClienteController::class, 'index']);
    Route::post('clientes', [ClienteController::class, 'store']);
    Route::put('clientes/{cliente:codigo}', [ClienteController::class, 'update']);
});
