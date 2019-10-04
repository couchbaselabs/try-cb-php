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
        $bucket = $cluster->bucket("travel-sample");
        $collection = $bucket->defaultCollection();

        $this->bucket = $bucket;
        $this->collection = $collection;
        $this->db = $cluster;
    }
}