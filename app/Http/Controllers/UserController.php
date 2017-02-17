<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\User;

class UserController extends CouchbaseController
{

    public function create(Request $request)
    {
        $credentials = [
            'name' => $request->user,
            'password' => $request->password,
        ];
        $user = new User($credentials);
        $this->db->insert($request->user, $user);
        $token = JWTAuth::fromUser($user);
        return response()->json(["data" => ["token" => $token]]);
    }


    public function authenticate(Request $request)
    {
        $credentials = [
            'name' => $request->user,
            'password' => $request->password,
        ];
        $user = new User($credentials);
        $token = JWTAuth::fromUser($user);
        if (strcmp($request->token, $token) != 0) {
            return response('Invalid token', 401);
        }
        return response()->json(["data" => ["token" => $token]]);
    }
}
