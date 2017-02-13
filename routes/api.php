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

Route::post('/user/signup', function (Request $request) {
    $credentials = [
        'name' => $request->user,
        'password' => $request->password,
    ];
    try {
        $user = User::create($credentials);
    } catch (Exception $e) {
        return Response::json(['failure' => 'There was an error creating account.'], HttpResponse::HTTP_CONFLICT);
    }

    $token = JWTAuth::fromUser($user);

    return Response::json(['data' => compact('token')], HttpResponse::HTTP_ACCEPTED);
})->middleware('api');

Route::post('/user/login', function (Request $request) {
    $credentials = [
        'name' => $request->user,
        'password' => $request->password,
    ];
    try {
        $user = User::where($credentials)->firstOrFail();
    } catch (Exception $e) {
        return Response::json(['failure' => 'Bad Username or Password'], HttpResponse::HTTP_CONFLICT);
    }

    $token = JWTAuth::fromUser($user, ['user' => $user->]);

    return Response::json(['data' => compact('token')], HttpResponse::HTTP_ACCEPTED);
})->middleware('api');