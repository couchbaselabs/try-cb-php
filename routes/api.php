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


Route::post('/tenants/{tenant}/user/signup', 'TenantUserController@create')->middleware('api');

Route::post('/user/login', 'UserController@authenticate')->middleware('api');

Route::post('/user/{user_name}/flights', 'UserController@book')->middleware('api');

Route::get('/user/{user_name}/flights', 'UserController@booked')->middleware('api');

Route::get('/airports', 'AirportController@search')->middleware('api');

Route::get('/flightPaths/{from}/{to}', 'FlightPathsController@find')->middleware('api');

Route::get('/hotels/', 'HotelController@find')->middleware('api');

Route::get('/hotels/{description}', 'HotelController@findByDescription')->middleware('api');

Route::get('/hotels/{description}/{location}', 'HotelController@findByDescriptionLocation')->middleware('api');