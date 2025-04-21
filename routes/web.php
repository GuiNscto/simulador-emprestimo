<?php

use App\Http\Controllers\SimuladorController;
use Illuminate\Support\Facades\Route;

Route::get('/instituicoes', [SimuladorController::class, 'instituicoes']);
Route::get('/convenios', [SimuladorController::class, 'convenios']);
Route::post('/simulacoes', [SimuladorController::class, 'simular']);