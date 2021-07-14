<?php

namespace App\Http\Controllers;

use Couchbase\KeyExistsException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\User;
use Illuminate\Auth\AuthenticationException;


class TenantUserController extends CouchbaseController
{
    public function create(Request $request)
    {
        $credentials = [
            'user' => $request->user,
            'password' => $request->password,
        ];
        $user = new User($credentials);
        $agent = $request->tenant;

        try {
            $scope = $this->bucket->scope($agent);
            $usersCollection = $scope->collection("users");
            $usersCollection->insert($request->user, $user);

            $queryType = sprintf(
                "KV insert - scoped to %s.users: document %s",
                $scope->name(),
                $request->user
            );
            return response()->json(
                ["data" => ["token" => $this->buildToken($user)], "context" => [$queryType]],
                201
            );
        } catch (KeyExistsException $ex) {
            return response()->json(["message" => 'User already exists'], 409);
        }
    }

    public function authenticate(Request $request)
    {
        $credentials = [
            'name' => $request->user,
            'password' => $request->password,
        ];
        $user = new User($credentials);

        try {
            $userInfo = $this->userColl->get($user->name);
        } catch (Couchbase\KeyNotFoundException $e) {
            throw new AuthenticationException("User not found");
        }

        $userInfo = json_decode($userInfo->content());

        if (strcmp($user->password, $userInfo->password) != 0) {
            throw new AuthenticationException("Invalid password");
        }

        $token = $this->buildToken($user);
        if ($request->token != "") {
            if (strcmp($request->token, $token) != 0) {
                throw new AuthenticationException("Invalid JWT");
            }
        }

        return response()->json(["data" => ["token" => $token]]);
    }

    public function book(Request $request, $userName)
    {
        $key = $userName;
        try {
            $userInfo = $this->userColl->get($key);
        } catch (Couchbase\KeyNotFoundException $e) {
            throw new AuthenticationException("User not found");
        }

        $userInfo = json_decode($userInfo->content());

        $credentials = [
            'name' => $userName,
            'password' => $userInfo->password,
        ];
        $user = new User($credentials);

        $token = $this->buildToken($user);
        if ($request->token != "") {
            if (strcmp($request->token, $token) != 0) {
                throw new AuthenticationException("Invalid JWT");
            }
        }

        if (!property_exists($userInfo, "flights")) {
            $userInfo->flights = array();
        }
        $added = [];
        foreach ($request->json()->get('flights') as $flight) {
            $flight['bookedon'] = 'try-cb-php';
            $uuid = uniqid();
            $userInfo->flights[] = $uuid;
            $this->flightColl->upsert($uuid, $flight);
            $added[] = $flight;
        }
        $this->userColl->upsert($key, $userInfo);
        return response()->json([
            "data" => ["added" => $added],
            'context' => "Booked flight in Couchbase document $key"
        ]);
    }

    public function booked(Request $request, $userName)
    {
        $userInfo = $this->userColl->get($userName);
        $userInfo = json_decode($userInfo->content());
        $flights = [];
        if (property_exists($userInfo, "flights")) {
            foreach ($userInfo->flights as $flight) {
                $flightData = $this->flightColl->get($flight);
                $flightData = json_decode($flightData->content());
                array_push($flights, $flightData);
            }
        }
        return response()->json(["data" => $flights]);
    }

    private function buildToken($user)
    {
        return JWTAuth::fromUser($user, ['user' => $user->name]);
    }
}
