<?php

use Illuminate\Support\Facades\Route;

Route::get('/city/{id}', 'App\Http\Controllers\CityController@show');
Route::get('/search', 'App\Http\Controllers\CityController@search');
Route::get('/api/search', 'App\Http\Controllers\Api\ApiSearchController@search');
