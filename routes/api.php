<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

use App\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/user/signup', 'UserController@create')->middleware('api');

Route::post('/user/login', 'UserController@login')->middleware('api');

Route::get('/airports', 'AirportController@search')->middleware('api');

Route::get('/flightPaths/{from}/{to}', 'FlightPathsController@find')->middleware('api');
