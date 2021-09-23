<?php

namespace App\Http\Controllers;

use Couchbase\KeyExistsException;
use Couchbase\DocumentNotFoundException;
use Couchbase\MutateUpsertSpec;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Credentials;
use App\User;


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
            'user' => $request->user,
            'password' => $request->password,
        ];
        $user = new User($credentials);
        $agent = $request->tenant;

        try {
            $scope = $this->bucket->scope($agent);
            $usersCollection = $scope->collection("users");
            $getResult = $usersCollection->get($user->user);
        } catch (DocumentNotFoundException $e) {
            return response()->json(["message" => 'User not found'], 401);
        }

        $userInfo = $getResult->content();
        if (strcmp($user->password, $userInfo["password"]) != 0) {
            return response()->json(["message" => 'Invalid password'], 401);
        }

        $token = $this->buildToken($user);
        if ($request->token != "") {
            if (strcmp($request->token, $token) != 0) {
                return response()->json(["message" => 'Invalid JWT token'], 401);
            }
        }

        $queryType = sprintf(
            "KV get - scoped to %s.users: for password field in document %s",
            $scope->name(),
            $request->user
        );
        return response()->json(["data" => ["token" => $token], "context" => [$queryType]]);
    }

    public function book(Request $request)
    {
        $username = $request->user;
        $agent = $request->tenant;
        try {
            $scope = $this->bucket->scope($agent);
            $usersCollection = $scope->collection("users");
            $getResult = $usersCollection->get($username);
        } catch (DocumentNotFoundException $e) {
            return response()->json(["message" => 'User not found'], 401);
        }

        $userInfo = $getResult->content();
        $credentials = [
            'user' => $username,
            'password' => $userInfo["password"],
        ];
        $user = new User($credentials);

        $token = $this->buildToken($user);
        if ($request->token != "") {
            if (strcmp($request->token, $token) != 0) {
                return response()->json(["message" => 'Invalid JWT token'], 401);
            }
        }

        if (!array_key_exists("bookings", $userInfo)) {
            $userInfo["bookings"] = array();
        }
        $added = [];
        $bookingsCollection = $scope->collection("bookings");
        foreach ($request->json()->get('flights') as $flight) {
            $flight['bookedon'] = 'try-cb-php';
            $uuid = uniqid();
            $userInfo["bookings"][] = $uuid;
            $bookingsCollection->upsert($uuid, $flight);
            $added[] = $flight;
        }

        $usersCollection->mutateIn($username, [
            new MutateUpsertSpec('bookings', $userInfo["bookings"])
        ]);

        $queryType = sprintf(
            "KV update - scoped to %s.users: for password field in document %s",
            $scope->name(),
            $request->user
        );
        return response()->json([
            "data" => ["added" => $added],
            "context" => [$queryType]
        ]);
    }

    public function booked(Request $request)
    {
        $username = $request->user;
        $agent = $request->tenant;

        $scope = $this->bucket->scope($agent);
        $usersCollection = $scope->collection("users");
        $bookingsCollection = $scope->collection("bookings");

        $getResult = $usersCollection->get($username);
        $userInfo = $getResult->content();
        $flights = [];
        if (array_key_exists("bookings", $userInfo)) {
            foreach ($userInfo["bookings"] as $flight) {
                $getFlightResult = $bookingsCollection->get($flight);
                $flightData = $getFlightResult->content();
                array_push($flights, $flightData);
            }
        }

        $queryType = sprintf(
            "KV get - scoped to %s.users: for %d bookings in document %s",
            $scope->name(),
            count($flights),
            $request->user
        );
        return response()->json(["data" => $flights, "context" => [$queryType]]);
    }

    private function buildToken($user)
    {
        return JWTAuth::fromUser($user, ['user' => $user->name]);
    }
}
