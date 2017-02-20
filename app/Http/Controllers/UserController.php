<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\User;
use Illuminate\Auth\AuthenticationException;


class UserController extends CouchbaseController
{

    public function create(Request $request)
    {
        $credentials = [
            'name' => $request->user,
            'password' => $request->password,
        ];
        $user = new User($credentials);
        $this->db->insert("user::".$request->user, $user);
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

        $userInfo = $this->db->get("user::".$user->name);

        if ($userInfo == null) {
            throw new AuthenticationException("User not found");
        }

        if (strcmp($user->password, $userInfo->value->password) != 0) {
            throw new AuthenticationException("Invalid password");
        }

        $token = JWTAuth::fromUser($user);

        if ($request->token != "") {
            if (strcmp($request->token, $token) != 0) {
                throw new AuthenticationException("Invalid JWT");
            }
        }

        return response()->json(["data" => ["token" => $token]]);
    }

    public function book(Request $request, $userName)
    {
        try {
            $userInfo = $this->db->get("user::" . $userName);
        } catch(\CouchbaseException $e) {
            throw new AuthenticationException("Invalid user ".$e);
        }

        if ($userInfo == null) {
            throw new AuthenticationException("User not found");
        }

        $credentials = [
            'name' => $userName,
            'password' => $userInfo->password,
        ];
        $user = new User($credentials);

        $token = JWTAuth::fromUser($user);

        if ($request->token != "") {
            if (strcmp($request->token, $token) != 0) {
                throw new AuthenticationException("Invalid JWT");
            }
        }

        if (property_exists($userInfo, "flights")) {
            $userInfo->flights[] = $request->json();
        } else {
            $userInfo->flights = array();
            $userInfo->flights[] = $request->json();
        }
        $this->db->upsert("user::".$userName, $userInfo);
        return response()->make("Booked using try-cb-php");
    }

    public function booked(Request $request, $userName)
    {
       //TODO: ui bug does not send the username in the request
    }

}
