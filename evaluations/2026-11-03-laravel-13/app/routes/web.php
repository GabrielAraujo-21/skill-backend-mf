<?php

use App\Http\Controllers\SessaoController;
use Illuminate\Support\Facades\Route;

Route::post('login', [SessaoController::class, 'entrar'])->middleware('throttle:5,1');
Route::post('logout', [SessaoController::class, 'sair'])->middleware('auth');
