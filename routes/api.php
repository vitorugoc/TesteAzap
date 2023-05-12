<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/notas', "App\Http\Controllers\api\NotasController@listaNotas");

Route::get('/notas/valor', "App\Http\Controllers\api\NotasController@calculaTotal");

Route::get('/notas/valor/entregue', "App\Http\Controllers\api\NotasController@calculaValorEntregue");

Route::get('/notas/valor/nao_entregue', "App\Http\Controllers\api\NotasController@calculaValorNaoEntregue");

Route::get('/notas/valor/nao_recebido', "App\Http\Controllers\api\NotasController@calculaNaoRecebido");

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
