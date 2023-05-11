<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/teste', "App\Http\Controllers\api\NotasController@listaNotas");

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
