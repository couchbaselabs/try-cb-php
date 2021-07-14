<?php

use App\Http\Controllers\AirportController;
use App\Http\Controllers\FlightPathsController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\TenantUserController;

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


Route::post('/tenants/{tenant}/user/signup', [TenantUserController::class, 'create'])->middleware('api');

Route::post('/tenants/{tenant}/user/login', [TenantUserController::class, 'authenticate'])->middleware('api');

Route::put('/tenants/{tenant}/user/{user}/flights', [TenantUserController::class, 'book'])->middleware('api');

Route::get('/tenants/{tenant}/user/{user}/flights', [TenantUserController::class, 'booked'])->middleware('api');

Route::get('/airports', [AirportController::class, 'search'])->middleware('api');

Route::get('/flightPaths/{from}/{to}', [FlightPathsController::class, 'find'])->middleware('api');

Route::get('/hotels/', [HotelController::class, 'find'])->middleware('api');

Route::get('/hotels/{description}', [HotelController::class, 'findByDescription'])->middleware('api');

Route::get('/hotels/{description}/{location}', [HotelController::class, 'findByDescriptionLocation'])->middleware('api');