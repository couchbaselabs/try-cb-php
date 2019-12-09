<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;


class CouchbaseController extends Controller
{
    protected $db;
    /**
     * Create a new couchbase controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $connectionString = "couchbase://localhost";
        $cluster = new \Couchbase\Cluster($connectionString);
        $cluster->authenticateAs("Administrator", "password");
        $dataBucket = $cluster->bucket("travel-sample");
        $userBucket = $cluster->bucket("travel-users");
        $mainColl = $dataBucket->defaultCollection();
        $userScope = $userBucket->scope("userData");
        $userColl = $userScope->collection("users");
        $flightColl = $userScope->collection("flights");

        $this->bucket = $dataBucket;
        $this->collection = $mainColl;
        $this->userColl = $userColl;
        $this->flightColl = $flightColl;
        $this->db = $cluster;
    }
}